<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

class Serializer extends AbstractSerializer implements SerializerInterface
{
    public function serialize($value)
    {
        return $this->serializeRecursively($value);
    }
}
