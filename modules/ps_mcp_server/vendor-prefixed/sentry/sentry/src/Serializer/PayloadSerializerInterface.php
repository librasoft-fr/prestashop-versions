<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

use PsMcpServerDeps\Sentry\Event;

interface PayloadSerializerInterface
{
    public function serialize(Event $event): string;
}
