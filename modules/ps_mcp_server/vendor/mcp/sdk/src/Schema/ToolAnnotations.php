<?php



namespace Mcp\Schema;


class ToolAnnotations implements \JsonSerializable
{
    
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?bool $readOnlyHint = null,
        public readonly ?bool $destructiveHint = null,
        public readonly ?bool $idempotentHint = null,
        public readonly ?bool $openWorldHint = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? null,
            $data['readOnlyHint'] ?? null,
            $data['destructiveHint'] ?? null,
            $data['idempotentHint'] ?? null,
            $data['openWorldHint'] ?? null
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = [];
        if (null !== $this->title) {
            $data['title'] = $this->title;
        }
        if (null !== $this->readOnlyHint) {
            $data['readOnlyHint'] = $this->readOnlyHint;
        }
        if (null !== $this->destructiveHint) {
            $data['destructiveHint'] = $this->destructiveHint;
        }
        if (null !== $this->idempotentHint) {
            $data['idempotentHint'] = $this->idempotentHint;
        }
        if (null !== $this->openWorldHint) {
            $data['openWorldHint'] = $this->openWorldHint;
        }

        return $data;
    }
}
