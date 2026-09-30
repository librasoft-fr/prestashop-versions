<?php



namespace Mcp\Server\Handler\Request;

use Mcp\Schema\JsonRpc\Request;
use Mcp\Schema\JsonRpc\Response;
use Mcp\Schema\Request\SetLogLevelRequest;
use Mcp\Schema\Result\EmptyResult;
use Mcp\Server\Protocol;
use Mcp\Server\Session\SessionInterface;


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
