<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\Request\PingRequest;
use Mcp\Schema\Result\EmptyResult;
use Mcp\Server\Session\SessionInterface;


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
