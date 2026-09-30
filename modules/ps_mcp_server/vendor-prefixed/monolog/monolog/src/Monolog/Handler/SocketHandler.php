<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;

class SocketHandler extends AbstractProcessingHandler
{
    private $connectionString;

    private $connectionTimeout;

    private $resource;

    private $timeout;

    private $writingTimeout;

    private $lastSentBytes;

    private $chunkSize;

    private $persistent;

    private $errno;

    private $errstr;

    private $lastWritingAt;

    public function __construct(
        string $connectionString,
        $level = Logger::DEBUG,
        bool $bubble = true,
        bool $persistent = false,
        float $timeout = 0.0,
        float $writingTimeout = 10.0,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null,
    ) {
        parent::__construct($level, $bubble);
        $this->connectionString = $connectionString;

        if ($connectionTimeout !== null) {
            $this->validateTimeout($connectionTimeout);
        }

        $this->connectionTimeout = $connectionTimeout ?? (float) ini_get('default_socket_timeout');
        $this->persistent = $persistent;
        $this->validateTimeout($timeout);
        $this->timeout = $timeout;
        $this->validateTimeout($writingTimeout);
        $this->writingTimeout = $writingTimeout;
        $this->chunkSize = $chunkSize;
    }

    protected function write(array $record): void
    {
        $this->connectIfNotConnected();
        $data = $this->generateDataStream($record);
        $this->writeToSocket($data);
    }

    public function close(): void
    {
        if (!$this->isPersistent()) {
            $this->closeSocket();
        }
    }

    public function closeSocket(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
            $this->resource = null;
        }
    }

    public function setPersistent(bool $persistent): self
    {
        $this->persistent = $persistent;

        return $this;
    }

    public function setConnectionTimeout(float $seconds): self
    {
        $this->validateTimeout($seconds);
        $this->connectionTimeout = $seconds;

        return $this;
    }

    public function setTimeout(float $seconds): self
    {
        $this->validateTimeout($seconds);
        $this->timeout = $seconds;

        return $this;
    }

    public function setWritingTimeout(float $seconds): self
    {
        $this->validateTimeout($seconds);
        $this->writingTimeout = $seconds;

        return $this;
    }

    public function setChunkSize(int $bytes): self
    {
        $this->chunkSize = $bytes;

        return $this;
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

    public function isPersistent(): bool
    {
        return $this->persistent;
    }

    public function getConnectionTimeout(): float
    {
        return $this->connectionTimeout;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getWritingTimeout(): float
    {
        return $this->writingTimeout;
    }

    public function getChunkSize(): ?int
    {
        return $this->chunkSize;
    }

    public function isConnected(): bool
    {
        return is_resource($this->resource)
            && !feof($this->resource);
    }

    protected function pfsockopen()
    {
        return @pfsockopen($this->connectionString, -1, $this->errno, $this->errstr, $this->connectionTimeout);
    }

    protected function fsockopen()
    {
        return @fsockopen($this->connectionString, -1, $this->errno, $this->errstr, $this->connectionTimeout);
    }

    protected function streamSetTimeout()
    {
        $seconds = floor($this->timeout);
        $microseconds = round(($this->timeout - $seconds) * 1e6);

        if (!is_resource($this->resource)) {
            throw new \LogicException('streamSetTimeout called but $this->resource is not a resource');
        }

        return stream_set_timeout($this->resource, (int) $seconds, (int) $microseconds);
    }

    protected function streamSetChunkSize()
    {
        if (!is_resource($this->resource)) {
            throw new \LogicException('streamSetChunkSize called but $this->resource is not a resource');
        }

        if (null === $this->chunkSize) {
            throw new \LogicException('streamSetChunkSize called but $this->chunkSize is not set');
        }

        return stream_set_chunk_size($this->resource, $this->chunkSize);
    }

    protected function fwrite(string $data)
    {
        if (!is_resource($this->resource)) {
            throw new \LogicException('fwrite called but $this->resource is not a resource');
        }

        return @fwrite($this->resource, $data);
    }

    protected function streamGetMetadata()
    {
        if (!is_resource($this->resource)) {
            throw new \LogicException('streamGetMetadata called but $this->resource is not a resource');
        }

        return stream_get_meta_data($this->resource);
    }

    private function validateTimeout(float $value): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Timeout must be 0 or a positive float (got $value)");
        }
    }

    private function connectIfNotConnected(): void
    {
        if ($this->isConnected()) {
            return;
        }
        $this->connect();
    }

    protected function generateDataStream(array $record): string
    {
        return (string) $record['formatted'];
    }

    protected function getResource()
    {
        return $this->resource;
    }

    private function connect(): void
    {
        $this->createSocketResource();
        $this->setSocketTimeout();
        $this->setStreamChunkSize();
    }

    private function createSocketResource(): void
    {
        if ($this->isPersistent()) {
            $resource = $this->pfsockopen();
        } else {
            $resource = $this->fsockopen();
        }
        if (is_bool($resource)) {
            throw new \UnexpectedValueException("Failed connecting to $this->connectionString ($this->errno: $this->errstr)");
        }
        $this->resource = $resource;
    }

    private function setSocketTimeout(): void
    {
        if (!$this->streamSetTimeout()) {
            throw new \UnexpectedValueException('Failed setting timeout with stream_set_timeout()');
        }
    }

    private function setStreamChunkSize(): void
    {
        if ($this->chunkSize && !$this->streamSetChunkSize()) {
            throw new \UnexpectedValueException('Failed setting chunk size with stream_set_chunk_size()');
        }
    }

    private function writeToSocket(string $data): void
    {
        $length = strlen($data);
        $sent = 0;
        $this->lastSentBytes = $sent;
        while ($this->isConnected() && $sent < $length) {
            if (0 == $sent) {
                $chunk = $this->fwrite($data);
            } else {
                $chunk = $this->fwrite(substr($data, $sent));
            }
            if ($chunk === false) {
                throw new \RuntimeException('Could not write to socket');
            }
            $sent += $chunk;
            $socketInfo = $this->streamGetMetadata();
            if (is_array($socketInfo) && $socketInfo['timed_out']) {
                throw new \RuntimeException('Write timed-out');
            }

            if ($this->writingIsTimedOut($sent)) {
                throw new \RuntimeException("Write timed-out, no data sent for `{$this->writingTimeout}` seconds, probably we got disconnected (sent $sent of $length)");
            }
        }
        if (!$this->isConnected() && $sent < $length) {
            throw new \RuntimeException("End-of-file reached, probably we got disconnected (sent $sent of $length)");
        }
    }

    private function writingIsTimedOut(int $sent): bool
    {
        if (0.0 == $this->writingTimeout) {
            return false;
        }

        if ($sent !== $this->lastSentBytes) {
            $this->lastWritingAt = microtime(true);
            $this->lastSentBytes = $sent;

            return false;
        }
        usleep(100);

        if ((microtime(true) - $this->lastWritingAt) >= $this->writingTimeout) {
            $this->closeSocket();

            return true;
        }

        return false;
    }
}
