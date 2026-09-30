<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;

class NullHandler extends Handler
{
    private $level;

    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    public function isHandling(array $record): bool
    {
        return $record['level'] >= $this->level;
    }

    public function handle(array $record): bool
    {
        return $record['level'] >= $this->level;
    }
}
