<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;

interface RequestHandlerInterface
{
    public function supports(Request $request): bool;

    public function handle(Request $request, SessionInterface $session): Response|Error;
}
