<?php

namespace Psr\Log;


class NullLogger extends AbstractLogger
{
    
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        
    }
}
