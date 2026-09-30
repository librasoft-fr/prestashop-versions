<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Integration;

use Psr\Http\Message\ServerRequestInterface;

interface RequestFetcherInterface
{
    public function fetchRequest(): ?ServerRequestInterface;
}
