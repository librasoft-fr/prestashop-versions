<?php



namespace Mcp\Schema\Request;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Content\SamplingMessage;
use Mcp\Schema\Enum\SamplingContext;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\ModelPreferences;


final class CreateSamplingMessageRequest extends Request
{
    
    public function __construct(
        public readonly array $messages,
        public readonly int $maxTokens,
        public readonly ?ModelPreferences $preferences = null,
        public readonly ?string $systemPrompt = null,
        public readonly ?SamplingContext $includeContext = null,
        public readonly ?float $temperature = null,
        public readonly ?array $stopSequences = null,
        public readonly ?array $metadata = null,
    ) {
        foreach ($this->messages as $message) {
            if (!$message instanceof SamplingMessage) {
                throw new InvalidArgumentException('Messages must be instance of SamplingMessage.');
            }
        }
    }

    public static function getMethod(): string
    {
        return 'sampling/createMessage';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['messages']) || !\is_array($params['messages'])) {
            throw new InvalidArgumentException('Missing or invalid "messages" parameter for sampling/createMessage.');
        }

        if (!isset($params['maxTokens']) || !\is_int($params['maxTokens'])) {
            throw new InvalidArgumentException('Missing or invalid "maxTokens" parameter for sampling/createMessage.');
        }

        $messages = [];
        foreach ($params['messages'] as $messageData) {
            if ($messageData instanceof SamplingMessage) {
                $messages[] = $messageData;
            } elseif (\is_array($messageData)) {
                $messages[] = SamplingMessage::fromArray($messageData);
            } else {
                throw new InvalidArgumentException('Invalid message format in sampling/createMessage.');
            }
        }

        $preferences = null;
        if (isset($params['preferences'])) {
            $preferences = ModelPreferences::fromArray($params['preferences']);
        }

        $includeContext = null;
        if (isset($params['includeContext']) && \is_string($params['includeContext'])) {
            $includeContext = SamplingContext::tryFrom($params['includeContext']);
        }

        return new self(
            $messages,
            $params['maxTokens'],
            $preferences,
            $params['systemPrompt'] ?? null,
            $includeContext,
            $params['temperature'] ?? null,
            $params['stopSequences'] ?? null,
            $params['metadata'] ?? null,
        );
    }

    
    protected function getParams(): array
    {
        $params = [
            'messages' => $this->messages,
            'maxTokens' => $this->maxTokens,
        ];

        if (null !== $this->preferences) {
            $params['preferences'] = $this->preferences;
        }

        if (null !== $this->systemPrompt) {
            $params['systemPrompt'] = $this->systemPrompt;
        }

        if (null !== $this->includeContext) {
            $params['includeContext'] = $this->includeContext->value;
        }

        if (null !== $this->temperature) {
            $params['temperature'] = $this->temperature;
        }

        if (null !== $this->stopSequences) {
            $params['stopSequences'] = $this->stopSequences;
        }

        if (null !== $this->metadata) {
            $params['metadata'] = $this->metadata;
        }

        return $params;
    }
}
