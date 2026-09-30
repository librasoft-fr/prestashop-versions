<?php



namespace Mcp\Schema\Result;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Content\PromptMessage;
use Mcp\Schema\JsonRpc\ResultInterface;


class GetPromptResult implements ResultInterface
{
    
    public function __construct(
        public readonly array $messages,
        public readonly ?string $description = null,
    ) {
        foreach ($this->messages as $message) {
            if (!$message instanceof PromptMessage) {
                throw new InvalidArgumentException('Messages must be an array of PromptMessage objects.');
            }
        }
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['messages']) || !\is_array($data['messages'])) {
            throw new InvalidArgumentException('Missing or invalid "messages" array in GetPromptResult data.');
        }

        $messages = [];
        foreach ($data['messages'] as $message) {
            $messages[] = PromptMessage::fromArray($message);
        }

        return new self($messages, $data['description'] ?? null);
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'messages' => $this->messages,
        ];

        if (null !== $this->description) {
            $result['description'] = $this->description;
        }

        return $result;
    }
}
