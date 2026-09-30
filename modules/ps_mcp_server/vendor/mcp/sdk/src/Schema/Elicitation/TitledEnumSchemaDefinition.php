<?php



namespace Mcp\Schema\Elicitation;

use Mcp\Exception\InvalidArgumentException;


final class TitledEnumSchemaDefinition extends AbstractSchemaDefinition
{
    
    public function __construct(
        string $title,
        public readonly array $oneOf,
        ?string $description = null,
        public readonly ?string $default = null,
    ) {
        parent::__construct($title, $description);

        if ([] === $oneOf) {
            throw new InvalidArgumentException('oneOf array must not be empty.');
        }

        $consts = [];
        foreach ($oneOf as $item) {
            if (!isset($item['const']) || !\is_string($item['const'])) {
                throw new InvalidArgumentException('Each oneOf item must have a string "const" property.');
            }
            if (!isset($item['title']) || !\is_string($item['title'])) {
                throw new InvalidArgumentException('Each oneOf item must have a string "title" property.');
            }
            $consts[] = $item['const'];
        }

        if (null !== $default && !\in_array($default, $consts, true)) {
            throw new InvalidArgumentException(\sprintf('Default value "%s" is not in the oneOf const values.', $default));
        }
    }

    
    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'titled enum');

        if (!isset($data['oneOf']) || !\is_array($data['oneOf'])) {
            throw new InvalidArgumentException('Missing or invalid "oneOf" for titled enum schema definition.');
        }

        return new self(
            title: $data['title'],
            oneOf: $data['oneOf'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = $this->buildBaseJson('string');
        $data['oneOf'] = $this->oneOf;

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        return $data;
    }
}
