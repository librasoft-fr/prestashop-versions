<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;

class DeduplicationHandler extends BufferHandler
{
    protected $deduplicationStore;

    protected $deduplicationLevel;

    protected $time;

    private $gc = false;

    public function __construct(HandlerInterface $handler, ?string $deduplicationStore = null, $deduplicationLevel = Logger::ERROR, int $time = 60, bool $bubble = true)
    {
        parent::__construct($handler, 0, Logger::DEBUG, $bubble, false);

        $this->deduplicationStore = $deduplicationStore === null ? sys_get_temp_dir() . '/monolog-dedup-' . substr(md5(__FILE__), 0, 20) . '.log' : $deduplicationStore;
        $this->deduplicationLevel = Logger::toMonologLevel($deduplicationLevel);
        $this->time = $time;
    }

    public function flush(): void
    {
        if ($this->bufferSize === 0) {
            return;
        }

        $passthru = null;

        foreach ($this->buffer as $record) {
            if ($record['level'] >= $this->deduplicationLevel) {
                $passthru = $passthru || !$this->isDuplicate($record);
                if ($passthru) {
                    $this->appendRecord($record);
                }
            }
        }

        if ($passthru === true || $passthru === null) {
            $this->handler->handleBatch($this->buffer);
        }

        $this->clear();

        if ($this->gc) {
            $this->collectLogs();
        }
    }

    private function isDuplicate(array $record): bool
    {
        if (!file_exists($this->deduplicationStore)) {
            return false;
        }

        $store = file($this->deduplicationStore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($store)) {
            return false;
        }

        $yesterday = time() - 86400;
        $timestampValidity = $record['datetime']->getTimestamp() - $this->time;
        $expectedMessage = preg_replace('{[\r\n].*}', '', $record['message']);

        for ($i = count($store) - 1; $i >= 0; --$i) {
            list($timestamp, $level, $message) = explode(':', $store[$i], 3);

            if ($level === $record['level_name'] && $message === $expectedMessage && $timestamp > $timestampValidity) {
                return true;
            }

            if ($timestamp < $yesterday) {
                $this->gc = true;
            }
        }

        return false;
    }

    private function collectLogs(): void
    {
        if (!file_exists($this->deduplicationStore)) {
            return;
        }

        $handle = fopen($this->deduplicationStore, 'rw+');

        if (!$handle) {
            throw new \RuntimeException('Failed to open file for reading and writing: ' . $this->deduplicationStore);
        }

        flock($handle, LOCK_EX);
        $validLogs = [];

        $timestampValidity = time() - $this->time;

        while (!feof($handle)) {
            $log = fgets($handle);
            if ($log && substr($log, 0, 10) >= $timestampValidity) {
                $validLogs[] = $log;
            }
        }

        ftruncate($handle, 0);
        rewind($handle);
        foreach ($validLogs as $log) {
            fwrite($handle, $log);
        }

        flock($handle, LOCK_UN);
        fclose($handle);

        $this->gc = false;
    }

    private function appendRecord(array $record): void
    {
        file_put_contents($this->deduplicationStore, $record['datetime']->getTimestamp() . ':' . $record['level_name'] . ':' . preg_replace('{[\r\n].*}', '', $record['message']) . "\n", FILE_APPEND);
    }
}
