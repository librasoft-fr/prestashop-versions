<?php



namespace Mcp\Schema;


class ModelPreferences implements \JsonSerializable
{
    
    public function __construct(
        public readonly ?array $hints = null,
        public readonly ?float $costPriority = null,
        public readonly ?float $speedPriority = null,
        public readonly ?float $intelligencePriority = null,
    ) {
    }

    
    public static function fromArray(array $preferences): self
    {
        return new self(
            $preferences['hints'] ?? null,
            $preferences['costPriority'] ?? null,
            $preferences['speedPriority'] ?? null,
            $preferences['intelligencePriority'] ?? null,
        );
    }

    
    public function jsonSerialize(): array
    {
        $result = [];
        if (null !== $this->hints) {
            $result['hints'] = $this->hints;
        }
        if (null !== $this->costPriority) {
            $result['costPriority'] = $this->costPriority;
        }
        if (null !== $this->speedPriority) {
            $result['speedPriority'] = $this->speedPriority;
        }
        if (null !== $this->intelligencePriority) {
            $result['intelligencePriority'] = $this->intelligencePriority;
        }

        return $result;
    }
}
