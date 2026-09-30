<?php

namespace PsMcpServerDeps\Mcp\Schema\Elicitation;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

final class ElicitationSchema implements \JsonSerializable
{
    public function __construct(
        public readonly array $properties,
        public readonly array $required = [],
    ) {
        if ([] === $properties) {
            throw new InvalidArgumentException('properties array must not be empty.');
        }

        foreach ($required as $name) {
            if (!\array_key_exists($name, $properties)) {
                throw new InvalidArgumentException(\sprintf('Required property "%s" is not defined in properties.', $name));
            }
        }
    }

    public static function fromArray(array $data): self
    {
        if (isset($data['type']) && 'object' !== $data['type']) {
            throw new InvalidArgumentException('ElicitationSchema type must be "object".');
        }

        if (!isset($data['properties']) || !\is_array($data['properties'])) {
            throw new InvalidArgumentException('Missing or invalid "properties" for elicitation schema.');
        }

        $properties = [];
        foreach ($data['properties'] as $name => $propertyData) {
            if (!\is_array($propertyData)) {
                throw new InvalidArgumentException(\sprintf('Property "%s" must be an array.', $name));
            }
            $properties[$name] = self::createSchemaDefinition($propertyData);
        }

        return new self(
            properties: $properties,
            required: $data['required'] ?? [],
        );
    }

    private static function createSchemaDefinition(array $data): AbstractSchemaDefinition
    {
        if (!isset($data['type']) || !\is_string($data['type'])) {
            throw new InvalidArgumentException('Missing or invalid "type" for schema definition.');
        }

        return match ($data['type']) {
            'string' => self::resolveStringType($data),
            'integer', 'number' => NumberSchemaDefinition::fromArray($data),
            'boolean' => BooleanSchemaDefinition::fromArray($data),
            'array' => self::resolveArrayType($data),
            default => throw new InvalidArgumentException(\sprintf('Unsupported type "%s". Supported types are: string, integer, number, boolean, array.', $data['type'])),
        };
    }

    private static function resolveStringType(array $data): AbstractSchemaDefinition
    {
        if (isset($data['oneOf'])) {
            return TitledEnumSchemaDefinition::fromArray($data);
        }

        if (isset($data['enum'])) {
            return EnumSchemaDefinition::fromArray($data);
        }

        return StringSchemaDefinition::fromArray($data);
    }

    private static function resolveArrayType(array $data): AbstractSchemaDefinition
    {
        if (isset($data['items']['anyOf'])) {
            return TitledMultiSelectEnumSchemaDefinition::fromArray($data);
        }

        if (isset($data['items']['enum'])) {
            return MultiSelectEnumSchemaDefinition::fromArray($data);
        }

        throw new InvalidArgumentException('Array type must have "items" with either "enum" or "anyOf".');
    }

    public function jsonSerialize(): array
    {
        $data = [
            'type' => 'object',
            'properties' => [],
        ];

        foreach ($this->properties as $name => $property) {
            $data['properties'][$name] = $property->jsonSerialize();
        }

        if ([] !== $this->required) {
            $data['required'] = $this->required;
        }

        return $data;
    }
}
