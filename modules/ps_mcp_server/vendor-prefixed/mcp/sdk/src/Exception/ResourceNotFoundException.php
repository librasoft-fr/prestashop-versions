<?php

namespace PsMcpServerDeps\Mcp\Exception;

final class ResourceNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(
        public readonly string $uri,
    ) {
        parent::__construct(\sprintf('Resource not found for uri: "%s".', $uri));
    }
}
