<?php

namespace PsMcpServerDeps\Mcp\Server;

use Psr\Clock\ClockInterface;

class NativeClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
