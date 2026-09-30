<?php

namespace PsMcpServerDeps\Mcp\Schema\Elicitation;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;

final class EnumSchemaDefinition extends AbstractSchemaDefinition
{
    public function __construct(
        string $title,
        public readonly array $enum,
        ?string $description = null,
        public readonly ?string $default = null,
        public readonly ?array $enumNames = null,
    ) {
        parent::__construct($title, $description);

        if ([] === $enum) {
            throw new InvalidArgumentException('enum array must not be empty.');
        }

        foreach ($enum as $value) {
            if (!\is_string($value)) {
                throw new InvalidArgumentException('All enum values must be strings.');
            }
        }

        if (null !== $enumNames && \count($enumNames) !== \count($enum)) {
            throw new InvalidArgumentException('enumNames length must match enum length.');
        }

        if (null !== $default && !\in_array($default, $enum, true)) {
            throw new InvalidArgumentException(\sprintf('Default value "%s" is not in the enum array.', $default));
        }
    }

    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'enum');

        if (!isset($data['enum']) || !\is_array($data['enum'])) {
            throw new InvalidArgumentException('Missing or invalid "enum" for enum schema definition.');
        }

        return new self(
            title: $data['title'],
            enum: $data['enum'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            enumNames: $data['enumNames'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        $data = [
            'type' => 'string',
            'title' => $this->title,
            'enum' => $this->enum,
        ];

        if (null !== $this->description) {
            $data['description'] = $this->description;
        }

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        if (null !== $this->enumNames) {
            $data['enumNames'] = $this->enumNames;
        }

        return $data;
    }
}
