<?php



namespace Mcp\Schema\Elicitation;

use Mcp\Exception\InvalidArgumentException;


final class NumberSchemaDefinition extends AbstractSchemaDefinition
{
    
    public function __construct(
        string $title,
        public readonly bool $integerOnly = false,
        ?string $description = null,
        public readonly int|float|null $default = null,
        public readonly int|float|null $minimum = null,
        public readonly int|float|null $maximum = null,
    ) {
        parent::__construct($title, $description);

        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new InvalidArgumentException('minimum cannot be greater than maximum.');
        }

        if (null !== $default && null !== $minimum && $default < $minimum) {
            throw new InvalidArgumentException('default value cannot be less than minimum.');
        }

        if (null !== $default && null !== $maximum && $default > $maximum) {
            throw new InvalidArgumentException('default value cannot be greater than maximum.');
        }

        if ($integerOnly && null !== $default && $default !== (int) $default) {
            throw new InvalidArgumentException('default value must be an integer when integerOnly is true.');
        }
    }

    
    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'number');

        $type = $data['type'] ?? 'number';
        $integerOnly = 'integer' === $type;

        return new self(
            title: $data['title'],
            integerOnly: $integerOnly,
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            minimum: $data['minimum'] ?? null,
            maximum: $data['maximum'] ?? null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = $this->buildBaseJson($this->integerOnly ? 'integer' : 'number');

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        if (null !== $this->minimum) {
            $data['minimum'] = $this->minimum;
        }

        if (null !== $this->maximum) {
            $data['maximum'] = $this->maximum;
        }

        return $data;
    }
}
