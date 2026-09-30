<?php



namespace Mcp\Schema;

use Mcp\Exception\InvalidArgumentException;


class Root implements \JsonSerializable
{
    private const URI_PATTERN = '/^file:\/\/.*$/';

    
    public function __construct(
        public readonly string $uri,
        public readonly ?string $name = null,
    ) {
        if (!preg_match(self::URI_PATTERN, $this->uri)) {
            throw new InvalidArgumentException(\sprintf('Root URI must start with "file://". Given: "%s".', $this->uri));
        }
    }

    
    public static function fromArray(array $data): self
    {
        if (empty($data['uri']) || !\is_string($data['uri'])) {
            throw new InvalidArgumentException('Invalid or missing "uri" in Root data.');
        }

        return new self($data['uri'], $data['name'] ?? null);
    }

    
    public function jsonSerialize(): array
    {
        $data = ['uri' => $this->uri];
        if (null !== $this->name) {
            $data['name'] = $this->name;
        }

        return $data;
    }
}
