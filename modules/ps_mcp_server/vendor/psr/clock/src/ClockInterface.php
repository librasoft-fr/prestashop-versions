<?php

namespace Psr\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    
    public function now(): DateTimeImmutable;
}
