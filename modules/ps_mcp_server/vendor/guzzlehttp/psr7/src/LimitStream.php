<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;


final class LimitStream implements StreamInterface
{
    use StreamDecoratorTrait;

    
    private $offset;

    
    private $limit;

    
    private $stream;

    
    public function __construct(
        StreamInterface $stream,
        int $limit = -1,
        int $offset = 0
    ) {
        $this->stream = $stream;
        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    public function eof(): bool
    {
        
        if ($this->stream->eof()) {
            return true;
        }

        
        if ($this->limit === -1) {
            return false;
        }

        return $this->stream->tell() >= $this->offset + $this->limit;
    }

    
    public function getSize(): ?int
    {
        if (null === ($length = $this->stream->getSize())) {
            return null;
        }

        $size = $length - $this->offset;

        if ($this->limit !== -1) {
            $size = min($this->limit, $size);
        }

        return max(0, $size);
    }

    
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!\is_int($offset)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::seek() is deprecated; guzzlehttp/psr7 3.0 requires int for $offset.',
                \get_debug_type($offset)
            );
        }

        if (!\is_int($whence)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::seek() is deprecated; guzzlehttp/psr7 3.0 requires int for $whence.',
                \get_debug_type($whence)
            );
        }

        if ($whence !== SEEK_SET || $offset < 0) {
            throw new \RuntimeException(sprintf(
                'Cannot seek to offset %s with whence %s',
                $offset,
                $whence
            ));
        }

        $offset += $this->offset;

        if ($this->limit !== -1) {
            if ($offset > $this->offset + $this->limit) {
                $offset = $this->offset + $this->limit;
            }
        }

        $this->stream->seek($offset);
    }

    
    public function tell(): int
    {
        return $this->stream->tell() - $this->offset;
    }

    
    public function setOffset(int $offset): void
    {
        $current = $this->stream->tell();

        if ($current !== $offset) {
            
            if ($this->stream->isSeekable()) {
                $this->stream->seek($offset);
            } elseif ($current > $offset) {
                throw new \RuntimeException("Could not seek to stream offset $offset");
            } else {
                $this->stream->read($offset - $current);
            }
        }

        $this->offset = $offset;
    }

    
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function read($length): string
    {
        if (!\is_int($length)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::read() is deprecated; guzzlehttp/psr7 3.0 requires int for $length.',
                \get_debug_type($length)
            );
        }

        if ($this->limit === -1) {
            return $this->stream->read($length);
        }

        
        
        $remaining = ($this->offset + $this->limit) - $this->stream->tell();
        if ($remaining > 0) {
            
            
            return $this->stream->read(min($remaining, $length));
        }

        return '';
    }
}
