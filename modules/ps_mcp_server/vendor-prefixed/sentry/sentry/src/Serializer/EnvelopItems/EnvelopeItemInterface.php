<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer\EnvelopItems;

use PsMcpServerDeps\Sentry\Event;

interface EnvelopeItemInterface
{
    public static function toEnvelopeItem(Event $event): ?string;
}
