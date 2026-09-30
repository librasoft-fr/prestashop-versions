<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * This file is part of the PrestaShop Enterprise solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Mcp;

/**
 * Decides which MCP tools must still pass through the human approval workflow.
 *
 * MCP servers advertise behavioral hints per tool via the `annotations` object
 * (readOnlyHint, openWorldHint, destructiveHint, idempotentHint). A tool that is
 * read-only AND operates against a closed world is safe to auto-execute. Every
 * other tool — including any that omits these hints — still requires operator
 * approval.
 *
 * The neuron-ai `ToolApproval` middleware is configured with the list of tool
 * names that REQUIRE approval (an empty list means "gate everything"), so this
 * helper returns exactly that list.
 */
final class ToolApprovalPolicy
{
    /**
     * @param iterable<mixed> $tools neuron-ai tools attached to the agent
     *
     * @return string[] names of tools that REQUIRE operator approval
     */
    public static function gatedToolNames(iterable $tools): array
    {
        $names = [];
        foreach ($tools as $tool) {
            if (!is_object($tool) || !method_exists($tool, 'getName')) {
                continue;
            }

            $name = (string) $tool->getName();
            if ($name === '') {
                continue;
            }

            // Fail closed: a tool that can't expose annotations can't prove it
            // is read-only + closed-world, so it stays gated.
            $annotations = method_exists($tool, 'getAnnotations') ? $tool->getAnnotations() : null;
            if (self::isAutoApprovable($annotations)) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    /**
     * A tool is auto-approvable only when it is explicitly read-only and
     * explicitly closed-world. Strict comparisons keep a missing or non-boolean
     * hint from accidentally skipping approval.
     *
     * @param mixed $annotations
     */
    private static function isAutoApprovable($annotations): bool
    {
        if (!is_array($annotations)) {
            return false;
        }

        return ($annotations['readOnlyHint'] ?? null) === true
            && ($annotations['openWorldHint'] ?? null) === false;
    }
}
