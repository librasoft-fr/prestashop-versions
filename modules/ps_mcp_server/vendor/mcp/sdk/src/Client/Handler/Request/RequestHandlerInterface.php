<?php



namespace Mcp\Client\Handler\Request;

use Mcp\Schema\JsonRpc\Error;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;


interface RequestHandlerInterface
{
    
    public function supports(Request $request): bool;

    
    public function handle(Request $request): Response|Error;
}
