<?php



namespace Mcp\Server\Transport\Stdio;


enum RunnerState
{
    case RUNNING;
    case STOP_AND_END_SESSION;
    case STOP;
}
