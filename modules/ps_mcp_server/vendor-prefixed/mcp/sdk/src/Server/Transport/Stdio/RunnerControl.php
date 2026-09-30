<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Stdio;

class RunnerControl implements RunnerControlInterface
{
    public static RunnerState $state = RunnerState::RUNNING;

    public function getState(): RunnerState
    {
        return self::$state;
    }
}
