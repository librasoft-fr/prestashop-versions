<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

interface IntegrationInterface
{
    public function setupOnce(): void;
}
