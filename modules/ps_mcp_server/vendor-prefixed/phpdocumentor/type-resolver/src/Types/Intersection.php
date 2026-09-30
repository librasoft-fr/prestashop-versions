<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\Types;

final class Intersection extends AggregatedType
{
    public function __construct(array $types)
    {
        parent::__construct($types, '&');
    }
}
