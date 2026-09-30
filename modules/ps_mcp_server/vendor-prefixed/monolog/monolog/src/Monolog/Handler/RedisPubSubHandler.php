<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\LineFormatter;
use PsMcpServerDeps\Monolog\Logger;

class RedisPubSubHandler extends AbstractProcessingHandler
{
    private $redisClient;

    private $channelKey;

    public function __construct($redis, string $key, $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!(($redis instanceof \Predis\Client) || ($redis instanceof \Redis))) {
            throw new \InvalidArgumentException('Predis\Client or Redis instance required');
        }

        $this->redisClient = $redis;
        $this->channelKey = $key;

        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $this->redisClient->publish($this->channelKey, $record['formatted']);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
