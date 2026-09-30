<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Processor;

use PsMcpServerDeps\Monolog\Logger;

class IntrospectionProcessor implements ProcessorInterface
{
    private $level;

    private $skipClassesPartials;

    private $skipStackFramesCount;

    private $skipFunctions = [
        'call_user_func',
        'call_user_func_array',
    ];

    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = [], int $skipStackFramesCount = 0)
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = array_merge(['PsMcpServerDeps\\Monolog\\'], $skipClassesPartials);
        $this->skipStackFramesCount = $skipStackFramesCount;
    }

    public function __invoke(array $record): array
    {
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        array_shift($trace);

        array_shift($trace);

        $i = 0;

        while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
            if (isset($trace[$i]['class'])) {
                foreach ($this->skipClassesPartials as $part) {
                    if (strpos($trace[$i]['class'], $part) !== false) {
                        ++$i;

                        continue 2;
                    }
                }
            } elseif (in_array($trace[$i]['function'], $this->skipFunctions)) {
                ++$i;

                continue;
            }

            break;
        }

        $i += $this->skipStackFramesCount;

        $record['extra'] = array_merge(
            $record['extra'],
            [
                'file' => isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : null,
                'line' => isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : null,
                'class' => isset($trace[$i]['class']) ? $trace[$i]['class'] : null,
                'callType' => isset($trace[$i]['type']) ? $trace[$i]['type'] : null,
                'function' => isset($trace[$i]['function']) ? $trace[$i]['function'] : null,
            ]
        );

        return $record;
    }

    private function isTraceClassOrSkippedFunction(array $trace, int $index): bool
    {
        if (!isset($trace[$index])) {
            return false;
        }

        return isset($trace[$index]['class']) || in_array($trace[$index]['function'], $this->skipFunctions);
    }
}
