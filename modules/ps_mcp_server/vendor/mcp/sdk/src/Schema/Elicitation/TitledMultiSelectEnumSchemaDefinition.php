<?php



namespace Mcp\Schema\Elicitation;

use Mcp\Exception\InvalidArgumentException;


final class TitledMultiSelectEnumSchemaDefinition extends AbstractSchemaDefinition
{
    
    public function __construct(
        string $title,
        public readonly array $anyOf,
        ?string $description = null,
        public readonly ?array $default = null,
        public readonly ?int $minItems = null,
        public readonly ?int $maxItems = null,
    ) {
        parent::__construct($title, $description);

        if ([] === $anyOf) {
            throw new InvalidArgumentException('anyOf array must not be empty.');
        }

        $consts = [];
        foreach ($anyOf as $item) {
            if (!isset($item['const']) || !\is_string($item['const'])) {
                throw new InvalidArgumentException('Each anyOf item must have a string "const" property.');
            }
            if (!isset($item['title']) || !\is_string($item['title'])) {
                throw new InvalidArgumentException('Each anyOf item must have a string "title" property.');
            }
            $consts[] = $item['const'];
        }

        if (null !== $minItems && $minItems < 0) {
            throw new InvalidArgumentException('minItems must be non-negative.');
        }

        if (null !== $maxItems && $maxItems < 0) {
            throw new InvalidArgumentException('maxItems must be non-negative.');
        }

        if (null !== $minItems && null !== $maxItems && $minItems > $maxItems) {
            throw new InvalidArgumentException('minItems cannot be greater than maxItems.');
        }

        if (null !== $default) {
            foreach ($default as $value) {
                if (!\in_array($value, $consts, true)) {
                    throw new InvalidArgumentException(\sprintf('Default value "%s" is not in the anyOf const values.', $value));
                }
            }
        }
    }

    
    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'titled multi-select enum');

        if (!isset($data['items']['anyOf']) || !\is_array($data['items']['anyOf'])) {
            throw new InvalidArgumentException('Missing or invalid "items.anyOf" for titled multi-select enum schema definition.');
        }

        return new self(
            title: $data['title'],
            anyOf: $data['items']['anyOf'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            minItems: isset($data['minItems']) ? (int) $data['minItems'] : null,
            maxItems: isset($data['maxItems']) ? (int) $data['maxItems'] : null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = $this->buildBaseJson('array');
        $data['items'] = [
            'anyOf' => $this->anyOf,
        ];

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        if (null !== $this->minItems) {
            $data['minItems'] = $this->minItems;
        }

        if (null !== $this->maxItems) {
            $data['maxItems'] = $this->maxItems;
        }

        return $data;
    }
}
