<?php



namespace Mcp\Server\Transport;

use Mcp\Exception\RuntimeException;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


final class CallbackStream implements \Stringable, StreamInterface
{
    private bool $called = false;

    private ?\Throwable $exception = null;

    
    public function __construct(private $callback, private LoggerInterface $logger = new NullLogger())
    {
    }

    public function __toString(): string
    {
        try {
            $this->invoke();
        } catch (\Throwable $e) {
            $this->exception = $e;
            $this->logger->error(
                \sprintf('CallbackStream execution failed: %s', $e->getMessage()),
                ['exception' => $e]
            );
        }

        return '';
    }

    public function read($length): string
    {
        $this->invoke();

        if (null !== $this->exception) {
            throw $this->exception;
        }

        return '';
    }

    public function getContents(): string
    {
        $this->invoke();

        if (null !== $this->exception) {
            throw $this->exception;
        }

        return '';
    }

    public function eof(): bool
    {
        return $this->called;
    }

    public function close(): void
    {
        
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): ?int
    {
        return null; 
    }

    public function tell(): int
    {
        return 0;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        throw new RuntimeException('Stream is not seekable');
    }

    public function rewind(): void
    {
        throw new RuntimeException('Stream is not rewindable');
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException('Stream is not writable');
    }

    public function isReadable(): bool
    {
        return !$this->called;
    }

    private function invoke(): void
    {
        if ($this->called) {
            return;
        }

        $this->called = true;
        $this->exception = null;
        ($this->callback)();
    }

    public function getMetadata($key = null)
    {
        return null === $key ? [] : null;
    }
}
