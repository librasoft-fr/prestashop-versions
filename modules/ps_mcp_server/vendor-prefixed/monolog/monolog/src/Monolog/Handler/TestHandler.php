<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;

class TestHandler extends AbstractProcessingHandler
{
    protected $records = [];

    protected $recordsByLevel = [];

    private $skipReset = false;

    public function getRecords()
    {
        return $this->records;
    }

    public function clear()
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }

    public function reset()
    {
        if (!$this->skipReset) {
            $this->clear();
        }
    }

    public function setSkipReset(bool $skipReset)
    {
        $this->skipReset = $skipReset;
    }

    public function hasRecords($level): bool
    {
        return isset($this->recordsByLevel[Logger::toMonologLevel($level)]);
    }

    public function hasRecord($record, $level): bool
    {
        if (is_string($record)) {
            $record = ['message' => $record];
        }

        return $this->hasRecordThatPasses(function ($rec) use ($record) {
            if ($rec['message'] !== $record['message']) {
                return false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return false;
            }

            return true;
        }, $level);
    }

    public function hasRecordThatContains(string $message, $level): bool
    {
        return $this->hasRecordThatPasses(function ($rec) use ($message) {
            return strpos($rec['message'], $message) !== false;
        }, $level);
    }

    public function hasRecordThatMatches(string $regex, $level): bool
    {
        return $this->hasRecordThatPasses(function (array $rec) use ($regex): bool {
            return preg_match($regex, $rec['message']) > 0;
        }, $level);
    }

    public function hasRecordThatPasses(callable $predicate, $level)
    {
        $level = Logger::toMonologLevel($level);

        if (!isset($this->recordsByLevel[$level])) {
            return false;
        }

        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if ($predicate($rec, $i)) {
                return true;
            }
        }

        return false;
    }

    protected function write(array $record): void
    {
        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }

    public function __call($method, $args)
    {
        if (preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = constant('PsMcpServerDeps\Monolog\Logger::' . strtoupper($matches[2]));
            $callback = [$this, $genericMethod];
            if (is_callable($callback)) {
                $args[] = $level;

                return call_user_func_array($callback, $args);
            }
        }

        throw new \BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }
}
