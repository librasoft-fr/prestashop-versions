<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\Registry\ReferenceHandlerInterface;
use PsMcpServerDeps\Mcp\Capability\Registry\ResourceTemplateReference;
use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Exception\ResourceNotFoundException;
use PsMcpServerDeps\Mcp\Exception\ResourceReadException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\ReadResourceRequest;
use PsMcpServerDeps\Mcp\Schema\Result\ReadResourceResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ReadResourceHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $referenceProvider,
        private readonly ReferenceHandlerInterface $referenceHandler,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof ReadResourceRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response|Error
    {
        \assert($request instanceof ReadResourceRequest);

        $uri = $request->uri;

        $this->logger->debug('Reading resource', ['uri' => $uri]);

        try {
            $reference = $this->referenceProvider->getResource($uri);

            $arguments = [
                'uri' => $uri,
                '_session' => $session,
                '_request' => $request,
            ];

            if ($reference instanceof ResourceTemplateReference) {
                $variables = $reference->extractVariables($uri);
                $arguments = array_merge($arguments, $variables);

                $result = $this->referenceHandler->handle($reference, $arguments);
                $formatted = $reference->formatResult($result, $uri, $reference->resourceTemplate->mimeType);
            } else {
                $result = $this->referenceHandler->handle($reference, $arguments);
                $formatted = $reference->formatResult($result, $uri, $reference->resource->mimeType);
            }

            return new Response($request->getId(), new ReadResourceResult($formatted));
        } catch (ResourceReadException $e) {
            $this->logger->error(\sprintf('Error while reading resource "%s": "%s".', $uri, $e->getMessage()));

            return Error::forInternalError($e->getMessage(), $request->getId());
        } catch (ResourceNotFoundException $e) {
            $this->logger->error('Resource not found', ['uri' => $uri]);

            return Error::forResourceNotFound($e->getMessage(), $request->getId());
        } catch (\Throwable $e) {
            $this->logger->error(\sprintf('Unexpected error while reading resource "%s": "%s".', $uri, $e->getMessage()));

            return Error::forInternalError('Error while reading resource', $request->getId());
        }
    }
}
