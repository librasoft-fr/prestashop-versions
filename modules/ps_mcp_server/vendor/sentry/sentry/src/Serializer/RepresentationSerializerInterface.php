<?php

declare(strict_types=1);

namespace Sentry\Serializer;


interface RepresentationSerializerInterface
{
    
    public function representationSerialize($value);
}
