<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use MongoDB\Client;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\MongoDBFormatter;
use PsMcpServerDeps\Monolog\Logger;

class MongoDBHandler extends AbstractProcessingHandler
{
    private $collection;

    private $manager;

    private $namespace;

    public function __construct($mongodb, string $database, string $collection, $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!($mongodb instanceof Client || $mongodb instanceof Manager)) {
            throw new \InvalidArgumentException('MongoDB\Client or MongoDB\Driver\Manager instance required');
        }

        if ($mongodb instanceof Client) {
            $this->collection = method_exists($mongodb, 'getCollection') ? $mongodb->getCollection($database, $collection) : $mongodb->selectCollection($database, $collection);
        } else {
            $this->manager = $mongodb;
            $this->namespace = $database . '.' . $collection;
        }

        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        if (isset($this->collection)) {
            $this->collection->insertOne($record['formatted']);
        }

        if (isset($this->manager, $this->namespace)) {
            $bulk = new BulkWrite();
            $bulk->insert($record['formatted']);
            $this->manager->executeBulkWrite($this->namespace, $bulk);
        }
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new MongoDBFormatter();
    }
}
