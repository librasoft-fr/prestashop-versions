<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Monolog\Formatter\FormatterInterface;


class PsrHandler extends AbstractHandler implements FormattableHandlerInterface
{
    
    protected $logger;

    
    protected $formatter;

    
    public function __construct(LoggerInterface $logger, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->logger = $logger;
    }

    
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if ($this->formatter) {
            $formatted = $this->formatter->format($record);
            $this->logger->log(strtolower($record['level_name']), (string) $formatted, $record['context']);
        } else {
            $this->logger->log(strtolower($record['level_name']), $record['message'], $record['context']);
        }

        return false === $this->bubble;
    }

    
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    
    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            throw new \LogicException('No formatter has been set and this handler does not have a default formatter');
        }

        return $this->formatter;
    }
}
