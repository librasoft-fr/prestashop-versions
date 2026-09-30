<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use Gelf\PublisherInterface;
use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\GelfMessageFormatter;
use PsMcpServerDeps\Monolog\Logger;

class GelfHandler extends AbstractProcessingHandler
{
    protected $publisher;

    public function __construct(PublisherInterface $publisher, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->publisher = $publisher;
    }

    protected function write(array $record): void
    {
        $this->publisher->publish($record['formatted']);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new GelfMessageFormatter();
    }
}
