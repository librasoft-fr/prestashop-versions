<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\ElicitAction;
use Mcp\Schema\JsonRpc\ResultInterface;


final class ElicitResult implements ResultInterface
{
    
    public function __construct(
        public readonly ElicitAction $action,
        public readonly ?array $content = null,
    ) {
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['action']) || !\is_string($data['action'])) {
            throw new InvalidArgumentException('Missing or invalid "action" in ElicitResult data.');
        }

        $action = ElicitAction::from($data['action']);
        $content = isset($data['content']) && \is_array($data['content']) ? $data['content'] : null;

        if (ElicitAction::Accept === $action && null === $content) {
            throw new InvalidArgumentException('Content must be provided when action is "accept".');
        }

        return new self($action, $content);
    }

    public function isAccepted(): bool
    {
        return ElicitAction::Accept === $this->action;
    }

    public function isDeclined(): bool
    {
        return ElicitAction::Decline === $this->action;
    }

    public function isCancelled(): bool
    {
        return ElicitAction::Cancel === $this->action;
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'action' => $this->action->value,
        ];

        if (null !== $this->content) {
            $result['content'] = $this->content;
        }

        return $result;
    }
}
