<?php



namespace Mcp\Schema\Elicitation;

use Mcp\Exception\InvalidArgumentException;


final class StringSchemaDefinition extends AbstractSchemaDefinition
{
    private const VALID_FORMATS = ['date', 'date-time', 'email', 'uri'];

    
    public function __construct(
        string $title,
        ?string $description = null,
        public readonly ?string $default = null,
        public readonly ?string $format = null,
        public readonly ?int $minLength = null,
        public readonly ?int $maxLength = null,
    ) {
        parent::__construct($title, $description);

        if (null !== $format && !\in_array($format, self::VALID_FORMATS, true)) {
            throw new InvalidArgumentException(\sprintf('Invalid format "%s". Valid formats are: %s.', $format, implode(', ', self::VALID_FORMATS)));
        }

        if (null !== $minLength && $minLength < 0) {
            throw new InvalidArgumentException('minLength must be non-negative.');
        }

        if (null !== $maxLength && $maxLength < 0) {
            throw new InvalidArgumentException('maxLength must be non-negative.');
        }

        if (null !== $minLength && null !== $maxLength && $minLength > $maxLength) {
            throw new InvalidArgumentException('minLength cannot be greater than maxLength.');
        }
    }

    
    public static function fromArray(array $data): self
    {
        self::validateTitle($data, 'string');

        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            format: $data['format'] ?? null,
            minLength: isset($data['minLength']) ? (int) $data['minLength'] : null,
            maxLength: isset($data['maxLength']) ? (int) $data['maxLength'] : null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = $this->buildBaseJson('string');

        if (null !== $this->default) {
            $data['default'] = $this->default;
        }

        if (null !== $this->format) {
            $data['format'] = $this->format;
        }

        if (null !== $this->minLength) {
            $data['minLength'] = $this->minLength;
        }

        if (null !== $this->maxLength) {
            $data['maxLength'] = $this->maxLength;
        }

        return $data;
    }
}
