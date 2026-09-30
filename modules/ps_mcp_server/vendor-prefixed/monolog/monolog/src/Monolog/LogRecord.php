<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog;

interface LogRecord extends \ArrayAccess
{
    public function toArray(): array;
}
