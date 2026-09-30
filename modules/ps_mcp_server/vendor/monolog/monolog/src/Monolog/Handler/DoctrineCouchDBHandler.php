<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\FormatterInterface;
use Doctrine\CouchDB\CouchDBClient;


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
        return new NormalizerFormatter;
    }
}
