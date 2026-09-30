<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use PrestaShop\Module\PsAskAi\Entity\PsAskAiConversation;
use PrestaShop\Module\PsAskAi\Repository\PsAskAiConversationRepository;

class ConversationService
{
    public function __construct(private readonly PsAskAiConversationRepository $repository)
    {
    }

    public function create(int $idEmployee, int $idShop, ?string $title = null): PsAskAiConversation
    {
        $conversation = new PsAskAiConversation(
            threadId: $this->generateThreadId(),
            idEmployee: $idEmployee,
            idShop: $idShop,
            title: $title
        );

        $this->repository->save($conversation);

        return $conversation;
    }

    /**
     * Fork a conversation (PE-1721): create a child thread that will hold a copy
     * of the source's messages up to $branchPoint (the edited user message),
     * with the edited turn appended afterwards. The child belongs to the same
     * employee/shop and the same tree root as the source, so it never appears as
     * its own recents entry. Copying the message blob itself is the caller's job
     * (NeuronAiService::copyHistoryPrefix) — this only creates the metadata row.
     */
    public function fork(PsAskAiConversation $source, int $branchPoint): PsAskAiConversation
    {
        $fork = new PsAskAiConversation(
            threadId: $this->generateThreadId(),
            idEmployee: $source->getIdEmployee(),
            idShop: $source->getIdShop(),
            title: null,
            parentThreadId: $source->getThreadId(),
            rootThreadId: $source->getRootThreadId(),
            branchPoint: $branchPoint,
        );

        $this->repository->save($fork);

        return $fork;
    }

    /**
     * The branch the merchant is currently on for a tree. Prefers the explicit
     * active-leaf pointer stored on the root (set when a fork is created or a
     * variation is navigated), so switching to an older variation sticks across
     * reload. Falls back to the newest-updated branch when the pointer is unset
     * (pre-phase-2 trees, roots never navigated) or stale (points at an archived
     * or missing thread), and finally to the conversation itself.
     */
    public function getActiveLeaf(PsAskAiConversation $conversation): PsAskAiConversation
    {
        $root = $this->resolveRoot($conversation);

        $pointer = $root?->getActiveLeafThreadId();
        if ($pointer !== null) {
            $leaf = $this->repository->findOneByThreadId($pointer);
            if ($leaf !== null
                && $leaf->getArchivedAt() === null
                && $leaf->getRootThreadId() === $conversation->getRootThreadId()
            ) {
                return $leaf;
            }
        }

        return $this->repository->findNewestInTree($conversation->getRootThreadId()) ?? $conversation;
    }

    /**
     * Point the tree root at $leaf as the branch the merchant is now on. Called
     * when a fork is created (the fork becomes active) and when the merchant
     * navigates to a sibling variation, so the choice survives a reload.
     */
    public function setActiveLeaf(PsAskAiConversation $leaf): void
    {
        $root = $this->resolveRoot($leaf);
        if ($root === null) {
            return;
        }
        $root->setActiveLeafThreadId($leaf->getThreadId());
        $this->repository->save($root);
    }

    private function resolveRoot(PsAskAiConversation $conversation): ?PsAskAiConversation
    {
        if ($conversation->isRoot()) {
            return $conversation;
        }

        return $this->repository->findOneByThreadId($conversation->getRootThreadId());
    }

    public function getByThreadId(string $threadId): ?PsAskAiConversation
    {
        return $this->repository->findOneByThreadId($threadId);
    }

    /**
     * Variation groups for the branch currently displayed (PE-1721 phase 2).
     * Returns a map keyed by USER-message ordinal → the sibling forks that
     * diverge at that ordinal, so the drawer can render prev/next arrows on the
     * matching user bubble:
     *
     *   [ ordinal => ['index' => int, 'count' => int, 'threads' => string[]] ]
     *
     * The group at ordinal N is resolved relative to $displayed:
     *  - if $displayed forked at N (it is the edited variation), the "original"
     *    is its parent and the members are the parent + all its forks at N;
     *  - otherwise $displayed is the original at N and the members are itself +
     *    its own forks at N (edits made while viewing this branch).
     * `index` is $displayed's position in `threads` (the currently-shown one).
     *
     * Members are ordered original-first, then forks by ascending id (creation
     * order) for a stable carousel. Only ordinals with >1 member are returned.
     *
     * Limitation (phase 2): groups are resolved relative to the displayed
     * branch, not flattened across the whole tree, so a variation created at an
     * ordinal that lies inside another branch's copied prefix is only visible
     * from the branch it was made on. Deeper flattening is future work.
     *
     * @return array<int, array{index: int, count: int, threads: string[]}>
     */
    public function computeVariations(PsAskAiConversation $displayed): array
    {
        return self::variationsForTree(
            $this->repository->findByRoot($displayed->getRootThreadId()),
            $displayed,
        );
    }

    /**
     * Pure variation computation (testable without a repository): see
     * computeVariations() for the semantics.
     *
     * @param PsAskAiConversation[] $tree all nodes sharing $displayed's root
     *
     * @return array<int, array{index: int, count: int, threads: string[]}>
     */
    public static function variationsForTree(array $tree, PsAskAiConversation $displayed): array
    {
        // Index by thread id (to walk the ancestor chain) and collect the forks
        // that diverge at each (parent, branchPoint), ordered by id (creation).
        $byThread = [];
        $forksByParentBranch = []; // parentThreadId => branchPoint => [threadId]
        $branchPoints = [];        // set of ordinals any active fork diverges at
        $sorted = $tree;
        usort($sorted, static fn (PsAskAiConversation $a, PsAskAiConversation $b): int => ($a->getId() ?? 0) <=> ($b->getId() ?? 0));
        foreach ($sorted as $node) {
            $byThread[$node->getThreadId()] = $node;
            $parent = $node->getParentThreadId();
            $bp = $node->getBranchPoint();
            if ($parent === null || $bp === null || $node->getArchivedAt() !== null) {
                continue;
            }
            $forksByParentBranch[$parent][$bp][] = $node->getThreadId();
            $branchPoints[$bp] = true;
        }

        // Walk $displayed's ancestor chain (deepest → root) and record, for each
        // ordinal, whether the displayed lineage itself forked there. This lets a
        // variation group at ordinal N be resolved against the ancestor that owns
        // N in $displayed's prefix — not just $displayed or its direct parent —
        // so a branch sees every sibling that shares its prefix through N (PE-1721
        // phase 3: flatten variation groups across the whole tree).
        $chain = [];              // [$displayed, parent, …, root]
        $chainForkAtOrdinal = []; // ordinal N => chain node whose lineage forked at N
        $cursor = $displayed;
        $guard = 0;
        while ($cursor !== null && $guard++ < 1000) {
            $chain[] = $cursor;
            $parent = $cursor->getParentThreadId();
            $bp = $cursor->getBranchPoint();
            if ($parent !== null && $bp !== null) {
                $chainForkAtOrdinal[$bp] = $cursor;
            }
            $cursor = $parent !== null ? ($byThread[$parent] ?? null) : null;
        }

        $result = [];
        foreach (array_keys($branchPoints) as $n) {
            if (isset($chainForkAtOrdinal[$n])) {
                // $displayed's own lineage forked at N → back-nav: the group is
                // that fork's parent (the original) plus all its forks at N, and
                // $displayed is (or descends through) the fork.
                $forkNode = $chainForkAtOrdinal[$n];
                $anchor = $forkNode->getParentThreadId();
                $dMember = $forkNode->getThreadId();
            } else {
                // $displayed took an original at N → the anchor is the deepest
                // chain node whose own turns cover N (branchPoint ≤ N, or root):
                // the node that authored $displayed's message N, from which any
                // prefix-compatible sibling forks branch off.
                $anchor = null;
                foreach ($chain as $node) {
                    $bp = $node->getBranchPoint();
                    if ($node->getParentThreadId() === null || ($bp !== null && $bp <= $n)) {
                        $anchor = $node->getThreadId();
                        break;
                    }
                }
                $dMember = $anchor;
            }

            if ($anchor === null) {
                continue;
            }
            $members = array_merge([$anchor], $forksByParentBranch[$anchor][$n] ?? []);
            if (count($members) <= 1) {
                continue;
            }
            $idx = array_search($dMember, $members, true);
            $result[$n] = [
                'index' => $idx === false ? 0 : (int) $idx,
                'count' => count($members),
                'threads' => $members,
            ];
        }

        ksort($result);

        return $result;
    }

    /**
     * @return PsAskAiConversation[]
     */
    public function listForEmployee(int $idEmployee, int $idShop): array
    {
        return $this->repository->findActiveByEmployee($idEmployee, $idShop);
    }

    public function touch(PsAskAiConversation $conversation): void
    {
        $conversation->touch();
        $this->repository->save($conversation);
    }

    /**
     * Archive the whole fork tree the conversation belongs to, so deleting a
     * root also removes its hidden variations (which are never listed on their
     * own and would otherwise linger). Idempotent on already-archived rows.
     */
    public function archive(PsAskAiConversation $conversation): void
    {
        foreach ($this->repository->findByRoot($conversation->getRootThreadId()) as $node) {
            if ($node->getArchivedAt() === null) {
                $node->archive();
                $this->repository->save($node, false);
            }
        }
        $this->repository->flush();
    }

    public function setTitle(PsAskAiConversation $conversation, string $title): void
    {
        $conversation->setTitle($title);
        $this->repository->save($conversation);
    }

    private function generateThreadId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
