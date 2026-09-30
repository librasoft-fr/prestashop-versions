<?php



namespace Mcp\Schema\Elicitation;

use Mcp\Exception\InvalidArgumentException;


abstract class AbstractSchemaDefinition implements \JsonSerializable
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
    ) {
    }

    
    protected static function validateTitle(array $data, string $schemaType): void
    {
        if (!isset($data['title']) || !\is_string($data['title'])) {
            throw new InvalidArgumentException(\sprintf('Missing or invalid "title" for %s schema definition.', $schemaType));
        }
    }

    
    protected function buildBaseJson(string $type): array
    {
        $data = [
            'type' => $type,
            'title' => $this->title,
        ];

        if (null !== $this->description) {
            $data['description'] = $this->description;
        }

        return $data;
    }

    
    abstract public function jsonSerialize(): array;
}
