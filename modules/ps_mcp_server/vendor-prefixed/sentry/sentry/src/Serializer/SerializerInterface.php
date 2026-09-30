<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

interface SerializerInterface
{
    public function serialize($value);
}
