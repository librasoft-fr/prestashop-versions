<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

trait WebRequestRecognizerTrait
{
    protected function isWebRequest(): bool
    {
        return 'cli' !== \PHP_SAPI && 'phpdbg' !== \PHP_SAPI;
    }
}
