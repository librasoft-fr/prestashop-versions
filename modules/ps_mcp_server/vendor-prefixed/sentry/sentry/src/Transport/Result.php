<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Transport;

use PsMcpServerDeps\Sentry\Event;

class Result
{
    private $status;

    private $event;

    public function __construct(ResultStatus $status, ?Event $event = null)
    {
        $this->status = $status;
        $this->event = $event;
    }

    public function getStatus(): ResultStatus
    {
        return $this->status;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }
}
