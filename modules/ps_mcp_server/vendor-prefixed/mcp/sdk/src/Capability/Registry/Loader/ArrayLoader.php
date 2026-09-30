<?php

namespace PsMcpServerDeps\Mcp\Capability\Registry\Loader;

use PsMcpServerDeps\Mcp\Capability\Attribute\CompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Completion\EnumCompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Completion\ListCompletionProvider;
use PsMcpServerDeps\Mcp\Capability\Discovery\DocBlockParser;
use PsMcpServerDeps\Mcp\Capability\Discovery\HandlerResolver;
use PsMcpServerDeps\Mcp\Capability\Discovery\SchemaGenerator;
use PsMcpServerDeps\Mcp\Capability\Discovery\SchemaGeneratorInterface;
use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Exception\ConfigurationException;
use PsMcpServerDeps\Mcp\Schema\Prompt;
use PsMcpServerDeps\Mcp\Schema\PromptArgument;
use PsMcpServerDeps\Mcp\Schema\Resource;
use PsMcpServerDeps\Mcp\Schema\ResourceTemplate;
use PsMcpServerDeps\Mcp\Schema\Tool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ArrayLoader implements LoaderInterface
{
    public function __construct(
        private readonly array $tools = [],
        private readonly array $resources = [],
        private readonly array $resourceTemplates = [],
        private readonly array $prompts = [],
        private LoggerInterface $logger = new NullLogger(),
        private ?SchemaGeneratorInterface $schemaGenerator = null,
    ) {
    }

    public function load(RegistryInterface $registry): void
    {
        $docBlockParser = new DocBlockParser(logger: $this->logger);
        $schemaGenerator = $this->schemaGenerator ?? new SchemaGenerator($docBlockParser);

        foreach ($this->tools as $data) {
            try {
                $reflection = HandlerResolver::resolve($data['handler']);

                if ($reflection instanceof \ReflectionFunction) {
                    $name = $data['name'] ?? 'closure_tool_' . spl_object_id($data['handler']);
                    $description = $data['description'] ?? null;
                } else {
                    $classShortName = $reflection->getDeclaringClass()->getShortName();
                    $methodName = $reflection->getName();
                    $docBlock = $docBlockParser->parseDocBlock($reflection->getDocComment() ?? null);

                    $name = $data['name'] ?? ('__invoke' === $methodName ? $classShortName : $methodName);
                    $description = $data['description'] ?? $docBlockParser->getDescription($docBlock) ?? null;
                }

                $inputSchema = $data['inputSchema'] ?? $schemaGenerator->generate($reflection);

                $tool = new Tool(
                    name: $name,
                    title: $data['title'] ?? null,
                    inputSchema: $inputSchema,
                    description: $description,
                    annotations: $data['annotations'] ?? null,
                    icons: $data['icons'] ?? null,
                    meta: $data['meta'] ?? null,
                    outputSchema: $data['outputSchema'] ?? null,
                );
                $registry->registerTool($tool, $data['handler'], true);

                $handlerDesc = $this->getHandlerDescription($data['handler']);
                $this->logger->debug("Registered manual tool {$name} from handler {$handlerDesc}");
            } catch (\Throwable $e) {
                $this->logger->error(
                    'Failed to register manual tool',
                    ['handler' => $data['handler'], 'name' => $data['name'], 'exception' => $e],
                );
                throw new ConfigurationException("Error registering manual tool '{$data['name']}': {$e->getMessage()}", 0, $e);
            }
        }

        foreach ($this->resources as $data) {
            try {
                $reflection = HandlerResolver::resolve($data['handler']);

                if ($reflection instanceof \ReflectionFunction) {
                    $name = $data['name'] ?? 'closure_resource_' . spl_object_id($data['handler']);
                    $description = $data['description'] ?? null;
                } else {
                    $classShortName = $reflection->getDeclaringClass()->getShortName();
                    $methodName = $reflection->getName();
                    $docBlock = $docBlockParser->parseDocBlock($reflection->getDocComment() ?? null);

                    $name = $data['name'] ?? ('__invoke' === $methodName ? $classShortName : $methodName);
                    $description = $data['description'] ?? $docBlockParser->getDescription($docBlock) ?? null;
                }

                $resource = new Resource(
                    uri: $data['uri'],
                    name: $name,
                    description: $description,
                    mimeType: $data['mimeType'] ?? null,
                    annotations: $data['annotations'] ?? null,
                    size: $data['size'] ?? null,
                    icons: $data['icons'] ?? null,
                    meta: $data['meta'] ?? null,
                );
                $registry->registerResource($resource, $data['handler'], true);

                $handlerDesc = $this->getHandlerDescription($data['handler']);
                $this->logger->debug("Registered manual resource {$name} from handler {$handlerDesc}");
            } catch (\Throwable $e) {
                $this->logger->error(
                    'Failed to register manual resource',
                    ['handler' => $data['handler'], 'uri' => $data['uri'], 'exception' => $e],
                );
                throw new ConfigurationException("Error registering manual resource '{$data['uri']}': {$e->getMessage()}", 0, $e);
            }
        }

        foreach ($this->resourceTemplates as $data) {
            try {
                $reflection = HandlerResolver::resolve($data['handler']);

                if ($reflection instanceof \ReflectionFunction) {
                    $name = $data['name'] ?? 'closure_template_' . spl_object_id($data['handler']);
                    $description = $data['description'] ?? null;
                } else {
                    $classShortName = $reflection->getDeclaringClass()->getShortName();
                    $methodName = $reflection->getName();
                    $docBlock = $docBlockParser->parseDocBlock($reflection->getDocComment() ?? null);

                    $name = $data['name'] ?? ('__invoke' === $methodName ? $classShortName : $methodName);
                    $description = $data['description'] ?? $docBlockParser->getDescription($docBlock) ?? null;
                }

                $template = new ResourceTemplate(
                    uriTemplate: $data['uriTemplate'],
                    name: $name,
                    description: $description,
                    mimeType: $data['mimeType'] ?? null,
                    annotations: $data['annotations'] ?? null,
                    meta: $data['meta'] ?? null,
                );
                $completionProviders = $this->getCompletionProviders($reflection);
                $registry->registerResourceTemplate($template, $data['handler'], $completionProviders, true);

                $handlerDesc = $this->getHandlerDescription($data['handler']);
                $this->logger->debug("Registered manual template {$name} from handler {$handlerDesc}");
            } catch (\Throwable $e) {
                $this->logger->error(
                    'Failed to register manual template',
                    ['handler' => $data['handler'], 'uriTemplate' => $data['uriTemplate'], 'exception' => $e],
                );
                throw new ConfigurationException("Error registering manual resource template '{$data['uriTemplate']}': {$e->getMessage()}", 0, $e);
            }
        }

        foreach ($this->prompts as $data) {
            try {
                $reflection = HandlerResolver::resolve($data['handler']);

                if ($reflection instanceof \ReflectionFunction) {
                    $name = $data['name'] ?? 'closure_prompt_' . spl_object_id($data['handler']);
                    $description = $data['description'] ?? null;
                } else {
                    $classShortName = $reflection->getDeclaringClass()->getShortName();
                    $methodName = $reflection->getName();
                    $docBlock = $docBlockParser->parseDocBlock($reflection->getDocComment() ?? null);

                    $name = $data['name'] ?? ('__invoke' === $methodName ? $classShortName : $methodName);
                    $description = $data['description'] ?? $docBlockParser->getDescription($docBlock) ?? null;
                }

                $arguments = [];
                $paramTags = $reflection instanceof \ReflectionMethod ? $docBlockParser->getParamTags(
                    $docBlockParser->parseDocBlock($reflection->getDocComment() ?? null),
                ) : [];
                foreach ($reflection->getParameters() as $param) {
                    $reflectionType = $param->getType();

                    if ($reflectionType instanceof \ReflectionNamedType && !$reflectionType->isBuiltin()) {
                        continue;
                    }

                    $paramTag = $paramTags['$' . $param->getName()] ?? null;
                    $arguments[] = new PromptArgument(
                        $param->getName(),
                        $paramTag ? trim((string) $paramTag->getDescription()) : null,
                        !$param->isOptional() && !$param->isDefaultValueAvailable(),
                    );
                }
                $prompt = new Prompt(
                    name: $name,
                    title: $data['title'] ?? null,
                    description: $description,
                    arguments: $arguments,
                    icons: $data['icons'] ?? null,
                    meta: $data['meta'] ?? null
                );
                $completionProviders = $this->getCompletionProviders($reflection);
                $registry->registerPrompt($prompt, $data['handler'], $completionProviders, true);

                $handlerDesc = $this->getHandlerDescription($data['handler']);
                $this->logger->debug("Registered manual prompt {$name} from handler {$handlerDesc}");
            } catch (\Throwable $e) {
                $this->logger->error(
                    'Failed to register manual prompt',
                    ['handler' => $data['handler'], 'name' => $data['name'], 'exception' => $e],
                );
                throw new ConfigurationException("Error registering manual prompt '{$data['name']}': {$e->getMessage()}", 0, $e);
            }
        }

        $this->logger->debug('Manual element registration complete.');
    }

    private function getHandlerDescription(\Closure|array|string $handler): string
    {
        if ($handler instanceof \Closure) {
            return 'Closure';
        }

        if (\is_array($handler)) {
            return \sprintf(
                '%s::%s',
                \is_object($handler[0]) ? $handler[0]::class : $handler[0],
                $handler[1],
            );
        }

        return (string) $handler;
    }

    private function getCompletionProviders(\ReflectionMethod|\ReflectionFunction $reflection): array
    {
        $completionProviders = [];
        foreach ($reflection->getParameters() as $param) {
            $reflectionType = $param->getType();
            if ($reflectionType instanceof \ReflectionNamedType && !$reflectionType->isBuiltin()) {
                continue;
            }

            $completionAttributes = $param->getAttributes(
                CompletionProvider::class,
                \ReflectionAttribute::IS_INSTANCEOF,
            );
            if (!empty($completionAttributes)) {
                $attributeInstance = $completionAttributes[0]->newInstance();

                if ($attributeInstance->provider) {
                    $completionProviders[$param->getName()] = $attributeInstance->provider;
                } elseif ($attributeInstance->providerClass) {
                    $completionProviders[$param->getName()] = $attributeInstance->providerClass;
                } elseif ($attributeInstance->values) {
                    $completionProviders[$param->getName()] = new ListCompletionProvider($attributeInstance->values);
                } elseif ($attributeInstance->enum) {
                    $completionProviders[$param->getName()] = new EnumCompletionProvider($attributeInstance->enum);
                }
            }
        }

        return $completionProviders;
    }
}
