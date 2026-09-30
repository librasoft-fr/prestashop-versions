<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Stdio;

interface RunnerControlInterface
{
    public function getState(): RunnerState;
}
