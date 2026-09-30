<?php

declare(strict_types=1);

namespace Sentry\Serializer;


interface SerializerInterface
{
    
    public function serialize($value);
}
