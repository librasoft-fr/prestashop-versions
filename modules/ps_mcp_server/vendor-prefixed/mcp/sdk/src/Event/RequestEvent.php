<?php

namespace PsMcpServerDeps\Mcp\Event;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class RequestEvent
{
    public function __construct(
        private Request $request,
        private readonly SessionInterface $session,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getMethod(): string
    {
        return $this->request::getMethod();
    }
}
