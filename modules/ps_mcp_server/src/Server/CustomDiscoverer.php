<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Server;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpPrompt;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResource;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResourceTemplate;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Services\McpFeaturesService;
use PrestaShop\Module\PsMcpServer\Tracker\Segment;
use PsMcpServerDeps\Mcp\Capability\Attribute\CompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Completion\EnumCompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Completion\ListCompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Discovery\DiscovererInterface;
use PsMcpServerDeps\Mcp\Capability\Discovery\DiscoveryState;
use PsMcpServerDeps\Mcp\Capability\Discovery\DocBlockParser as DiscoveryDocBlockParser;
use PsMcpServerDeps\Mcp\Capability\Discovery\SchemaGenerator as DiscoverySchemaGenerator;
use PsMcpServerDeps\Mcp\Capability\Registry\PromptReference;
use PsMcpServerDeps\Mcp\Capability\Registry\ResourceReference;
use PsMcpServerDeps\Mcp\Capability\Registry\ResourceTemplateReference;
use PsMcpServerDeps\Mcp\Capability\Registry\ToolReference;
use PsMcpServerDeps\Mcp\Schema\Prompt;
use PsMcpServerDeps\Mcp\Schema\PromptArgument;
use PsMcpServerDeps\Mcp\Schema\Resource;
use PsMcpServerDeps\Mcp\Schema\ResourceTemplate;
use PsMcpServerDeps\Mcp\Schema\Tool;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomDiscoverer implements DiscovererInterface
{
    private DiscoveryDocBlockParser $docBlockParser;
    private DiscoverySchemaGenerator $schemaGenerator;
    private McpFeaturesService $mcpFeaturesService;
    private Segment $segment;

    private array $discoveredItems = [];

    private array $moduleActiveCache = [];

    public function __construct(
        private LoggerInterface $logger,
        McpFeaturesService $mcpFeaturesService,
        Segment $segment,
        ?DiscoveryDocBlockParser $docBlockParser,
        ?DiscoverySchemaGenerator $schemaGenerator,
    ) {
        $this->mcpFeaturesService = $mcpFeaturesService;
        $this->segment = $segment;
        $this->docBlockParser = $docBlockParser ?? new DiscoveryDocBlockParser(logger: $this->logger);
        $this->schemaGenerator = $schemaGenerator ?? new DiscoverySchemaGenerator($this->docBlockParser);
    }

    public function discover(string $basePath, array $directories, array $excludeDirs = []): DiscoveryState
    {
        $startTime = microtime(true);

        $this->discoveredItems = [
            'tools' => ['entries' => [], 'counts' => 0],
            'resources' => ['entries' => [], 'counts' => 0],
            'prompts' => ['entries' => [], 'counts' => 0],
            'resourceTemplates' => ['entries' => [], 'counts' => 0],
        ];

        $tools = [];
        $resources = [];
        $prompts = [];
        $resourceTemplates = [];

        try {
            $finder = new Finder();
            $absolutePaths = [];

            foreach ($directories as $dir) {
                $path = str_starts_with((string) $dir, '/') ? $dir : rtrim((string) $basePath, '/') . '/' . ltrim((string) $dir, '/');
                if (is_dir($path)) {
                    $absolutePaths[] = $path;
                }
            }

            if (empty($absolutePaths)) {
                return new DiscoveryState();
            }

            $finder->files()->in($absolutePaths)->exclude($excludeDirs)->name('*.php')->notName(['index.php']);

            foreach ($finder as $file) {
                $this->processFile($file, $tools, $resources, $prompts, $resourceTemplates);
            }

            $this->mcpFeaturesService->cleanObsoleteFeatures('tool', $this->discoveredItems['tools']['entries']);
            $this->mcpFeaturesService->cleanObsoleteFeatures('prompt', $this->discoveredItems['prompts']['entries']);
            $this->mcpFeaturesService->cleanObsoleteFeatures('resource', $this->discoveredItems['resources']['entries']);
            $this->mcpFeaturesService->cleanObsoleteFeatures('resourceTemplate', $this->discoveredItems['resourceTemplates']['entries']);
        } catch (\Throwable $e) {
            $this->logger->error('Error during MCP discovery', ['exception' => $e->getMessage()]);
        }

        $this->logger->info('Discovery finished.', [
            'duration' => round(microtime(true) - $startTime, 3),
            'counts' => array_map(fn ($item) => $item['counts'], $this->discoveredItems),
        ]);

        return new DiscoveryState($tools, $resources, $prompts, $resourceTemplates);
    }

    private function processFile(\SplFileInfo $file, array &$tools, array &$resources, array &$prompts, array &$resourceTemplates): void
    {
        $className = $this->getClassFromFile($file->getPathname());
        if (!$className) {
            return;
        }

        try {
            $reflectionClass = new \ReflectionClass($className);
            if ($reflectionClass->isAbstract() || $reflectionClass->isInterface() || $reflectionClass->isTrait()) {
                return;
            }

            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $attributeTypes = [
                    PsMcpTool::class => 'handleTool',
                    PsMcpPrompt::class => 'handlePrompt',
                    PsMcpResource::class => 'handleResource',
                    PsMcpResourceTemplate::class => 'handleResourceTemplate',
                ];

                foreach ($attributeTypes as $attrClass => $handler) {
                    $attribute = $method->getAttributes($attrClass, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
                    if ($attribute) {
                        $this->processMethod($method, $attribute, $tools, $resources, $prompts, $resourceTemplates);
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $this->logger->error('Error processing class', ['class' => $className, 'error' => $e->getMessage()]);
        }
    }

    private function processMethod(\ReflectionMethod $method, \ReflectionAttribute $attribute, array &$tools, array &$resources, array &$prompts, array &$resourceTemplates): void
    {
        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        try {
            $instance = $attribute->newInstance();
            $docBlock = $this->docBlockParser->parseDocBlock($method->getDocComment() ?: null);
            $technicalName = $instance->name ?? ('__invoke' === $methodName ? $method->getDeclaringClass()->getShortName() : $methodName);
            $humanizedName = ($instance->title ?? null) ?: self::humanizeName($technicalName);

            $moduleName = $this->getModuleNameFromClass($className);
            if ($moduleName) {
                $technicalName = $moduleName . '-' . $technicalName;
            }

            if (strlen($technicalName) > 64) {
                $this->logger->warning('MCP item name exceeds 64 characters, skipping.', ['name' => $technicalName, 'length' => strlen($technicalName)]);

                return;
            }

            $description = $instance->description ?? $this->docBlockParser->getDescription($docBlock);
            $category = self::normalizeCategory($instance->meta['category'] ?? null);

            match ($attribute->getName()) {
                PsMcpTool::class => $this->handleTool($method, $instance, $technicalName, $humanizedName, $description, $category, $tools, $className, $methodName),
                PsMcpResource::class => $this->handleResource($instance, $technicalName, $humanizedName, $description, $category, $resources, $className, $methodName),
                PsMcpPrompt::class => $this->handlePrompt($method, $docBlock, $instance, $technicalName, $humanizedName, $description, $category, $prompts, $className, $methodName),
                PsMcpResourceTemplate::class => $this->handleResourceTemplate($method, $instance, $technicalName, $humanizedName, $description, $category, $resourceTemplates, $className, $methodName),
                default => null,
            };
        } catch (\Throwable $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $this->logger->error('Error in processMethod', ['method' => $methodName, 'error' => $e->getMessage()]);
        }
    }

    private function handleTool(\ReflectionMethod $method, PsMcpTool $instance, string $technicalName, string $humanizedName, ?string $description, string $category, array &$tools, string $className, string $methodName): void
    {
        $module = $this->getModuleFromClass($className);
        if (!$module) {
            return;
        }

        $toolInDb = $this->mcpFeaturesService->getFeatureByNameAndType('tool', $humanizedName);
        $tool = new Tool($technicalName, $instance->title ?? null, $this->schemaGenerator->generate($method), $description, $instance->annotations, $instance->icons, $instance->meta, $this->schemaGenerator->generateOutputSchema($method));
        $ref = new ToolReference($tool, [$className, $methodName], false);

        $moduleActive = $this->isModuleActive((int) $module->id);

        if (!$toolInDb) {
            $this->segment->trackMessage('Tool Discovered', [
                'tool_name' => $technicalName,
                'module_name' => $module->name,
                'module_version' => $module->version,
            ]);

            $this->mcpFeaturesService->registerFeature('tool', (int) $module->id, $humanizedName, $description, $category);
            if ($moduleActive) {
                $tools[$technicalName] = $ref;
                ++$this->discoveredItems['tools']['counts'];
            }
        } elseif ($toolInDb['is_active'] && $moduleActive) {
            $tools[$technicalName] = $ref;
            ++$this->discoveredItems['tools']['counts'];
        }

        $this->discoveredItems['tools']['entries'][] = ['name' => $humanizedName, 'module_id' => (int) $module->id];
    }

    private function handlePrompt(\ReflectionMethod $method, mixed $docBlock, PsMcpPrompt $instance, string $technicalName, string $humanizedName, ?string $description, string $category, array &$prompts, string $className, string $methodName): void
    {
        $module = $this->getModuleFromClass($className);
        if (!$module) {
            return;
        }

        $promptInDb = $this->mcpFeaturesService->getFeatureByNameAndType('prompt', $humanizedName);
        $arguments = $this->extractPromptArguments($method, $docBlock);

        $prompt = new Prompt($technicalName, $instance->title ?? null, $description, $arguments, $instance->icons, $instance->meta);
        $ref = new PromptReference($prompt, [$className, $methodName], false, $this->getCompletionProviders($method));

        $moduleActive = $this->isModuleActive((int) $module->id);

        if (!$promptInDb) {
            $this->segment->trackMessage('Prompt Discovered', [
                'prompt_name' => $technicalName,
                'module_name' => $module->name,
                'module_version' => $module->version,
            ]);

            $this->mcpFeaturesService->registerFeature('prompt', (int) $module->id, $humanizedName, $description, $category);
            if ($moduleActive) {
                $prompts[$technicalName] = $ref;
                ++$this->discoveredItems['prompts']['counts'];
            }
        } elseif ($promptInDb['is_active'] && $moduleActive) {
            $prompts[$technicalName] = $ref;
            ++$this->discoveredItems['prompts']['counts'];
        }

        $this->discoveredItems['prompts']['entries'][] = ['name' => $humanizedName, 'module_id' => (int) $module->id];
    }

    private function handleResource(PsMcpResource $instance, string $technicalName, string $humanizedName, ?string $description, string $category, array &$resources, string $className, string $methodName): void
    {
        $module = $this->getModuleFromClass($className);
        if (!$module) {
            return;
        }

        $resourceInDb = $this->mcpFeaturesService->getFeatureByNameAndType('resource', $humanizedName);
        $resource = new Resource($instance->uri, $technicalName, $description, $instance->mimeType, null, $instance->size, $instance->icons, $instance->meta);
        $ref = new ResourceReference($resource, [$className, $methodName], false);

        $moduleActive = $this->isModuleActive((int) $module->id);

        if (!$resourceInDb) {
            $this->segment->trackMessage('Resource Discovered', [
                'resource_name' => $technicalName,
                'module_name' => $module->name,
                'module_version' => $module->version,
            ]);

            $this->mcpFeaturesService->registerFeature('resource', (int) $module->id, $humanizedName, $description, $category);
            if ($moduleActive) {
                $resources[$instance->uri] = $ref;
                ++$this->discoveredItems['resources']['counts'];
            }
        } elseif ($resourceInDb['is_active'] && $moduleActive) {
            $resources[$instance->uri] = $ref;
            ++$this->discoveredItems['resources']['counts'];
        }

        $this->discoveredItems['resources']['entries'][] = ['name' => $humanizedName, 'module_id' => (int) $module->id];
    }

    private function handleResourceTemplate(\ReflectionMethod $method, PsMcpResourceTemplate $instance, string $technicalName, string $humanizedName, ?string $description, string $category, array &$resourceTemplates, string $className, string $methodName): void
    {
        $module = $this->getModuleFromClass($className);
        if (!$module) {
            return;
        }

        $templateInDb = $this->mcpFeaturesService->getFeatureByNameAndType('resourceTemplate', $humanizedName);
        $template = new ResourceTemplate($instance->uriTemplate, $technicalName, $description, $instance->mimeType, null, $instance->meta ?? null);

        $ref = new ResourceTemplateReference($template, [$className, $methodName], false, $this->getCompletionProviders($method));

        $moduleActive = $this->isModuleActive((int) $module->id);

        if (!$templateInDb) {
            $this->segment->trackMessage('Resource template Discovered', [
                'resource_template_name' => $technicalName,
                'module_name' => $module->name,
                'module_version' => $module->version,
            ]);

            $this->mcpFeaturesService->registerFeature('resourceTemplate', (int) $module->id, $humanizedName, $description, $category);
            if ($moduleActive) {
                $resourceTemplates[$instance->uriTemplate] = $ref;
                ++$this->discoveredItems['resourceTemplates']['counts'];
            }
        } elseif ($templateInDb['is_active'] && $moduleActive) {
            $resourceTemplates[$instance->uriTemplate] = $ref;
            ++$this->discoveredItems['resourceTemplates']['counts'];
        }

        $this->discoveredItems['resourceTemplates']['entries'][] = ['name' => $humanizedName, 'module_id' => (int) $module->id];
    }

    private function extractPromptArguments(\ReflectionMethod $method, $docBlock): array
    {
        $arguments = [];
        $paramTags = $this->docBlockParser->getParamTags($docBlock);
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                continue;
            }
            $desc = isset($paramTags['$' . $param->getName()]) ? trim((string) $paramTags['$' . $param->getName()]->getDescription()) : null;
            $arguments[] = new PromptArgument($param->getName(), $desc, !$param->isOptional() && !$param->isDefaultValueAvailable());
        }

        return $arguments;
    }

    private function getCompletionProviders(\ReflectionMethod $reflectionMethod): array
    {
        $completionProviders = [];
        foreach ($reflectionMethod->getParameters() as $param) {
            $reflectionType = $param->getType();
            if ($reflectionType instanceof \ReflectionNamedType && !$reflectionType->isBuiltin()) {
                continue;
            }

            $completionAttributes = $param->getAttributes(CompletionProvider::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (!empty($completionAttributes)) {
                $attributeInstance = $completionAttributes[0]->newInstance();

                if ($attributeInstance->provider) {
                    $completionProviders[$param->getName()] = $attributeInstance->provider;
                } elseif ($attributeInstance->providerClass) {
                    $providerClass = $attributeInstance->providerClass;
                    $completionProviders[$param->getName()] = new $providerClass();
                } elseif ($attributeInstance->values) {
                    $completionProviders[$param->getName()] = new ListCompletionProvider($attributeInstance->values);
                } elseif ($attributeInstance->enum) {
                    $completionProviders[$param->getName()] = new EnumCompletionProvider($attributeInstance->enum);
                }
            }
        }

        return $completionProviders;
    }

    private function isModuleActive(int $moduleId): bool
    {
        if (!isset($this->moduleActiveCache[$moduleId])) {
            $this->moduleActiveCache[$moduleId] = $this->mcpFeaturesService->isModuleActive($moduleId);
        }

        return $this->moduleActiveCache[$moduleId];
    }

    private function getModuleFromClass(string $className): ?\Module
    {
        $folder = $this->getModuleNameFromClass($className);

        return $folder ? \Module::getInstanceByName($folder) : null;
    }

    private function getClassFromFile(string $filePath): ?string
    {
        try {
            if (!file_exists($filePath) || !is_readable($filePath)) {
                $this->logger->warning('File does not exist or is not readable.', ['file' => $filePath]);
                $content = false;
            } else {
                $content = file_get_contents($filePath);
                if ($content !== false && strlen($content) > 500 * 1024) {
                    $this->logger->debug('Skipping large file during class discovery.', ['file' => $filePath]);
                    $content = false;
                }
            }

            if ($content === false) {
                return null;
            }

            $tokens = token_get_all($content);
        } catch (\Throwable $e) {
            \PsMcpServerDeps\Sentry\captureException($e);
            $this->logger->warning("Failed to read or tokenize file during class discovery: {$filePath}", ['exception' => $e->getMessage()]);

            return null;
        }

        $namespace = '';
        $namespaceFound = false;
        $level = 0;
        $potentialClasses = [];

        $tokenCount = count($tokens);
        for ($i = 0; $i < $tokenCount; ++$i) {
            if (is_array($tokens[$i]) && $tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $tokenCount; ++$j) {
                    if ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        $namespaceFound = true;
                        $i = $j;
                        break;
                    }
                    if (is_array($tokens[$j]) && in_array($tokens[$j][0], [T_STRING, T_NAME_QUALIFIED])) {
                        $namespace .= $tokens[$j][1];
                    } elseif ($tokens[$j][0] === T_NS_SEPARATOR) {
                        $namespace .= '\\';
                    }
                }
                if ($namespaceFound) {
                    break;
                }
            }
        }
        $namespace = trim($namespace, '\\');

        for ($i = 0; $i < $tokenCount; ++$i) {
            $token = $tokens[$i];
            if ($token === '{') {
                ++$level;

                continue;
            }
            if ($token === '}') {
                --$level;

                continue;
            }

            if (
                $level === ($namespaceFound && str_contains($content, "namespace {$namespace} {") ? 1 : 0)
                && is_array($token)
                && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, defined('T_ENUM') ? T_ENUM : -1])
            ) {
                for ($j = $i + 1; $j < $tokenCount; ++$j) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $className = $tokens[$j][1];
                        $potentialClasses[] = $namespace ? $namespace . '\\' . $className : $className;
                        $i = $j;
                        break;
                    }
                    if ($tokens[$j] === ';' || $tokens[$j] === '{' || $tokens[$j] === ')') {
                        break;
                    }
                }
            }
        }

        $resolvedClass = null;
        foreach ($potentialClasses as $potentialClass) {
            if (class_exists($potentialClass, true)) {
                $resolvedClass = $potentialClass;
                break;
            }
        }

        if ($resolvedClass === null && !empty($potentialClasses)) {
            if (!class_exists($potentialClasses[0], false)) {
                $this->logger->debug('getClassFromFile returning potential non-class type. Are you sure this class has been autoloaded?', ['file' => $filePath, 'type' => $potentialClasses[0]]);
            }
            $resolvedClass = $potentialClasses[0];
        }

        return $resolvedClass;
    }

    private static function humanizeName(string $name): string
    {
        $pos = strpos($name, '-');
        if ($pos !== false) {
            $name = substr($name, $pos + 1);
        }

        return ucfirst(str_replace('_', ' ', $name));
    }

    private static function normalizeCategory(?string $category): string
    {
        $category = $category ?? 'general';
        if (function_exists('transliterator_transliterate')) {
            $category = transliterator_transliterate('Any-Latin; Latin-ASCII', $category) ?: $category;
        } else {
            $category = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $category) ?: $category;
        }

        return mb_strtolower(trim($category));
    }

    private function getModuleNameFromClass(string $className): ?string
    {
        try {
            $ref = new \ReflectionClass($className);
            $filePath = $ref->getFileName();

            $pos = $filePath ? strpos(str_replace('\\', '/', $filePath), '/modules/') : false;

            if ($pos === false) {
                return null;
            }

            $relativePath = substr(str_replace('\\', '/', $filePath), $pos + 9);
            $parts = explode('/', $relativePath);

            return $parts[0] ?? null;
        } catch (\Throwable $e) {
            $this->logger->debug('Could not determine module name from class', [
                'class' => $className,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
