<?php



namespace Mcp\Schema;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\Role;


class Annotations implements \JsonSerializable
{
    
    public function __construct(
        public readonly ?array $audience = null,
        public readonly ?float $priority = null,
    ) {
        if (null !== $this->priority && ($this->priority < 0 || $this->priority > 1)) {
            throw new InvalidArgumentException('Annotation priority must be between 0 and 1.');
        }
        if (null !== $this->audience) {
            foreach ($this->audience as $role) {
                if (!$role instanceof Role) {
                    throw new InvalidArgumentException('All audience members must be instances of Role enum.');
                }
            }
        }
    }

    
    public static function fromArray(array $data): self
    {
        $audience = null;
        if (isset($data['audience']) && \is_array($data['audience'])) {
            $audience = array_map(static fn (string $r) => Role::from($r), $data['audience']);
        }

        return new self(
            $audience,
            isset($data['priority']) ? (float) $data['priority'] : null
        );
    }

    
    public function jsonSerialize(): array
    {
        $data = [];
        if (null !== $this->audience) {
            $data['audience'] = array_map(static fn (Role $r) => $r->value, $this->audience);
        }
        if (null !== $this->priority) {
            $data['priority'] = $this->priority;
        }

        return $data;
    }
}
