<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Mcp;

use NeuronAI\MCP\McpConnector;

/**
 * Drop-in replacement for neuron-ai's CallableMcpTool that removes the
 * synthetic `reason` argument (injected into every tool schema by
 * ReasonedMcpConnector) before forwarding the call to the MCP server —
 * ps_mcp_server binds arguments to PHP method parameters by name, so an
 * undeclared argument would fail the call.
 *
 * Must stay an invokable class with only serializable properties: tool
 * callables are serialized to disk by FilePersistence across the
 * tool-approval HTTP round-trip (closures would fail there).
 */
final class ReasonStrippingMcpCaller
{
    /**
     * @param array<string, mixed> $item raw MCP listTools entry for this tool
     */
    public function __construct(
        private readonly McpConnector $connector,
        private readonly array $item,
    ) {
    }

    public function __invoke(mixed ...$arguments): mixed
    {
        unset($arguments[ReasonedMcpConnector::REASON_PROPERTY]);

        return $this->connector->invokeTool(
            item: $this->item,
            arguments: $arguments,
        );
    }
}
