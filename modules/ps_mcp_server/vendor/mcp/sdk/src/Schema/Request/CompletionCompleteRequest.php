<?php



namespace Mcp\Schema\Request;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\PromptReference;
use Mcp\Schema\ResourceReference;


final class CompletionCompleteRequest extends Request
{
    
    public function __construct(
        public readonly PromptReference|ResourceReference $ref,
        public readonly array $argument,
    ) {
    }

    public static function getMethod(): string
    {
        return 'completion/complete';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['ref']) || !\is_array($params['ref'])) {
            throw new InvalidArgumentException('Missing or invalid "ref" parameter for completion/complete.');
        }

        $ref = match ($params['ref']['type'] ?? null) {
            'ref/prompt' => new PromptReference($params['ref']['name']),
            'ref/resource' => new ResourceReference($params['ref']['uri']),
            default => throw new InvalidArgumentException('Invalid "ref" parameter for completion/complete.'),
        };

        if (!isset($params['argument']) || !\is_array($params['argument'])) {
            throw new InvalidArgumentException('Missing or invalid "argument" parameter for completion/complete.');
        }

        return new self($ref, $params['argument']);
    }

    
    protected function getParams(): array
    {
        return [
            'ref' => $this->ref,
            'argument' => $this->argument,
        ];
    }
}
