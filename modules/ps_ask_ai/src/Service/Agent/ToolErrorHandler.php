<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of PrestaShop AskAI.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Agent;

use NeuronAI\Tools\ToolInterface;
use PrestaShop\Module\PsAskAi\Service\SentryReporter;
use Psr\Log\LoggerInterface;

/**
 * Serializable tool error handler.
 *
 * neuron-ai stores the agent's tool error handler on the ToolNode, and a
 * ToolApproval interrupt serializes that node to disk (FilePersistence) so the
 * workflow survives the approval HTTP round-trip. A Closure can't be
 * serialized ("Serialization of 'Closure' is not allowed"), so we use an
 * invokable class instead — mirroring neuron-ai's own ToolRejectionHandler.
 *
 * The logger is excluded from serialization: PSR loggers (e.g. Monolog with
 * handlers/closures) are frequently unserializable too, and logging is
 * best-effort anyway. After a resume the handler simply runs without a logger.
 *
 * Turns a tool execution failure into the tool's result instead of letting it
 * propagate. neuron-ai feeds the returned string back to the model as the tool
 * result, so the LLM can react (retry differently, choose another tool, or
 * explain the failure to the user) rather than the request 500-ing.
 *
 * neuron-ai has no protocol-level tool-failure flag (it never sets Anthropic's
 * is_error / a Gemini error functionResponse), so we follow the convention its
 * own toolkits use and return a structured `status: error` payload — making it
 * unambiguous to the model that the call failed.
 */
final class ToolErrorHandler
{
    public function __construct(
        private readonly ?LoggerInterface $logger = null,
        private readonly ?SentryReporter $sentryReporter = null,
    ) {
    }

    public function __invoke(\Throwable $e, ToolInterface $tool): string
    {
        $name = (string) $tool->getName();

        $this->logger?->error('AskAI tool execution failed', [
            'tool' => $name,
            'exception' => $e->getMessage(),
        ]);

        $this->sentryReporter?->captureException($e, 'tool_execution_failed', [], ['tool_name' => $name]);

        return (string) json_encode([
            'status' => 'error',
            'tool' => $name,
            'message' => $e->getMessage(),
            'guidance' => 'The tool call failed. Do not retry blindly — try a different approach or explain the failure to the user.',
        ]);
    }

    /**
     * Drop the logger so the handler survives the workflow-interrupt
     * serialize() round-trip. Restored without a logger on unserialize.
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->logger = null;
        $this->sentryReporter = new SentryReporter();
    }
}
