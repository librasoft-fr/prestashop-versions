<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

interface RepresentationSerializerInterface
{
    public function representationSerialize($value);
}
