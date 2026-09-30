<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Server\Session\SessionInterface;


interface RequestHandlerInterface
{
    public function supports(Request $request): bool;

    
    public function handle(Request $request, SessionInterface $session): Response|Error;
}
