<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

interface ProcessableHandlerInterface
{
    public function pushProcessor(callable $callback): HandlerInterface;

    public function popProcessor(): callable;
}
