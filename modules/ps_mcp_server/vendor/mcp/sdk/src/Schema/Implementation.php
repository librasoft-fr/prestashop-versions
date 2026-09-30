<?php



namespace Mcp\Schema;

use Mcp\Exception\InvalidArgumentException;


class Implementation implements \JsonSerializable
{
    
    public function __construct(
        public readonly string $name = 'app',
        public readonly string $version = 'dev',
        public readonly ?string $description = null,
        public readonly ?array $icons = null,
        public readonly ?string $websiteUrl = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (empty($data['name']) || !\is_string($data['name'])) {
            throw new InvalidArgumentException('Invalid or missing "name" in Implementation data.');
        }
        if (empty($data['version']) || !\is_string($data['version'])) {
            throw new InvalidArgumentException('Invalid or missing "version" in Implementation data.');
        }

        if (isset($data['icons'])) {
            if (!\is_array($data['icons'])) {
                throw new InvalidArgumentException('Invalid "icons" in Implementation data; expected an array.');
            }

            $data['icons'] = array_map(Icon::fromArray(...), $data['icons']);
        }

        return new self(
            $data['name'],
            $data['version'],
            $data['description'] ?? null,
            $data['icons'] ?? null,
            $data['websiteUrl'] ?? null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = [
            'name' => $this->name,
            'version' => $this->version,
        ];

        if (null !== $this->description) {
            $data['description'] = $this->description;
        }

        if (null !== $this->icons) {
            $data['icons'] = $this->icons;
        }

        if (null !== $this->websiteUrl) {
            $data['websiteUrl'] = $this->websiteUrl;
        }

        return $data;
    }
}
