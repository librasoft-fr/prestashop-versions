<?php



namespace Mcp\Schema\Notification;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\JsonRpc\Notification;


class CancelledNotification extends Notification
{
    
    public function __construct(
        public readonly string|int $requestId,
        public readonly ?string $reason = null,
    ) {
    }

    public static function getMethod(): string
    {
        return 'notifications/cancelled';
    }

    protected static function fromParams(?array $params): Notification
    {
        if (null === $params || !isset($params['requestId']) || (!\is_string($params['requestId']) && !\is_int($params['requestId']))) {
            throw new InvalidArgumentException('Invalid or missing "requestId" parameter for "notifications/cancelled" notification.');
        }

        return new self($params['requestId'], $params['reason'] ?? null);
    }

    protected function getParams(): ?array
    {
        $params = ['requestId' => $this->requestId];

        if (null !== $this->reason) {
            $params['reason'] = $this->reason;
        }

        return $params;
    }
}
