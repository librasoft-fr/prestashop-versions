<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Chat;

use NeuronAI\Chat\History\SQLChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ContentBlocks\ReasoningContent;
use NeuronAI\Chat\Messages\ContentBlocks\TextContent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolResultMessage;
use NeuronAI\Chat\Messages\UserMessage;

/**
 * SQLChatHistory variant that survives PHP's serialize() round-trip used by
 * the workflow interrupt persistence. The default class holds a live PDO,
 * which serialize() refuses. We drop the PDO on serialize and rebuild it
 * from PrestaShop's _DB_* constants on unserialize, then re-hydrate the
 * in-memory history from the database.
 *
 * It also heals histories left inconsistent by a previous turn that failed or
 * was abandoned before the assistant's final message was saved. neuron-ai
 * persists incrementally: StreamingNode/ToolNode write the user (and tool-call)
 * messages to the DB BEFORE the provider/tool call that follows them. If that
 * call throws, times out, or the SSE stream is cut short (tab closed, token
 * expired, provider 5xx), the assistant reply is never appended — the thread
 * keeps a dangling tail (an orphan UserMessage, or a tool call/result with no
 * closing assistant message).
 *
 * On the next turn neuron-ai appends a new UserMessage and HistoryTrimmer
 * ::validateAlternation() walks the sequence; the dangling tail then makes a
 * UserMessage land where an assistant message is expected, throwing "Invalid
 * message sequence at position N: expected role assistant, got user". Because
 * that check runs at addMessage() time — before the new message is persisted —
 * the corruption never reaches the DB mid-history; it is always a single
 * incomplete trailing turn. Dropping that tail at load is therefore enough to
 * make the sequence valid again. The dropped orphan prompt is captured so the
 * caller can fold it into the current turn rather than lose it.
 */
class SerializableSqlChatHistory extends SQLChatHistory
{
    /**
     * Metadata key flagging a user message whose text is itself the product of
     * an earlier orphan merge. Set by NeuronAiService when it merges, read here
     * on the next load so an already-merged orphan is dropped rather than
     * merged again (bounds prompt growth to two messages). Invisible to the LLM
     * (metadata, not message text); survives persistence via deserializeMeta().
     */
    public const MERGED_META_KEY = 'ps_ask_ai_merged';

    /**
     * Metadata key carrying the model key (MODELS catalog id) the turn was
     * sent with. Set by NeuronAiService on each turn's user message so the
     * history endpoint can show which model produced each exchange — and
     * where the model changed mid-thread. Invisible to the LLM (metadata,
     * not message text); survives persistence via deserializeMeta().
     */
    public const MODEL_META_KEY = 'ps_ask_ai_model';

    /**
     * The orphan user message dropped from the tail, if any — the prompt of a
     * turn that never got an answer, plus whether it was itself a merged
     * prompt. Consumed once by the caller (NeuronAiService) to fold it into the
     * current turn. Null when the thread ended cleanly or on incomplete tool
     * plumbing (dropped, never folded).
     *
     * @var array{text: string, merged: bool}|null
     */
    private ?array $pendingOrphan = null;

    /**
     * The provider getMessages() should sanitize the replayed history FOR, or
     * null to replay it verbatim. Reasoning is not provider-portable, so on a
     * cross-provider replay we drop the foreign provider's reasoning blocks:
     *
     *  - Anthropic reasoning carries its signature in ReasoningContent::$id (a
     *    non-empty string). Replaying a foreign (null-signature) reasoning block
     *    to Anthropic makes its MessageMapper emit {type: thinking, signature:
     *    null}, rejected with "messages.N.content.0.thinking.signature.str:
     *    Input should be a valid string".
     *  - Gemini reasoning has a null $id — its thought_signature lives in
     *    message/block metadata — and MUST be kept for same-provider replay:
     *    dropping it (or rebuilding the ToolCallMessage, which loses the
     *    metadata) strips the signature Gemini requires on functionCall parts
     *    ("Function call is missing a thought_signature in functionCall parts").
     *
     * So we keep only reasoning that ORIGINATED from the target provider;
     * same-provider replay returns messages by identity (never rebuilt, so
     * their metadata survives). See sanitizeReasoningForProvider().
     *
     * Set on the new-turn path (__construct). Stays null on the resume path
     * (__unserialize, which bypasses __construct): resume is same-provider by
     * design and an interrupted turn needs its reasoning/signature preserved
     * for the tool_use it is resuming.
     */
    private ?string $sanitizeForProvider = null;

    public function __construct(
        string $thread_id,
        \PDO $pdo,
        string $table = 'chat_history',
        int $contextWindow = 50000,
        ?string $sanitizeForProvider = null,
    ) {
        parent::__construct($thread_id, $pdo, $table, $contextWindow);

        // Only heal on a fresh open of the thread for a new turn. The resume
        // path rebuilds this object via __unserialize(), which calls
        // SQLChatHistory::__construct() directly (bypassing this constructor),
        // so a paused workflow's trailing tool-call message is preserved —
        // dropping it there would discard the very call being approved.
        $this->captureOrphanPrompt();

        if ($this->dropIncompleteTrailingTurn() > 0) {
            // Persist the repair so the thread is healed once and for all,
            // not re-trimmed on every load.
            $this->setMessages($this->history);
        }

        // A fresh open for a new turn: the replayed history may cross providers
        // (operator switched the active provider, then continued this thread),
        // so getMessages() sanitizes foreign reasoning FOR this target provider.
        // Persisted rows keep the full blocks (see below).
        $this->sanitizeForProvider = $sanitizeForProvider;
    }

    /**
     * Returns (once) the dropped orphan — its text and whether it was already a
     * merged prompt — so the caller can fold it into the current turn instead
     * of losing it. Nulls itself after the read.
     *
     * @return array{text: string, merged: bool}|null
     */
    public function consumePendingOrphan(): ?array
    {
        $orphan = $this->pendingOrphan;
        $this->pendingOrphan = null;

        return $orphan;
    }

    /**
     * The agent builds every provider request from getMessages() (StreamingNode
     * / ChatNode). On the new-turn path we hand back a COPY sanitized for the
     * target provider so a thread survives a provider switch, while
     * $this->history — which addMessage()/finalizeStoppedTurn() re-persist
     * wholesale — keeps the full blocks. On the resume path
     * ($sanitizeForProvider null) the raw history is returned verbatim so the
     * interrupted turn keeps its reasoning/signature. See $sanitizeForProvider.
     *
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->sanitizeForProvider === null
            ? $this->history
            : self::sanitizeReasoningForProvider($this->history, $this->sanitizeForProvider);
    }

    /**
     * Return a copy of the history keeping only reasoning that originated from
     * $targetProvider, so a replay across providers can't carry a foreign
     * provider's reasoning (Anthropic rejects a null signature; Gemini rejects
     * a functionCall whose thought_signature is gone). Origin is read off the
     * block: Anthropic reasoning has a non-empty ReasoningContent::$id (the
     * signature); Gemini's is null (signature is in metadata).
     *
     * Pure and static so it can be unit-tested without a database, and it never
     * mutates the input Messages (they are the persisted originals): a message
     * whose reasoning all survives is returned by identity — crucially, a
     * same-provider replay never rebuilds, so message metadata like Gemini's
     * thought_signature is preserved. Only a message that actually loses a
     * block is rebuilt (subtype + tools + usage kept); such a rebuild only
     * happens on a cross-provider replay, where the dropped message's own
     * provider metadata is foreign to the target and irrelevant to its request.
     *
     * @param Message[] $messages
     *
     * @return Message[]
     */
    public static function sanitizeReasoningForProvider(array $messages, string $targetProvider): array
    {
        return array_map(static function (Message $message) use ($targetProvider): Message {
            $blocks = $message->getContentBlocks();
            $kept = array_values(array_filter(
                $blocks,
                static function ($block) use ($targetProvider): bool {
                    if (!$block instanceof ReasoningContent) {
                        return true;
                    }
                    $isAnthropicOrigin = $block->id !== null && $block->id !== '';

                    // anthropic keeps its own signed reasoning; gemini keeps its
                    // own (null-id) reasoning; openai/mistral consume neither.
                    return match ($targetProvider) {
                        'anthropic' => $isAnthropicOrigin,
                        'gemini' => !$isAnthropicOrigin,
                        default => false,
                    };
                }
            ));

            if (count($kept) === count($blocks)) {
                // Nothing dropped — leave the persisted original untouched.
                return $message;
            }

            // Reconstruct rather than mutate. Non-reasoning content (the text
            // answer) survives; the ` ` fallback guards the rare reasoning-only
            // turn so we never emit empty content and break provider alternation.
            $content = $kept !== [] ? $kept : [new TextContent(' ')];
            $copy = $message instanceof ToolCallMessage
                ? new ToolCallMessage($content, $message->getTools())
                : new AssistantMessage($content);

            if ($message->getUsage() !== null) {
                $copy->setUsage($message->getUsage());
            }

            return $copy;
        }, $messages);
    }

    /**
     * Overwrite this thread's history with an explicit message list (PE-1721
     * fork prefix copy). The thread row must already exist — the constructor's
     * load() inserts an empty one. setMessages() serializes $this->history
     * wholesale (ignoring its argument), so we set the property first, the same
     * pattern the constructor's healing uses.
     *
     * @param Message[] $messages
     */
    public function replaceHistory(array $messages): void
    {
        $this->history = array_values($messages);
        $this->setMessages($this->history);
    }

    /**
     * The prefix of $messages that precedes the ($ordinal)-th genuine user
     * prompt (0-based) — everything before the message the merchant edited.
     * ToolResultMessage subclasses UserMessage but is internal plumbing, so it
     * is not counted. Ordinal 0 (or less) yields an empty prefix; an ordinal
     * past the last user message yields the whole history.
     *
     * Pure and static so the fork slice can be unit-tested without a database.
     *
     * @param Message[] $messages
     *
     * @return Message[]
     */
    public static function prefixBeforeUserOrdinal(array $messages, int $ordinal): array
    {
        if ($ordinal <= 0) {
            return [];
        }

        $seen = 0;
        $messages = array_values($messages);
        foreach ($messages as $i => $message) {
            if ($message instanceof UserMessage && !$message instanceof ToolResultMessage) {
                if ($seen === $ordinal) {
                    return array_slice($messages, 0, $i);
                }
                ++$seen;
            }
        }

        return $messages;
    }

    /**
     * Remember the trailing message when it is a plain user prompt left
     * unanswered (NOT a ToolResultMessage, which is internal plumbing, and not
     * a dangling tool call — those are dropped without folding). Must run
     * before dropIncompleteTrailingTurn() removes it.
     */
    private function captureOrphanPrompt(): void
    {
        if ($this->history === []) {
            return;
        }

        $last = $this->history[array_key_last($this->history)];
        if (!$last instanceof UserMessage || $last instanceof ToolResultMessage) {
            return;
        }

        $text = $last->getContent();
        if ($text === null) {
            return;
        }

        $this->pendingOrphan = [
            'text' => $text,
            'merged' => $last->getMetadata(self::MERGED_META_KEY) !== null,
        ];
    }

    /**
     * Close out a turn the user explicitly stopped mid-stream, then persist.
     *
     * neuron-ai appends the assistant reply only once the stream completes; a
     * stopped turn breaks out of that loop, so the DB still ends on the user
     * message (plus any tool plumbing) with no answer. Left as-is, the next
     * load would treat it as an orphan — dropped AND folded into the following
     * prompt. We pre-empt that here:
     *
     *  - $partialText !== '' and the tail can take an assistant message (a
     *    UserMessage — ToolResultMessage is one too): append the partial answer
     *    as a completed AssistantMessage so the turn ends cleanly and survives
     *    reload exactly as it appeared when stopped.
     *  - otherwise (nothing received yet, or a dangling tool call we can't
     *    legally follow with an assistant message): drop the incomplete
     *    trailing turn, leaving no orphan to fold.
     *
     * setMessages() is used directly (not addMessage) so the manual sequence
     * skips HistoryTrimmer::validateAlternation() — the cases above already
     * guarantee a valid alternation.
     */
    public function finalizeStoppedTurn(string $partialText): void
    {
        $this->setMessages(self::withStoppedTurnFinalized($this->history, $partialText));
    }

    /**
     * Pure counterpart of finalizeStoppedTurn() (static + DB-free for testing).
     *
     * @param Message[] $messages
     *
     * @return Message[]
     */
    public static function withStoppedTurnFinalized(array $messages, string $partialText): array
    {
        $last = $messages === [] ? null : $messages[array_key_last($messages)];

        // An AssistantMessage may only follow a UserMessage (ToolResultMessage
        // is one); with partial text and such a tail, keep the partial answer.
        if ($partialText !== '' && $last instanceof UserMessage) {
            $messages[] = new AssistantMessage($partialText);

            return $messages;
        }

        // Nothing received yet, or a tail (e.g. a dangling tool call) that can't
        // legally precede an assistant message: drop the incomplete turn.
        return self::withoutIncompleteTrailingTurn($messages);
    }

    /**
     * @return int number of messages dropped from the in-memory history
     */
    private function dropIncompleteTrailingTurn(): int
    {
        $before = count($this->history);
        $this->history = self::withoutIncompleteTrailingTurn($this->history);

        return $before - count($this->history);
    }

    /**
     * Pop trailing messages until the history ends on a completed assistant
     * turn (a plain AssistantMessage — NOT a ToolCallMessage, which is an
     * assistant message still awaiting its tool result) or becomes empty.
     * A history ending that way always alternates correctly once the next
     * UserMessage is appended.
     *
     * Pure and static so it can be unit-tested without a database.
     *
     * @param Message[] $messages
     *
     * @return Message[]
     */
    public static function withoutIncompleteTrailingTurn(array $messages): array
    {
        while ($messages !== []) {
            $last = $messages[array_key_last($messages)];
            if ($last instanceof AssistantMessage && !$last instanceof ToolCallMessage) {
                break;
            }
            array_pop($messages);
        }

        return $messages;
    }

    /**
     * @return array<string, string>
     */
    public function __serialize(): array
    {
        return [
            'thread_id' => $this->thread_id,
            'table' => $this->table,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        // Resume path: replay verbatim. Resume is same-provider by design and
        // the interrupted turn needs its reasoning/signature preserved for the
        // tool_use it is resuming.
        $this->sanitizeForProvider = null;

        parent::__construct(
            thread_id: (string) $data['thread_id'],
            pdo: $this->buildPdo(),
            table: (string) $data['table'],
        );
    }

    private function buildPdo(): \PDO
    {
        return new \PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', _DB_SERVER_, _DB_NAME_),
            _DB_USER_,
            _DB_PASSWD_,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }
}
