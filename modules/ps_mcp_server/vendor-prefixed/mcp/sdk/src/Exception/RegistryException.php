<?php

namespace PsMcpServerDeps\Mcp\Exception;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;

final class RegistryException extends \Exception implements ExceptionInterface
{
    public static function invalidParams(string $message = 'Invalid params', ?\Throwable $previous = null): self
    {
        return new self($message, Error::INVALID_PARAMS, $previous);
    }

    public static function internalError(?string $details = null, ?\Throwable $previous = null): self
    {
        $message = 'Internal error';
        if (null !== $details) {
            $message .= ': ' . $details;
        }
        if (null !== $previous) {
            $message .= ' (See server logs)';
        }

        return new self($message, Error::INTERNAL_ERROR, $previous);
    }
}
