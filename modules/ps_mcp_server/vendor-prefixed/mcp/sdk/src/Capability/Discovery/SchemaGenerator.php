<?php

namespace PsMcpServerDeps\Mcp\Capability\Discovery;

use PsMcpServerDeps\Mcp\Capability\Attribute\McpTool;
use PsMcpServerDeps\Mcp\Capability\Attribute\Schema;
use PsMcpServerDeps\Mcp\Exception\BadMethodCallException;
use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Server\RequestContext;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Param;

final class SchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private readonly DocBlockParser $docBlockParser,
    ) {
    }

    public function generate(\Reflector $reflection): array
    {
        if ($reflection instanceof \ReflectionClass) {
            throw new BadMethodCallException('Schema generation from ReflectionClass is not implemented yet. Use ReflectionMethod or ReflectionFunction instead.');
        }

        if (!$reflection instanceof \ReflectionMethod && !$reflection instanceof \ReflectionFunction) {
            throw new BadMethodCallException(\sprintf('Schema generation from %s is not supported. Use ReflectionMethod or ReflectionFunction instead.', $reflection::class));
        }

        $methodSchema = $this->extractMethodLevelSchema($reflection);

        if ($methodSchema && isset($methodSchema['definition'])) {
            return $methodSchema['definition'];
        }

        $parametersInfo = $this->parseParametersInfo($reflection);

        return $this->buildSchemaFromParameters($parametersInfo, $methodSchema);
    }

    public function generateOutputSchema(\Reflector $reflection): ?array
    {
        if ($reflection instanceof \ReflectionClass) {
            throw new BadMethodCallException('Schema generation from ReflectionClass is not implemented yet. Use ReflectionMethod or ReflectionFunction instead.');
        }

        if (!$reflection instanceof \ReflectionMethod && !$reflection instanceof \ReflectionFunction) {
            throw new BadMethodCallException(\sprintf('Schema generation from %s is not supported. Use ReflectionMethod or ReflectionFunction instead.', $reflection::class));
        }

        $mcpToolAttrs = $reflection->getAttributes(McpTool::class, \ReflectionAttribute::IS_INSTANCEOF);
        if ($mcpToolAttrs) {
            $mcpToolInstance = $mcpToolAttrs[0]->newInstance();

            return $mcpToolInstance->outputSchema;
        }

        return null;
    }

    private function extractMethodLevelSchema(\ReflectionFunctionAbstract $reflection): ?array
    {
        $schemaAttrs = $reflection->getAttributes(Schema::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (empty($schemaAttrs)) {
            return null;
        }

        $schemaAttr = $schemaAttrs[0]->newInstance();

        return $schemaAttr->toArray();
    }

    private function extractParameterLevelSchema(\ReflectionParameter $parameter): array
    {
        $schemaAttrs = $parameter->getAttributes(Schema::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (empty($schemaAttrs)) {
            return [];
        }

        $schemaAttr = $schemaAttrs[0]->newInstance();

        return $schemaAttr->toArray();
    }

    private function buildSchemaFromParameters(array $parametersInfo, ?array $methodSchema): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        if ($methodSchema) {
            $schema = array_merge($schema, $methodSchema);
            if (!isset($schema['type'])) {
                $schema['type'] = 'object';
            }
            if (!isset($schema['properties'])) {
                $schema['properties'] = [];
            }
            if (!isset($schema['required'])) {
                $schema['required'] = [];
            }
        }

        foreach ($parametersInfo as $paramInfo) {
            $paramName = $paramInfo['name'];

            $methodLevelParamSchema = $schema['properties'][$paramName] ?? null;

            $paramSchema = $this->buildParameterSchema($paramInfo, $methodLevelParamSchema);

            $schema['properties'][$paramName] = $paramSchema;

            if ($paramInfo['required'] && !\in_array($paramName, $schema['required'])) {
                $schema['required'][] = $paramName;
            } elseif (!$paramInfo['required'] && ($key = array_search($paramName, $schema['required'])) !== false) {
                unset($schema['required'][$key]);
                $schema['required'] = array_values($schema['required']);
            }
        }

        if (empty($schema['properties'])) {
            $schema['properties'] = new \stdClass();
        }
        if (empty($schema['required'])) {
            unset($schema['required']);
        }

        return $schema;
    }

    private function buildParameterSchema(array $paramInfo, ?array $methodLevelParamSchema): array
    {
        if ($paramInfo['is_variadic']) {
            return $this->buildVariadicParameterSchema($paramInfo);
        }

        $inferredSchema = $this->buildInferredParameterSchema($paramInfo);

        $mergedSchema = $inferredSchema;
        if ($methodLevelParamSchema) {
            $mergedSchema = array_merge($inferredSchema, $methodLevelParamSchema);
        }

        $parameterLevelSchema = $paramInfo['parameter_schema'];
        if (!empty($parameterLevelSchema)) {
            $mergedSchema = array_merge($mergedSchema, $parameterLevelSchema);
        }

        return $mergedSchema;
    }

    private function buildInferredParameterSchema(array $paramInfo): array
    {
        $paramSchema = [];

        if ($paramInfo['is_variadic']) {
            return [];
        }

        $jsonTypes = $this->inferParameterTypes($paramInfo);

        if (1 === \count($jsonTypes)) {
            $paramSchema['type'] = $jsonTypes[0];
        } elseif (\count($jsonTypes) > 1) {
            $paramSchema['type'] = $jsonTypes;
        }

        if ($paramInfo['description']) {
            $paramSchema['description'] = $paramInfo['description'];
        }

        if ($paramInfo['has_default']) {
            $paramSchema['default'] = $paramInfo['default_value'];
        }

        $paramSchema = $this->applyEnumConstraints($paramSchema, $paramInfo);

        return $this->applyArrayConstraints($paramSchema, $paramInfo);
    }

    private function buildVariadicParameterSchema(array $paramInfo): array
    {
        $paramSchema = ['type' => 'array'];

        if (!empty($paramInfo['parameter_schema'])) {
            $paramSchema = array_merge($paramSchema, $paramInfo['parameter_schema']);

            $paramSchema['type'] = 'array';
        }

        if ($paramInfo['description']) {
            $paramSchema['description'] = $paramInfo['description'];
        }

        if (!isset($paramSchema['items'])) {
            $itemJsonTypes = $this->mapPhpTypeToJsonSchemaType($paramInfo['type_string']);
            $nonNullItemTypes = array_filter($itemJsonTypes, static fn ($t) => 'null' !== $t);

            if (1 === \count($nonNullItemTypes)) {
                $paramSchema['items'] = ['type' => $nonNullItemTypes[0]];
            }
        }

        return $paramSchema;
    }

    private function inferParameterTypes(array $paramInfo): array
    {
        $jsonTypes = $this->mapPhpTypeToJsonSchemaType($paramInfo['type_string']);

        if ($paramInfo['allows_null'] && 'mixed' !== strtolower($paramInfo['type_string']) && !\in_array('null', $jsonTypes)) {
            $jsonTypes[] = 'null';
        }

        if (\count($jsonTypes) > 1) {
            $nullIndex = array_search('null', $jsonTypes);
            if (false !== $nullIndex) {
                unset($jsonTypes[$nullIndex]);
                sort($jsonTypes);
                array_unshift($jsonTypes, 'null');
            } else {
                sort($jsonTypes);
            }
        }

        return $jsonTypes;
    }

    private function applyEnumConstraints(array $paramSchema, array $paramInfo): array
    {
        $reflectionType = $paramInfo['reflection_type_object'];

        if (!($reflectionType instanceof \ReflectionNamedType) || $reflectionType->isBuiltin() || !enum_exists($reflectionType->getName())) {
            return $paramSchema;
        }

        $enumClass = $reflectionType->getName();
        $enumReflection = new \ReflectionEnum($enumClass);
        $backingTypeReflection = $enumReflection->getBackingType();

        if ($enumReflection->isBacked() && $backingTypeReflection instanceof \ReflectionNamedType) {
            $paramSchema['enum'] = array_column($enumClass::cases(), 'value');
            $jsonBackingType = match ($backingTypeReflection->getName()) {
                'int' => 'integer',
                'string' => 'string',
                default => null,
            };

            if ($jsonBackingType) {
                if (isset($paramSchema['type']) && \is_array($paramSchema['type']) && \in_array('null', $paramSchema['type'])) {
                    $paramSchema['type'] = ['null', $jsonBackingType];
                } else {
                    $paramSchema['type'] = $jsonBackingType;
                }
            }
        } else {
            $paramSchema['enum'] = array_column($enumClass::cases(), 'name');
            if (isset($paramSchema['type']) && \is_array($paramSchema['type']) && \in_array('null', $paramSchema['type'])) {
                $paramSchema['type'] = ['null', 'string'];
            } else {
                $paramSchema['type'] = 'string';
            }
        }

        return $paramSchema;
    }

    private function applyArrayConstraints(array $paramSchema, array $paramInfo): array
    {
        if (!isset($paramSchema['type'])) {
            return $paramSchema;
        }

        $typeString = $paramInfo['type_string'];
        $allowsNull = $paramInfo['allows_null'];

        if (preg_match('/^array\s*{/i', $typeString)) {
            $objectSchema = $this->inferArrayItemsType($typeString);
            if (\is_array($objectSchema) && isset($objectSchema['properties'])) {
                $paramSchema = array_merge($paramSchema, $objectSchema);
                $paramSchema['type'] = $allowsNull ? ['object', 'null'] : 'object';
            }
        } elseif (\in_array('array', $this->mapPhpTypeToJsonSchemaType($typeString))) {
            $itemsType = $this->inferArrayItemsType($typeString);
            if ('any' !== $itemsType) {
                if (\is_string($itemsType)) {
                    $paramSchema['items'] = ['type' => $itemsType];
                } else {
                    if (!isset($itemsType['type']) && isset($itemsType['properties'])) {
                        $itemsType = array_merge(['type' => 'object'], $itemsType);
                    }
                    $paramSchema['items'] = $itemsType;
                }
            }

            if ($allowsNull) {
                $paramSchema['type'] = ['array', 'null'];
                sort($paramSchema['type']);
            } else {
                $paramSchema['type'] = 'array';
            }
        }

        return $paramSchema;
    }

    private function parseParametersInfo(\ReflectionMethod|\ReflectionFunction $reflection): array
    {
        $docComment = $reflection->getDocComment() ?: null;
        $docBlock = $this->docBlockParser->parseDocBlock($docComment);
        $paramTags = $this->docBlockParser->getParamTags($docBlock);
        $parametersInfo = [];

        foreach ($reflection->getParameters() as $rp) {
            $reflectionType = $rp->getType();

            if ($reflectionType instanceof \ReflectionNamedType && !$reflectionType->isBuiltin()) {
                $typeName = $reflectionType->getName();

                if (is_a($typeName, RequestContext::class, true)) {
                    continue;
                }
            }

            $paramName = $rp->getName();
            if (\in_array(strtolower($paramName), ['_session', '_request'], true)) {
                throw new InvalidArgumentException(\sprintf('Handler method "%s::%s" has parameter named "%s" which is not allowed. Please change the name of that parameter.', $reflection->class, $reflection->name, $paramName));
            }
            $paramTag = $paramTags['$' . $paramName] ?? null;

            $typeString = $this->getParameterTypeString($rp, $paramTag);
            $description = $this->docBlockParser->getParamDescription($paramTag);
            $hasDefault = $rp->isDefaultValueAvailable();
            $defaultValue = $hasDefault ? $rp->getDefaultValue() : null;
            $isVariadic = $rp->isVariadic();

            $parameterSchema = $this->extractParameterLevelSchema($rp);

            if ($defaultValue instanceof \BackedEnum) {
                $defaultValue = $defaultValue->value;
            }

            if ($defaultValue instanceof \UnitEnum) {
                $defaultValue = $defaultValue->name;
            }

            $allowsNull = false;
            if ($reflectionType && $reflectionType->allowsNull()) {
                $allowsNull = true;
            } elseif ($hasDefault && null === $defaultValue) {
                $allowsNull = true;
            } elseif (str_contains($typeString, 'null') || 'mixed' === strtolower($typeString)) {
                $allowsNull = true;
            }

            $parametersInfo[] = [
                'name' => $paramName,
                'doc_block_tag' => $paramTag,
                'reflection_param' => $rp,
                'reflection_type_object' => $reflectionType,
                'type_string' => $typeString,
                'description' => $description,
                'required' => !$rp->isOptional(),
                'allows_null' => $allowsNull,
                'default_value' => $defaultValue,
                'has_default' => $hasDefault,
                'is_variadic' => $isVariadic,
                'parameter_schema' => $parameterSchema,
            ];
        }

        return $parametersInfo;
    }

    private function getParameterTypeString(\ReflectionParameter $rp, ?Param $paramTag): string
    {
        $docBlockType = $this->docBlockParser->getParamTypeString($paramTag);
        $isDocBlockTypeGeneric = false;

        if (null !== $docBlockType) {
            if (\in_array(strtolower($docBlockType), ['mixed', 'unknown', ''])) {
                $isDocBlockTypeGeneric = true;
            }
        } else {
            $isDocBlockTypeGeneric = true;
        }

        $reflectionType = $rp->getType();
        $reflectionTypeString = null;
        if ($reflectionType) {
            $reflectionTypeString = $this->getTypeStringFromReflection($reflectionType, $rp->allowsNull());
        }

        if ($isDocBlockTypeGeneric && null !== $reflectionTypeString && 'mixed' !== $reflectionTypeString) {
            return $reflectionTypeString;
        }

        if (null !== $docBlockType && !$isDocBlockTypeGeneric) {
            if (false !== stripos($docBlockType, 'null') && $reflectionTypeString && false === stripos($reflectionTypeString, 'null') && !str_ends_with($reflectionTypeString, '|null')) {
                if ('mixed' !== $reflectionTypeString) {
                    return $reflectionTypeString . '|null';
                }
            }

            return $docBlockType;
        }

        if (null !== $reflectionTypeString) {
            return $reflectionTypeString;
        }

        return 'mixed';
    }

    private function getTypeStringFromReflection(?\ReflectionType $type, bool $nativeAllowsNull): string
    {
        if (null === $type) {
            return 'mixed';
        }

        $types = [];
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $innerType) {
                $types[] = $this->getTypeStringFromReflection($innerType, $innerType->allowsNull());
            }
            if ($nativeAllowsNull) {
                $types = array_filter($types, static fn ($t) => 'null' !== strtolower($t));
            }
            $typeString = implode('|', array_unique(array_filter($types)));
        } elseif ($type instanceof \ReflectionIntersectionType) {
            foreach ($type->getTypes() as $innerType) {
                $types[] = $this->getTypeStringFromReflection($innerType, false);
            }
            $typeString = implode('&', array_unique(array_filter($types)));
        } elseif ($type instanceof \ReflectionNamedType) {
            $typeString = $type->getName();
        } else {
            return 'mixed';
        }

        $typeString = match (strtolower($typeString)) {
            'bool' => 'boolean',
            'int' => 'integer',
            'float', 'double' => 'number',
            'str' => 'string',
            default => $typeString,
        };

        $isNullable = $nativeAllowsNull;
        if ($type instanceof \ReflectionNamedType && 'mixed' === $type->getName()) {
            $isNullable = true;
        }

        if ($type instanceof \ReflectionUnionType && !$nativeAllowsNull) {
            foreach ($type->getTypes() as $innerType) {
                if ($innerType instanceof \ReflectionNamedType && 'null' === strtolower($innerType->getName())) {
                    $isNullable = true;
                    break;
                }
            }
        }

        if ($isNullable && 'mixed' !== $typeString && false === stripos($typeString, 'null')) {
            if (!str_ends_with($typeString, '|null') && !str_ends_with($typeString, '&null')) {
                $typeString .= '|null';
            }
        }

        if (str_contains($typeString, '\\')) {
            $parts = preg_split('/([|&])/', $typeString, -1, \PREG_SPLIT_DELIM_CAPTURE);
            $processedParts = array_map(static fn ($part) => str_starts_with($part, '\\') ? ltrim($part, '\\') : $part, $parts);
            $typeString = implode('', $processedParts);
        }

        return $typeString ?: 'mixed';
    }

    private function mapPhpTypeToJsonSchemaType(string $phpTypeString): array
    {
        $normalizedType = strtolower(trim($phpTypeString));

        if (preg_match('/^array\s*{/i', $normalizedType)) {
            return ['object'];
        }

        if (
            str_contains($normalizedType, '[]')
            || preg_match('/^(array|list|iterable|collection)</i', $normalizedType)
        ) {
            return ['array'];
        }

        if (str_contains($normalizedType, '|')) {
            $types = explode('|', $normalizedType);
            $jsonTypes = [];
            foreach ($types as $type) {
                $mapped = $this->mapPhpTypeToJsonSchemaType(trim($type));
                $jsonTypes = array_merge($jsonTypes, $mapped);
            }

            return array_values(array_unique($jsonTypes));
        }

        return match ($normalizedType) {
            'string', 'scalar' => ['string'],
            '?string' => ['null', 'string'],
            'int', 'integer' => ['integer'],
            '?int', '?integer' => ['null', 'integer'],
            'float', 'double', 'number' => ['number'],
            '?float', '?double', '?number' => ['null', 'number'],
            'bool', 'boolean' => ['boolean'],
            '?bool', '?boolean' => ['null', 'boolean'],
            'array' => ['array'],
            '?array' => ['null', 'array'],
            'object', 'stdclass' => ['object'],
            '?object', '?stdclass' => ['null', 'object'],
            'null' => ['null'],
            'resource', 'callable' => ['object'],
            'mixed' => [],
            'void', 'never' => [],
            default => ['object'],
        };
    }

    private function inferArrayItemsType(string $phpTypeString): string|array
    {
        $normalizedType = trim($phpTypeString);

        if (preg_match('/^(\\??)([\w\\\\]+)\\s*\\[\\]$/i', $normalizedType, $matches)) {
            $itemType = strtolower($matches[2]);

            return $this->mapSimpleTypeToJsonSchema($itemType);
        }

        if (preg_match('/^(\\??)array\s*<\s*([\w\\\\|]+)\s*>$/i', $normalizedType, $matches)) {
            $itemType = strtolower($matches[2]);

            return $this->mapSimpleTypeToJsonSchema($itemType);
        }

        if (
            preg_match('/^(\\??)array\s*<\s*array\s*<\s*([\w\\\\|]+)\s*>\s*>$/i', $normalizedType, $matches)
            || preg_match('/^(\\??)([\w\\\\]+)\s*\[\]\[\]$/i', $normalizedType, $matches)
        ) {
            $innerType = $this->mapSimpleTypeToJsonSchema(isset($matches[2]) ? strtolower($matches[2]) : 'any');

            return [
                'type' => 'array',
                'items' => [
                    'type' => $innerType,
                ],
            ];
        }

        if (preg_match('/^(\\??)array\s*\{(.+)\}$/is', $normalizedType, $matches)) {
            return $this->parseObjectLikeArray($matches[2]);
        }

        return 'any';
    }

    private function parseObjectLikeArray(string $propertiesStr): array
    {
        $properties = [];
        $required = [];

        $depth = 0;
        $buffer = '';

        for ($i = 0; $i < \strlen($propertiesStr); ++$i) {
            $char = $propertiesStr[$i];

            if ('{' === $char) {
                ++$depth;
                $buffer .= $char;
            } elseif ('}' === $char) {
                --$depth;
                $buffer .= $char;
            } elseif (',' === $char && 0 === $depth) {
                $this->parsePropertyDefinition(trim($buffer), $properties, $required);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        if (!empty($buffer)) {
            $this->parsePropertyDefinition(trim($buffer), $properties, $required);
        }

        if (!empty($properties)) {
            return [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ];
        }

        return ['type' => 'object'];
    }

    private function parsePropertyDefinition(string $propDefinition, array &$properties, array &$required): void
    {
        if (preg_match('/^([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\s*:\s*(.+)$/i', $propDefinition, $matches)) {
            $propName = $matches[1];
            $propType = trim($matches[2]);

            $required[] = $propName;

            if (preg_match('/^array\s*\{(.+)\}$/is', $propType, $nestedMatches)) {
                $nestedSchema = $this->parseObjectLikeArray($nestedMatches[1]);
                $properties[$propName] = $nestedSchema;
            } elseif (
                preg_match('/^array\s*<\s*([\w\\\\|]+)\s*>$/i', $propType, $arrayMatches)
                || preg_match('/^([\w\\\\]+)\s*\[\]$/i', $propType, $arrayMatches)
            ) {
                $itemType = $arrayMatches[1] ?? 'any';
                $properties[$propName] = [
                    'type' => 'array',
                    'items' => [
                        'type' => $this->mapSimpleTypeToJsonSchema($itemType),
                    ],
                ];
            } else {
                $properties[$propName] = ['type' => $this->mapSimpleTypeToJsonSchema($propType)];
            }
        }
    }

    private function mapSimpleTypeToJsonSchema(string $type): string
    {
        return match (strtolower($type)) {
            'string' => 'string',
            'int', 'integer' => 'integer',
            'bool', 'boolean' => 'boolean',
            'float', 'double', 'number' => 'number',
            'array' => 'array',
            'object', 'stdclass' => 'object',
            default => \in_array(strtolower($type), ['datetime', 'datetimeinterface']) ? 'string' : 'object',
        };
    }
}
