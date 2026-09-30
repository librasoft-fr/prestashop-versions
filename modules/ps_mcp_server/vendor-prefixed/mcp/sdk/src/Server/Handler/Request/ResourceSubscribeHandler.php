<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Exception\ResourceNotFoundException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\ResourceSubscribeRequest;
use PsMcpServerDeps\Mcp\Schema\Result\EmptyResult;
use PsMcpServerDeps\Mcp\Server\Resource\SubscriptionManagerInterface;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ResourceSubscribeHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly SubscriptionManagerInterface $subscriptionManager,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ResourceSubscribeRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response|Error
    {
        \assert($request instanceof ResourceSubscribeRequest);

        $uri = $request->uri;

        try {
            $this->registry->getResource($uri);
        } catch (ResourceNotFoundException $e) {
            $this->logger->error('Resource not found', ['uri' => $uri]);

            return Error::forResourceNotFound($e->getMessage(), $request->getId());
        }

        $this->logger->debug('Subscribing to resource', ['uri' => $uri]);

        $this->subscriptionManager->subscribe($session, $uri);

        return new Response(
            $request->getId(),
            new EmptyResult(),
        );
    }
}
