<?php declare(strict_types=1);



namespace Monolog\Handler;

use Gelf\PublisherInterface;
use Monolog\Logger;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Formatter\FormatterInterface;


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
