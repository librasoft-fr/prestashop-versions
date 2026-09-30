<?php

namespace PsMcpServerDeps\Mcp\Server\Transport\Http\Middleware;

use PsMcpServerDeps\Http\Discovery\Psr17FactoryDiscovery;
use PsMcpServerDeps\Mcp\Server\Transport\Http\OAuth\ProtectedResourceMetadata;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ProtectedResourceMetadataMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly ProtectedResourceMetadata $metadata,
        ?ResponseFactoryInterface $responseFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->responseFactory = $responseFactory ?? Psr17FactoryDiscovery::findResponseFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isMetadataRequest($request)) {
            return $handler->handle($request);
        }

        return $this->responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streamFactory->createStream(json_encode($this->metadata, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES)));
    }

    private function isMetadataRequest(ServerRequestInterface $request): bool
    {
        if ('GET' !== $request->getMethod()) {
            return false;
        }

        return \in_array($request->getUri()->getPath(), $this->metadata->getMetadataPaths(), true);
    }
}
