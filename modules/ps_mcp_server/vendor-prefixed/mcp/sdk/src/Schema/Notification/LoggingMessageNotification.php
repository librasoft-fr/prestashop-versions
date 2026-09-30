<?php

namespace PsMcpServerDeps\Mcp\Schema\Notification;

use PsMcpServerDeps\Mcp\Exception\InvalidArgumentException;
use PsMcpServerDeps\Mcp\Schema\Enum\LoggingLevel;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Notification;

class LoggingMessageNotification extends Notification
{
    public function __construct(
        public readonly LoggingLevel $level,
        public readonly mixed $data,
        public readonly ?string $logger = null,
    ) {
    }

    public static function getMethod(): string
    {
        return 'notifications/message';
    }

    protected static function fromParams(?array $params): Notification
    {
        if (!isset($params['level']) || !\is_string($params['level'])) {
            throw new InvalidArgumentException('Missing or invalid "level" parameter for "notifications/message" notification.');
        }

        if (!isset($params['data'])) {
            throw new InvalidArgumentException('Missing "data" parameter for "notifications/message" notification.');
        }

        $level = LoggingLevel::from($params['level']);
        $data = \is_string($params['data']) ? $params['data'] : json_encode($params['data']);

        return new self($level, $data, $params['logger'] ?? null);
    }

    protected function getParams(): ?array
    {
        $params = [
            'level' => $this->level->value,
            'data' => $this->data,
        ];

        if (null !== $this->logger) {
            $params['logger'] = $this->logger;
        }

        return $params;
    }
}
