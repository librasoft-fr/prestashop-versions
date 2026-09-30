<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;


class RedisHandler extends AbstractProcessingHandler
{
    
    private $redisClient;
    
    private $redisKey;
    
    protected $capSize;

    
    public function __construct($redis, string $key, $level = Logger::DEBUG, bool $bubble = true, int $capSize = 0)
    {
        if (!(($redis instanceof \Predis\Client) || ($redis instanceof \Redis))) {
            throw new \InvalidArgumentException('Predis\Client or Redis instance required');
        }

        $this->redisClient = $redis;
        $this->redisKey = $key;
        $this->capSize = $capSize;

        parent::__construct($level, $bubble);
    }

    
    protected function write(array $record): void
    {
        if ($this->capSize) {
            $this->writeCapped($record);
        } else {
            $this->redisClient->rpush($this->redisKey, $record["formatted"]);
        }
    }

    
    protected function writeCapped(array $record): void
    {
        if ($this->redisClient instanceof \Redis) {
            $mode = defined('\Redis::MULTI') ? \Redis::MULTI : 1;
            $this->redisClient->multi($mode)
                ->rpush($this->redisKey, $record["formatted"])
                ->ltrim($this->redisKey, -$this->capSize, -1)
                ->exec();
        } else {
            $redisKey = $this->redisKey;
            $capSize = $this->capSize;
            $this->redisClient->transaction(function ($tx) use ($record, $redisKey, $capSize) {
                $tx->rpush($redisKey, $record["formatted"]);
                $tx->ltrim($redisKey, -$capSize, -1);
            });
        }
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
