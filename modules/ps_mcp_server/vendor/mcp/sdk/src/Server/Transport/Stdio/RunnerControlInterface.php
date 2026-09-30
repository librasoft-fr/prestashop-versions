<?php



namespace Mcp\Server\Transport\Stdio;


interface RunnerControlInterface
{
    public function getState(): RunnerState;
}
