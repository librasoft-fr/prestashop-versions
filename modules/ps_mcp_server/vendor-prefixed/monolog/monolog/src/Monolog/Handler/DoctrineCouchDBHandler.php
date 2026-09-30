<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use Doctrine\CouchDB\CouchDBClient;
use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\NormalizerFormatter;
use PsMcpServerDeps\Monolog\Logger;

class DoctrineCouchDBHandler extends AbstractProcessingHandler
{
    private $client;

    public function __construct(CouchDBClient $client, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $this->client->postDocument($record['formatted']);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }
}
