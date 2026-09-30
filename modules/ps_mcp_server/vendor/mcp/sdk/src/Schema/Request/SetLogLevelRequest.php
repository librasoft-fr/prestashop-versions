<?php



namespace Mcp\Schema\Request;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Enum\LoggingLevel;
use Mcp\Schema\JsonRpc\Request;


final class SetLogLevelRequest extends Request
{
    
    public function __construct(
        public readonly LoggingLevel $level,
    ) {
    }

    public static function getMethod(): string
    {
        return 'logging/setLevel';
    }

    protected static function fromParams(?array $params): static
    {
        if (!isset($params['level']) || !\is_string($params['level']) || '' === $params['level']) {
            throw new InvalidArgumentException('Missing or invalid "level" parameter for "logging/setLevel".');
        }

        return new self(LoggingLevel::from($params['level']));
    }

    
    protected function getParams(): array
    {
        return [
            'level' => $this->level->value,
        ];
    }
}
