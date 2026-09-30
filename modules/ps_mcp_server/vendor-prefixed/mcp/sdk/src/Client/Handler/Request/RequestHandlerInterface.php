<?php

namespace PsMcpServerDeps\Mcp\Client\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;

interface RequestHandlerInterface
{
    public function supports(Request $request): bool;

    public function handle(Request $request): Response|Error;
}
