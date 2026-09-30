<?php



namespace Mcp\Schema\Notification;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Notification;


class ProgressNotification extends Notification
{
    
    public function __construct(
        public readonly string|int $progressToken,
        public readonly float $progress,
        public readonly ?float $total = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function getMethod(): string
    {
        return 'notifications/progress';
    }

    protected static function fromParams(?array $params): Notification
    {
        if (!isset($params['progressToken']) || !\is_string($params['progressToken'])) {
            throw new InvalidArgumentException('Missing or invalid "progressToken" parameter for "notifications/progress" notification.');
        }

        if (!isset($params['progress']) || !\is_float($params['progress'])) {
            throw new InvalidArgumentException('Missing or invalid "progress" parameter for "notifications/progress" notification.');
        }

        return new self(
            $params['progressToken'],
            $params['progress'],
            $params['total'] ?? null,
            $params['message'] ?? null,
        );
    }

    protected function getParams(): ?array
    {
        $params = [
            'progressToken' => $this->progressToken,
            'progress' => $this->progress,
        ];

        if (null !== $this->total) {
            $params['total'] = $this->total;
        }

        if (null !== $this->message) {
            $params['message'] = $this->message;
        }

        return $params;
    }
}
