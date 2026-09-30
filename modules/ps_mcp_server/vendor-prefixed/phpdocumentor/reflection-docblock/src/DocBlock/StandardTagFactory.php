<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Author;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Covers;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Generic;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Link as LinkTag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Method;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Mixin;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Param;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Property;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\PropertyWrite;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Return_;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\See as SeeTag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Since;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Source;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\TemplateCovariant;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Throws;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Uses;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Var_;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Version;
use PsMcpServerDeps\phpDocumentor\Reflection\FqsenResolver;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context as TypeContext;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class StandardTagFactory implements TagFactory
{
    public const REGEX_TAGNAME = '[\w\-\_\\\\:]+';

    private array $tagHandlerMappings = [
        'author' => Author::class,
        'covers' => Covers::class,
        'deprecated' => Deprecated::class,

        'link' => LinkTag::class,
        'mixin' => Mixin::class,
        'method' => Method::class,
        'param' => Param::class,
        'property-read' => PropertyRead::class,
        'property' => Property::class,
        'property-write' => PropertyWrite::class,
        'return' => Return_::class,
        'see' => SeeTag::class,
        'since' => Since::class,
        'source' => Source::class,
        'template-covariant' => TemplateCovariant::class,
        'throw' => Throws::class,
        'throws' => Throws::class,
        'uses' => Uses::class,
        'var' => Var_::class,
        'version' => Version::class,
    ];

    private array $annotationMappings = [];

    private array $tagHandlerParameterCache = [];

    private FqsenResolver $fqsenResolver;

    private array $serviceLocator = [];

    public function __construct(FqsenResolver $fqsenResolver, ?array $tagHandlers = null)
    {
        $this->fqsenResolver = $fqsenResolver;
        if ($tagHandlers !== null) {
            $this->tagHandlerMappings = $tagHandlers;
        }

        $this->addService($fqsenResolver, FqsenResolver::class);
    }

    public function create(string $tagLine, ?TypeContext $context = null): Tag
    {
        if (!$context) {
            $context = new TypeContext('');
        }

        [$tagName, $tagBody] = $this->extractTagParts($tagLine);

        return $this->createTag(\trim($tagBody), $tagName, $context);
    }

    public function addParameter(string $name, $value): void
    {
        $this->serviceLocator[$name] = $value;
    }

    public function addService(object $service, ?string $alias = null): void
    {
        $this->serviceLocator[$alias ?? \get_class($service)] = $service;
    }

    public function registerTagHandler(string $tagName, $handler): void
    {
        Assert::stringNotEmpty($tagName);
        if (\strpos($tagName, '\\') !== false && $tagName[0] !== '\\') {
            throw new \InvalidArgumentException('A namespaced tag must have a leading backslash as it must be fully qualified');
        }

        if (\is_object($handler)) {
            Assert::isInstanceOf($handler, Factory::class);
            $this->tagHandlerMappings[$tagName] = $handler;

            return;
        }

        Assert::classExists($handler);
        Assert::implementsInterface($handler, Tag::class);
        $this->tagHandlerMappings[$tagName] = $handler;
    }

    private function extractTagParts(string $tagLine): array
    {
        $matches = [];
        if (!\preg_match('/^@(' . self::REGEX_TAGNAME . ')((?:[\s\(\{])\s*([^\s].*)|$)/us', $tagLine, $matches)) {
            throw new \InvalidArgumentException('The tag "' . $tagLine . '" does not seem to be wellformed, please check it for errors');
        }

        return \array_slice($matches, 1);
    }

    private function createTag(string $body, string $name, TypeContext $context): Tag
    {
        $handlerClassName = $this->findHandlerClassName($name, $context);
        $arguments = $this->getArgumentsForParametersFromWiring(
            $this->fetchParametersForHandlerFactoryMethod($handlerClassName),
            $this->getServiceLocatorWithDynamicParameters($context, $name, $body)
        );

        if (\array_key_exists('tagLine', $arguments)) {
            $arguments['tagLine'] = \sprintf('@%s %s', $name, $body);
        }

        try {
            $callable = [$handlerClassName, 'create'];
            Assert::isCallable($callable);

            $tag = \call_user_func_array($callable, $arguments);

            return $tag ?? InvalidTag::create($body, $name);
        } catch (\InvalidArgumentException $e) {
            return InvalidTag::create($body, $name)->withError($e);
        }
    }

    private function findHandlerClassName(string $tagName, TypeContext $context)
    {
        $handlerClassName = Generic::class;
        if (isset($this->tagHandlerMappings[$tagName])) {
            $handlerClassName = $this->tagHandlerMappings[$tagName];
        } elseif ($this->isAnnotation($tagName)) {
            $tagName = (string) $this->fqsenResolver->resolve($tagName, $context);
            if (isset($this->annotationMappings[$tagName])) {
                $handlerClassName = $this->annotationMappings[$tagName];
            }
        }

        return $handlerClassName;
    }

    private function getArgumentsForParametersFromWiring(array $parameters, array $locator): array
    {
        $arguments = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            $typeHint = null;
            if ($type instanceof \ReflectionNamedType) {
                $typeHint = $type->getName();
                if ($typeHint === 'self') {
                    $declaringClass = $parameter->getDeclaringClass();
                    if ($declaringClass !== null) {
                        $typeHint = $declaringClass->getName();
                    }
                }
            }

            $parameterName = $parameter->getName();
            if (isset($locator[$typeHint ?? ''])) {
                $arguments[$parameterName] = $locator[$typeHint ?? ''];
                continue;
            }

            if (isset($locator[$parameterName])) {
                $arguments[$parameterName] = $locator[$parameterName];
                continue;
            }

            $arguments[$parameterName] = null;
        }

        return $arguments;
    }

    private function fetchParametersForHandlerFactoryMethod($handler): array
    {
        $handlerClassName = \is_object($handler) ? \get_class($handler) : $handler;

        if (!isset($this->tagHandlerParameterCache[$handlerClassName])) {
            $methodReflection = new \ReflectionMethod($handlerClassName, 'create');
            $this->tagHandlerParameterCache[$handlerClassName] = $methodReflection->getParameters();
        }

        return $this->tagHandlerParameterCache[$handlerClassName];
    }

    private function getServiceLocatorWithDynamicParameters(
        TypeContext $context,
        string $tagName,
        string $tagBody,
    ): array {
        return \array_merge(
            $this->serviceLocator,
            [
                'name' => $tagName,
                'body' => $tagBody,
                TypeContext::class => $context,
            ]
        );
    }

    private function isAnnotation(string $tagContent): bool
    {
        return false;
    }
}
