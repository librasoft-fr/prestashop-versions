<?php

declare(strict_types=1);

namespace Sentry\Integration;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;


final class RequestFetcher implements RequestFetcherInterface
{
    
    public function fetchRequest(): ?ServerRequestInterface
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || \PHP_SAPI === 'cli') {
            return null;
        }

        return ServerRequest::fromGlobals();
    }
}
