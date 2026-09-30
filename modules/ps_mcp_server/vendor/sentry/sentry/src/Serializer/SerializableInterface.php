<?php

declare(strict_types=1);

namespace Sentry\Serializer;


interface SerializableInterface
{
    
    public function toSentry(): ?array;
}
