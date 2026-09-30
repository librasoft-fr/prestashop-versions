<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\SetLogLevelRequest;
use PsMcpServerDeps\Mcp\Schema\Result\EmptyResult;
use PsMcpServerDeps\Mcp\Server\Protocol;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

final class SetLogLevelHandler implements RequestHandlerInterface
{
    public function supports(Request $request): bool
    {
        return $request instanceof SetLogLevelRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response
    {
        \assert($request instanceof SetLogLevelRequest);

        $session->set(Protocol::SESSION_LOGGING_LEVEL, $request->level->value);

        return new Response($request->getId(), new EmptyResult());
    }
}
