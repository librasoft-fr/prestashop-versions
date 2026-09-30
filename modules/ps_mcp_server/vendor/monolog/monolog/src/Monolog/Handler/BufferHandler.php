<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\ResettableInterface;
use Monolog\Formatter\FormatterInterface;


class BufferHandler extends AbstractHandler implements ProcessableHandlerInterface, FormattableHandlerInterface
{
    use ProcessableHandlerTrait;

    
    protected $handler;
    
    protected $bufferSize = 0;
    
    protected $bufferLimit;
    
    protected $flushOnOverflow;
    
    protected $buffer = [];
    
    protected $initialized = false;

    
    public function __construct(HandlerInterface $handler, int $bufferLimit = 0, $level = Logger::DEBUG, bool $bubble = true, bool $flushOnOverflow = false)
    {
        parent::__construct($level, $bubble);
        $this->handler = $handler;
        $this->bufferLimit = $bufferLimit;
        $this->flushOnOverflow = $flushOnOverflow;
    }

    
    public function handle(array $record): bool
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        if (!$this->initialized) {
            
            register_shutdown_function([$this, 'close']);
            $this->initialized = true;
        }

        if ($this->bufferLimit > 0 && $this->bufferSize === $this->bufferLimit) {
            if ($this->flushOnOverflow) {
                $this->flush();
            } else {
                array_shift($this->buffer);
                $this->bufferSize--;
            }
        }

        if ($this->processors) {
            
            $record = $this->processRecord($record);
        }

        $this->buffer[] = $record;
        $this->bufferSize++;

        return false === $this->bubble;
    }

    public function flush(): void
    {
        if ($this->bufferSize === 0) {
            return;
        }

        $this->handler->handleBatch($this->buffer);
        $this->clear();
    }

    public function __destruct()
    {
        
        
        
    }

    
    public function close(): void
    {
        $this->flush();

        $this->handler->close();
    }

    
    public function clear(): void
    {
        $this->bufferSize = 0;
        $this->buffer = [];
    }

    public function reset()
    {
        $this->flush();

        parent::reset();

        $this->resetProcessors();

        if ($this->handler instanceof ResettableInterface) {
            $this->handler->reset();
        }
    }

    
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($this->handler instanceof FormattableHandlerInterface) {
            $this->handler->setFormatter($formatter);

            return $this;
        }

        throw new \UnexpectedValueException('The nested handler of type '.get_class($this->handler).' does not support formatters.');
    }

    
    public function getFormatter(): FormatterInterface
    {
        if ($this->handler instanceof FormattableHandlerInterface) {
            return $this->handler->getFormatter();
        }

        throw new \UnexpectedValueException('The nested handler of type '.get_class($this->handler).' does not support formatters.');
    }
}
