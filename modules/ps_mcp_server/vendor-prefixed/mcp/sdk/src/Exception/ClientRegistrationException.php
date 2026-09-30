<?php

namespace PsMcpServerDeps\Mcp\Exception;

final class ClientRegistrationException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'invalid_client_metadata',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
