<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Transport;

use PsMcpServerDeps\Sentry\Event;

interface TransportInterface
{
    public function send(Event $event): Result;

    public function close(?int $timeout = null): Result;
}
