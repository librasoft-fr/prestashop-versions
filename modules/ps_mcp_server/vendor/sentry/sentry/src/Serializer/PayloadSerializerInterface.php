<?php

declare(strict_types=1);

namespace Sentry\Serializer;

use Sentry\Event;


interface PayloadSerializerInterface
{
    
    public function serialize(Event $event): string;
}
