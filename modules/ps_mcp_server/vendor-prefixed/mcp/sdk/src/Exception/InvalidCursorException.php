<?php

namespace PsMcpServerDeps\Mcp\Exception;

final class InvalidCursorException extends \InvalidArgumentException implements ExceptionInterface
{
    public function __construct(
        public readonly string $cursor,
    ) {
        parent::__construct(\sprintf('Invalid value for pagination parameter "cursor": "%s"', $cursor));
    }
}
