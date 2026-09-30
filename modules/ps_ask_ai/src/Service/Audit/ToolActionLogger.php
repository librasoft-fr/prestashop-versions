<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Audit;

use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\ObserverInterface;
use NeuronAI\Tools\ToolInterface;

/**
 * Audit trail of tool executions triggered by employees through AskAI.
 *
 * Attached to the agent via Workflow::observe(); ToolNode emits `tool-called`
 * in a `finally` after every tool execution — success, error (ToolErrorHandler
 * turned the throwable into the result) and approval rejection (ToolApproval
 * swaps the callable for ToolRejectionHandler, so the tool still "executes").
 * One hook therefore covers every tool — MCP and local — on both the initial
 * stream and the post-approval resume.
 *
 * Writes go through legacy Db, NOT Doctrine: the event fires mid-SSE-stream,
 * after headers went out, where a Doctrine flush is unsafe (same constraint
 * that makes SerializableSqlChatHistory use raw PDO). The instance holds only
 * scalars so it stays serialize()-safe should it ever end up in a persisted
 * workflow graph.
 */
final class ToolActionLogger implements ObserverInterface
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_REJECTED = 'rejected';

    public const APPROVAL_AUTO = 'auto';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    private const TABLE = 'ps_ask_ai_tool_log';

    /**
     * Keep rows bounded: inputs are audit context ("who did what"), not a
     * replay payload. TEXT holds 64KB; we cut well below.
     */
    private const MAX_INPUTS_BYTES = 8192;

    /**
     * @param array<string, string> $approvalDecisions callId => APPROVAL_APPROVED|APPROVAL_REJECTED,
     *                                                 built from the human decisions on the resume
     *                                                 path; empty on an initial turn (a gated tool
     *                                                 interrupts BEFORE executing, so anything that
     *                                                 runs on the initial turn ran without approval)
     */
    public function __construct(
        private readonly int $idEmployee,
        private readonly int $idShop,
        private readonly ?string $threadId = null,
        private readonly array $approvalDecisions = [],
    ) {
    }

    public function onEvent(string $event, object $source, mixed $data = null, ?string $branchId = null): void
    {
        if ($event !== 'tool-called' || !$data instanceof ToolCalled) {
            return;
        }

        try {
            $this->persist($data->tool);
        } catch (\Throwable $e) {
            // Audit logging is best-effort: it must never break the chat turn.
            error_log('ps_ask_ai ToolActionLogger insert failed: ' . $e->getMessage());
        }
    }

    private function persist(ToolInterface $tool): void
    {
        $callId = $tool->getCallId();
        $approval = $callId !== null
            ? ($this->approvalDecisions[$callId] ?? self::APPROVAL_AUTO)
            : self::APPROVAL_AUTO;

        $inputs = json_encode($tool->getInputs(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($inputs) && strlen($inputs) > self::MAX_INPUTS_BYTES) {
            $inputs = substr($inputs, 0, self::MAX_INPUTS_BYTES);
        }

        \Db::getInstance()->insert(self::TABLE, [
            'id_employee' => $this->idEmployee,
            'id_shop' => $this->idShop,
            'thread_id' => $this->threadId !== null ? pSQL($this->threadId) : null,
            'tool_call_id' => $callId !== null ? pSQL($callId) : null,
            'tool_name' => pSQL((string) $tool->getName()),
            'inputs' => is_string($inputs) ? pSQL($inputs, true) : null,
            'status' => pSQL($this->deriveStatus($tool, $approval)),
            'approval' => pSQL($approval),
            'executed_at' => date('Y-m-d H:i:s'),
        ], true);
    }

    /**
     * A rejected approval is exact when we hold the human decision (SSE resume
     * path); the "TOOL NOT EXECUTED" prefix is neuron-ai's ToolRejectionHandler
     * result and covers the in-process approval path (ask()). Errors are
     * detected from the result format our own ToolErrorHandler produces
     * (`{"status":"error",...}`) — a coupling internal to this module, so
     * stable, but revisit if the handler's payload changes.
     */
    private function deriveStatus(ToolInterface $tool, string $approval): string
    {
        if ($approval === self::APPROVAL_REJECTED || str_starts_with($tool->getResult(), 'TOOL NOT EXECUTED')) {
            return self::STATUS_REJECTED;
        }

        $decoded = json_decode($tool->getResult(), true);
        if (is_array($decoded) && ($decoded['status'] ?? null) === 'error') {
            return self::STATUS_ERROR;
        }

        return self::STATUS_SUCCESS;
    }
}
