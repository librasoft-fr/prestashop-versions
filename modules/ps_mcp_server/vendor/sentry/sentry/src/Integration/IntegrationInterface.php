<?php

declare(strict_types=1);

namespace Sentry\Integration;


interface IntegrationInterface
{
    
    public function setupOnce(): void;
}
