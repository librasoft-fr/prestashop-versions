<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\PingRequest;
use PsMcpServerDeps\Mcp\Schema\Result\EmptyResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class PingHandler implements RequestHandlerInterface
{
    public function supports(Request $request): bool
    {
        return $request instanceof PingRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof PingRequest);

        return new Response($request->getId(), new EmptyResult());
    }
}
