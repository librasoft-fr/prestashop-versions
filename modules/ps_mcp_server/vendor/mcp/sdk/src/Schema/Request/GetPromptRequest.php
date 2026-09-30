<?php



namespace Mcp\Schema\Request;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Request;


final class GetPromptRequest extends Request
{
    
    public function __construct(
        public readonly string $name,
        public readonly ?array $arguments = null,
    ) {
    }

    public static function getMethod(): string
    {
        return 'prompts/get';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['name']) || !\is_string($params['name']) || empty($params['name'])) {
            throw new InvalidArgumentException('Missing or invalid "name" parameter for prompts/get.');
        }

        $arguments = $params['arguments'] ?? null;
        if (null !== $arguments) {
            if ($arguments instanceof \stdClass) {
                $arguments = (array) $arguments;
            }
            if (!\is_array($arguments)) {
                throw new InvalidArgumentException('Parameter "arguments" must be an array for prompts/get.');
            }
        }

        return new self($params['name'], $arguments);
    }

    
    protected function getParams(): array
    {
        $params = ['name' => $this->name];

        if (null !== $this->arguments) {
            $params['arguments'] = $this->arguments;
        }

        return $params;
    }
}
