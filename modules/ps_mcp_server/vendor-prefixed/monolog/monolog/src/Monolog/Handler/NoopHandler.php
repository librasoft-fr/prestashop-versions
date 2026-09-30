<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

class NoopHandler extends Handler
{
    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        return false;
    }
}
