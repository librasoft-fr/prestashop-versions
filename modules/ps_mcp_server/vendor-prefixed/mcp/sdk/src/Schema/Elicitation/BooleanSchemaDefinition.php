<?php

namespace PsMcpServerDeps\Mcp\Schema\Elicitation;

final class BooleanSchemaDefinition extends AbstractSchemaDefinition
{
    public function __construct(
        string $title,
        ?string $description = null,
        public readonly ?bool $default = null,
    ) {
        parent::__construct($title, $description);
    }

    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'boolean');

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            default: isset($data['default']) ? (bool) $data['default'] : null,
        );
    }

    public function jsonSerialize(): array
    {
        $data = $this->buildBaseJson('boolean');

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        return $data;
    }
}
