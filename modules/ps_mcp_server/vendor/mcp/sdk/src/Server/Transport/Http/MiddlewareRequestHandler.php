<?php



namespace Mcp\Server\Transport\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


final class MiddlewareRequestHandler implements RequestHandlerInterface
{
    private int $index = 0;

    
    public function __construct(
        private readonly array $middleware,
        private readonly \Closure $application,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$this->index])) {
            return ($this->application)($request);
        }

        return $this->middleware[$this->index++]->process($request, $this);
    }
}
