<?php

declare(strict_types=1);

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;


final class CachingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    
    private $remoteStream;

    
    private $skipReadBytes = 0;

    
    private $stream;

    
    private $detached = false;

    
    public function __construct(
        StreamInterface $stream,
        ?StreamInterface $target = null
    ) {
        $this->remoteStream = $stream;
        $this->stream = $target ?: new Stream(Utils::tryFopen('php://temp', 'r+'));
    }

    public function getSize(): ?int
    {
        if ($this->detached) {
            return null;
        }

        $remoteSize = $this->remoteStream->getSize();

        if (null === $remoteSize) {
            return null;
        }

        return max($this->stream->getSize(), $remoteSize);
    }

    public function rewind(): void
    {
        $this->seek(0);
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

        if ($whence === SEEK_SET) {
            $byte = $offset;
        } elseif ($whence === SEEK_CUR) {
            $byte = $offset + $this->tell();
        } elseif ($whence === SEEK_END) {
            $size = $this->remoteStream->getSize();
            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            $byte = $size + $offset;
        } else {
            throw new \InvalidArgumentException('Invalid whence');
        }

        $diff = $byte - $this->stream->getSize();

        if ($diff > 0) {
            
            
            while ($diff > 0 && !$this->remoteStream->eof()) {
                $previousSize = $this->stream->getSize();
                $previousSkipReadBytes = $this->skipReadBytes;
                $data = $this->read($diff);
                $currentSize = $this->stream->getSize();

                if ($data === '' && $currentSize === $previousSize && $this->skipReadBytes === $previousSkipReadBytes) {
                    break;
                }

                $diff = $byte - $currentSize;
            }
        } else {
            
            $this->stream->seek($byte);
        }
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

        
        $data = $this->stream->read($length);
        $remaining = $length - strlen($data);

        
        if ($remaining) {
            
            
            
            
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes) {
                $len = strlen($remoteData);
                $remoteData = substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;

            
            if ($this->stream->write($remoteData) !== strlen($remoteData)) {
                throw new \RuntimeException('Unable to cache the entire read from the remote stream');
            }
        }

        return $data;
    }

    public function write($string): int
    {
        if (!\is_string($string)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::write() is deprecated; guzzlehttp/psr7 3.0 requires string for $string.',
                \get_debug_type($string)
            );
        }

        
        
        
        
        $overflow = (strlen($string) + $this->tell()) - $this->remoteStream->tell();
        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    public function eof(): bool
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    public function detach()
    {
        if ($this->detached) {
            return null;
        }

        $position = $this->tell();

        $this->cacheEntireStream();
        $this->stream->seek($position);

        $resource = $this->stream->detach();
        $this->detached = true;

        return $resource;
    }

    
    public function close(): void
    {
        $this->remoteStream->close();
        $this->stream->close();
        $this->detached = true;
    }

    private function cacheEntireStream(): int
    {
        $target = new FnStream(['write' => 'strlen']);
        Utils::copyToStream($this, $target);

        return $this->tell();
    }
}
