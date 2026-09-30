<?php

namespace PsMcpServerDeps\Mcp\Server\Handler\Request;

use PsMcpServerDeps\Mcp\Capability\Registry\ReferenceHandlerInterface;
use PsMcpServerDeps\Mcp\Capability\RegistryInterface;
use PsMcpServerDeps\Mcp\Exception\PromptGetException;
use PsMcpServerDeps\Mcp\Exception\PromptNotFoundException;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Error;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Request;
use PsMcpServerDeps\Mcp\Schema\JsonRpc\Response;
use PsMcpServerDeps\Mcp\Schema\Request\GetPromptRequest;
use PsMcpServerDeps\Mcp\Schema\Result\GetPromptResult;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GetPromptHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly ReferenceHandlerInterface $referenceHandler,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request instanceof GetPromptRequest;
    }

    public function handle(Request $request, SessionInterface $session): Response|Error
    {
        \assert($request instanceof GetPromptRequest);

        $promptName = $request->name;
        $arguments = $request->arguments ?? [];

        try {
            $reference = $this->registry->getPrompt($promptName);

            $arguments['_session'] = $session;
            $arguments['_request'] = $request;

            $result = $this->referenceHandler->handle($reference, $arguments);

            $formatted = $reference->formatResult($result);

            return new Response($request->getId(), new GetPromptResult($formatted));
        } catch (PromptGetException $e) {
            $this->logger->error(\sprintf('Error while handling prompt "%s": "%s".', $promptName, $e->getMessage()));

            return Error::forInternalError($e->getMessage(), $request->getId());
        } catch (PromptNotFoundException $e) {
            $this->logger->error('Prompt not found', ['prompt_name' => $promptName]);

            return Error::forResourceNotFound($e->getMessage(), $request->getId());
        } catch (\Throwable $e) {
            $this->logger->error(\sprintf('Unexpected error while handling prompt "%s": "%s".', $promptName, $e->getMessage()));

            return Error::forInternalError('Error while handling prompt', $request->getId());
        }
    }
}
