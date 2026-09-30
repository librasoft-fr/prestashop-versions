<?php

declare(strict_types=1);

namespace Sentry;

use Sentry\Util\SentryUid;


final class EventId implements \Stringable
{
    
    private $value;

    
    public function __construct(string $value)
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $value)) {
            throw new \InvalidArgumentException('The $value argument must be a 32 characters long hexadecimal string.');
        }

        $this->value = $value;
    }

    
    public static function generate(): self
    {
        return new self(SentryUid::generate());
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
