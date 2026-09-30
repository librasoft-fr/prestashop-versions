<?php

namespace PsMcpServerDeps\Mcp\Event;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class ResponseEvent
{
    public function __construct(
        private Response $response,
        private readonly Request $request,
        private readonly SessionInterface $session,
    ) {
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getRequest(): Request
    {
        return $this->request;
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
