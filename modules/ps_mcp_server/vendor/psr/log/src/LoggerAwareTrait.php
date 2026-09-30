<?php

namespace Psr\Log;


trait LoggerAwareTrait
{
    
    protected ?LoggerInterface $logger = null;

    
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
