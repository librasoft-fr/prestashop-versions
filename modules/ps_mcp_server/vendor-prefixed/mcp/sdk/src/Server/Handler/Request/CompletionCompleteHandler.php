<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\Completion\ProviderInterface;
use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Exception\PromptNotFoundException;
use PsMcpServerDeps\Mcp\Exception\ResourceNotFoundException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\PromptReference;
use PsMcpServerDeps\Mcp\Schema\Request\CompletionCompleteRequest;
use PsMcpServerDeps\Mcp\Schema\ResourceReference;
use PsMcpServerDeps\Mcp\Schema\Result\CompletionCompleteResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Container\ContainerInterface;

final class CompletionCompleteHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly ?ContainerInterface $container = null,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof CompletionCompleteRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response|Error
    {
        \assert($request instanceof CompletionCompleteRequest);

        $name = $request->argument['name'] ?? '';
        $value = $request->argument['value'] ?? '';

        try {
            $reference = match (true) {
                $request->ref instanceof PromptReference => $this->registry->getPrompt($request->ref->name),
                $request->ref instanceof ResourceReference => $this->registry->getResource($request->ref->uri),
            };

            $providers = $reference->completionProviders;
            $provider = $providers[$name] ?? null;
            if (null === $provider) {
                return new Response($request->getId(), new CompletionCompleteResult([]));
            }

            if (\is_string($provider)) {
                if (!class_exists($provider)) {
                    return Error::forInternalError('Invalid completion provider', $request->getId());
                }
                $provider = $this->container?->has($provider) ? $this->container->get($provider) : new $provider();
            }

            if (!$provider instanceof ProviderInterface) {
                return Error::forInternalError('Invalid completion provider type', $request->getId());
            }

            $completions = $provider->getCompletions($value);
            $total = \count($completions);
            $hasMore = $total > 100;
            $paged = \array_slice($completions, 0, 100);

            return new Response($request->getId(), new CompletionCompleteResult($paged, $total, $hasMore));
        } catch (PromptNotFoundException|ResourceNotFoundException $e) {
            return Error::forResourceNotFound($e->getMessage(), $request->getId());
        } catch (\Throwable $e) {
            return Error::forInternalError('Error while handling completion request', $request->getId());
        }
    }
}
