<?php

namespace PsMcpServerDeps\Mcp\Exception;

final class ToolNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public function __construct(
        public readonly string $name,
    ) {
        parent::__construct(\sprintf('Tool not found: "%s".', $name));
    }
}
