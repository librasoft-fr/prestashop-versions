<?php

declare(strict_types=1);

namespace Sentry\Integration;

use Sentry\Options;

interface OptionAwareIntegrationInterface extends IntegrationInterface
{
    
    public function setOptions(Options $options): void;
}
