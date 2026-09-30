<?php

namespace PsMcpServerDeps\Mcp\Event;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class ErrorEvent
{
    public function __construct(
        private Error $error,
        private readonly Request $request,
        private readonly SessionInterface $session,
        private readonly ?\Throwable $throwable,
    ) {
    }

    public function getError(): Error
    {
        return $this->error;
    }

    public function setError(Error $error): void
    {
        $this->error = $error;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }
}
