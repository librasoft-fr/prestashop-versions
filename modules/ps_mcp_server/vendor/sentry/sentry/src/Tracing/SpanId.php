<?php

declare(strict_types=1);

namespace Sentry\Tracing;

use Sentry\Util\SentryUid;


final class SpanId implements \Stringable
{
    
    private $value;

    
    public function __construct(string $value)
    {
        if (!preg_match('/^[a-f0-9]{16}$/i', $value)) {
            throw new \InvalidArgumentException('The $value argument must be a 16 characters long hexadecimal string.');
        }

        $this->value = $value;
    }

    
    public static function generate(): self
    {
        return new self(substr(SentryUid::generate(), 0, 16));
    }

    
    public function isEqualTo(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
