<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Utils;


class StreamHandler extends AbstractProcessingHandler
{
    
    protected const MAX_CHUNK_SIZE = 2147483647;
    
    protected const DEFAULT_CHUNK_SIZE = 10 * 1024 * 1024;
    
    protected $streamChunkSize;
    
    protected $stream;
    
    protected $url = null;
    
    private $errorMessage = null;
    
    protected $filePermission;
    
    protected $useLocking;
	
    protected $fileOpenMode;
    
    private $dirCreated = null;
    
    private $retrying = false;

    
    public function __construct($stream, $level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false, $fileOpenMode = 'a')
    {
        parent::__construct($level, $bubble);

        if (($phpMemoryLimit = Utils::expandIniShorthandBytes(ini_get('memory_limit'))) !== false) {
            if ($phpMemoryLimit > 0) {
                
                $this->streamChunkSize = min(static::MAX_CHUNK_SIZE, max((int) ($phpMemoryLimit / 10), 100 * 1024));
            } else {
                
                $this->streamChunkSize = static::DEFAULT_CHUNK_SIZE;
            }
        } else {
            
            $this->streamChunkSize = static::DEFAULT_CHUNK_SIZE;
        }

        if (is_resource($stream)) {
            $this->stream = $stream;

            stream_set_chunk_size($this->stream, $this->streamChunkSize);
        } elseif (is_string($stream)) {
            $this->url = Utils::canonicalizePath($stream);
        } else {
            throw new \InvalidArgumentException('A stream must either be a resource or a string.');
        }

        $this->fileOpenMode = $fileOpenMode;
        $this->filePermission = $filePermission;
        $this->useLocking = $useLocking;
    }

    
    public function close(): void
    {
        if ($this->url && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
        $this->dirCreated = null;
    }

    
    public function getStream()
    {
        return $this->stream;
    }

    
    public function getUrl(): ?string
    {
        return $this->url;
    }

    
    public function getStreamChunkSize(): int
    {
        return $this->streamChunkSize;
    }

    
    protected function write(array $record): void
    {
        if (!is_resource($this->stream)) {
            $url = $this->url;
            if (null === $url || '' === $url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().' . Utils::getRecordMessageForException($record));
            }
            $this->createDir($url);
            $this->errorMessage = null;
            set_error_handler(function (...$args) {
                return $this->customErrorHandler(...$args);
            });
            try {
                $stream = fopen($url, $this->fileOpenMode);
                if ($this->filePermission !== null) {
                    @chmod($url, $this->filePermission);
                }
            } finally {
                restore_error_handler();
            }
            if (!is_resource($stream)) {
                $this->stream = null;

                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened in append mode: '.$this->errorMessage, $url) . Utils::getRecordMessageForException($record));
            }
            stream_set_chunk_size($stream, $this->streamChunkSize);
            $this->stream = $stream;
        }

        $stream = $this->stream;
        if (!is_resource($stream)) {
            throw new \LogicException('No stream was opened yet' . Utils::getRecordMessageForException($record));
        }

        if ($this->useLocking) {
            
            flock($stream, LOCK_EX);
        }

        $this->errorMessage = null;
        set_error_handler(function (...$args) {
            return $this->customErrorHandler(...$args);
        });
        try {
            $this->streamWrite($stream, $record);
        } finally {
            restore_error_handler();
        }
        if ($this->errorMessage !== null) {
            $error = $this->errorMessage;
            
            if (!$this->retrying && $this->url !== null && $this->url !== 'php://memory') {
                $this->retrying = true;
                $this->close();
                $this->write($record);

                return;
            }

            throw new \UnexpectedValueException('Writing to the log file failed: '.$error . Utils::getRecordMessageForException($record));
        }

        $this->retrying = false;
        if ($this->useLocking) {
            flock($stream, LOCK_UN);
        }
    }

    
    protected function streamWrite($stream, array $record): void
    {
        fwrite($stream, (string) $record['formatted']);
    }

    private function customErrorHandler(int $code, string $msg): bool
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir|fwrite)\(.*?\): }', '', $msg);

        return true;
    }

    private function getDirFromStream(string $stream): ?string
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' === substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }

        return null;
    }

    private function createDir(string $url): void
    {
        
        if ($this->dirCreated) {
            return;
        }

        $dir = $this->getDirFromStream($url);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler(function (...$args) {
                return $this->customErrorHandler(...$args);
            });
            $status = mkdir($dir, 0777, true);
            restore_error_handler();
            if (false === $status && !is_dir($dir) && strpos((string) $this->errorMessage, 'File exists') === false) {
                throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and it could not be created: '.$this->errorMessage, $dir));
            }
        }
        $this->dirCreated = true;
    }
}
