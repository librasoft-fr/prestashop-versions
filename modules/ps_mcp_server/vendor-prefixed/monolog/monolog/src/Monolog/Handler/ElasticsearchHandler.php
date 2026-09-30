<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use Elastic\Elasticsearch\Client as Client8;
use Elastic\Elasticsearch\Exception\InvalidArgumentException as ElasticInvalidArgumentException;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\RuntimeException as ElasticsearchRuntimeException;
use PsMcpServerDeps\Monolog\Formatter\ElasticsearchFormatter;
use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Logger;

class ElasticsearchHandler extends AbstractProcessingHandler
{
    protected $client;

    protected $options = [];

    private $needsType;

    public function __construct($client, array $options = [], $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!$client instanceof Client && !$client instanceof Client8) {
            throw new \TypeError('Elasticsearch\Client or Elastic\Elasticsearch\Client instance required');
        }

        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->options = array_merge(
            [
                'index' => 'monolog',
                'type' => '_doc',
                'ignore_error' => false,
            ],
            $options
        );

        if ($client instanceof Client8 || $client::VERSION[0] === '7') {
            $this->needsType = false;

            $this->options['type'] = '_doc';
        } else {
            $this->needsType = true;
        }
    }

    protected function write(array $record): void
    {
        $this->bulkSend([$record['formatted']]);
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($formatter instanceof ElasticsearchFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new \InvalidArgumentException('ElasticsearchHandler is only compatible with ElasticsearchFormatter');
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new ElasticsearchFormatter($this->options['index'], $this->options['type']);
    }

    public function handleBatch(array $records): void
    {
        $documents = $this->getFormatter()->formatBatch($records);
        $this->bulkSend($documents);
    }

    protected function bulkSend(array $records): void
    {
        try {
            $params = [
                'body' => [],
            ];

            foreach ($records as $record) {
                $params['body'][] = [
                    'index' => $this->needsType ? [
                        '_index' => $record['_index'],
                        '_type' => $record['_type'],
                    ] : [
                        '_index' => $record['_index'],
                    ],
                ];
                unset($record['_index'], $record['_type']);

                $params['body'][] = $record;
            }

            $responses = $this->client->bulk($params);

            if ($responses['errors'] === true) {
                throw $this->createExceptionFromResponses($responses);
            }
        } catch (\Throwable $e) {
            if (!$this->options['ignore_error']) {
                throw new \RuntimeException('Error sending messages to Elasticsearch', 0, $e);
            }
        }
    }

    protected function createExceptionFromResponses($responses): \Throwable
    {
        foreach ($responses['items'] ?? [] as $item) {
            if (isset($item['index']['error'])) {
                return $this->createExceptionFromError($item['index']['error']);
            }
        }

        if (class_exists(ElasticInvalidArgumentException::class)) {
            return new ElasticInvalidArgumentException('Elasticsearch failed to index one or more records.');
        }

        return new ElasticsearchRuntimeException('Elasticsearch failed to index one or more records.');
    }

    protected function createExceptionFromError(array $error): \Throwable
    {
        $previous = isset($error['caused_by']) ? $this->createExceptionFromError($error['caused_by']) : null;

        if (class_exists(ElasticInvalidArgumentException::class)) {
            return new ElasticInvalidArgumentException($error['type'] . ': ' . $error['reason'], 0, $previous);
        }

        return new ElasticsearchRuntimeException($error['type'] . ': ' . $error['reason'], 0, $previous);
    }
}
