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
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolProperty;

/**
 * McpConnector that injects a synthetic `reason` property into every tool
 * schema so the LLM explains each call in the same completion that emits it
 * (no extra request, no added latency). The value is surfaced to the operator
 * in the tool-call / approval UI and stripped again by
 * ReasonStrippingMcpCaller before the MCP server is hit.
 */
class ReasonedMcpConnector extends McpConnector
{
    public const REASON_PROPERTY = 'reason';

    private const REASON_DESCRIPTION = 'A short activity label, in the user\'s language, describing this tool'
        . ' call — shown to the user to help them approve or reject it. Phrase it as a'
        . ' neutral action-in-progress description (a gerund/noun phrase, like a network'
        . ' activity indicator), never in the first person. For example write'
        . ' "Fetching the product list" or "Updating stock for product X", NOT'
        . ' "I\'m looking up the products" or "I will update the stock". Never leave it empty.';

    /**
     * @param array<string, mixed> $item
     */
    protected function createTool(array $item): ToolInterface
    {
        $tool = parent::createTool($item);

        $tool->addProperty(new ToolProperty(
            name: self::REASON_PROPERTY,
            type: PropertyType::STRING,
            description: self::REASON_DESCRIPTION,
            required: true,
        ));

        // parent::createTool wired a CallableMcpTool that forwards every
        // input verbatim — replace it with the reason-stripping variant.
        $tool->setCallable(new ReasonStrippingMcpCaller($this, $item));

        return $tool;
    }
}
