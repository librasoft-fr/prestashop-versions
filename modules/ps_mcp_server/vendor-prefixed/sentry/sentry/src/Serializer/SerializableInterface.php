<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

interface SerializableInterface
{
    public function toSentry(): ?array;
}
