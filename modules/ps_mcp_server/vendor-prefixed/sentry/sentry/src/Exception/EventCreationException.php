<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Exception;

class EventCreationException extends \RuntimeException
{
    public function __construct(\Throwable $previous)
    {
        parent::__construct('Unable to instantiate an event', 0, $previous);
    }
}
