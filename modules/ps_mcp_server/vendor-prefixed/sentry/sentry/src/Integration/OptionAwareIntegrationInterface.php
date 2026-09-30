<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use PsMcpServerDeps\Sentry\Options;

interface OptionAwareIntegrationInterface extends IntegrationInterface
{
    public function setOptions(Options $options): void;
}
