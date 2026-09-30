<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 *
 * @ORM\Entity(repositoryClass="PrestaShop\Module\PsAskAi\Repository\PsAskAiConversationRepository")
 *
 * @ORM\HasLifecycleCallbacks
 */
class PsAskAiConversation
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_conversation", type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @phpstan-ignore property.unusedType
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="thread_id", type="string", length=64, unique=true)
     */
    private string $threadId;

    /**
     * @ORM\Column(name="id_employee", type="integer")
     */
    private int $idEmployee;

    /**
     * @ORM\Column(name="id_shop", type="integer")
     */
    private int $idShop;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private ?string $title = null;

    /**
     * Fork tree (PE-1721). A conversation forks when the merchant edits an
     * earlier message and resends: a child thread is created holding a copy of
     * the messages before the edited one, and the edited prompt runs there.
     * The parent thread is left intact.
     *
     * parentThreadId: the thread this one forked from (null on a root).
     * rootThreadId: the tree root's thread id (a root points at itself), so a
     *   whole tree can be grouped without walking the parent chain.
     * branchPoint: index (among USER messages) of the edited message in the
     *   parent — the divergence point, used by the variation navigation UI.
     *
     * @ORM\Column(name="parent_thread_id", type="string", length=64, nullable=true)
     */
    private ?string $parentThreadId = null;

    /**
     * @ORM\Column(name="root_thread_id", type="string", length=64)
     */
    private string $rootThreadId;

    /**
     * @ORM\Column(name="branch_point", type="integer", nullable=true)
     */
    private ?int $branchPoint = null;

    /**
     * Active leaf pointer (PE-1721 phase 2). Set on the tree ROOT only: the
     * thread id of the branch the merchant is currently on. A root opened from
     * recents resolves to this branch, so switching to an older variation and
     * reloading sticks (instead of always snapping to the newest-updated leaf).
     * Null on forks and on roots never navigated — the resolver then falls back
     * to the newest-in-tree branch.
     *
     * @ORM\Column(name="active_leaf_thread_id", type="string", length=64, nullable=true)
     */
    private ?string $activeLeafThreadId = null;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private \DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $archivedAt = null;

    public function __construct(
        string $threadId,
        int $idEmployee,
        int $idShop,
        ?string $title = null,
        ?string $parentThreadId = null,
        ?string $rootThreadId = null,
        ?int $branchPoint = null,
    ) {
        $this->threadId = $threadId;
        $this->idEmployee = $idEmployee;
        $this->idShop = $idShop;
        $this->title = $title;
        $this->parentThreadId = $parentThreadId;
        // A root is its own tree root; forks inherit the root's id.
        $this->rootThreadId = $rootThreadId ?? $threadId;
        $this->branchPoint = $branchPoint;
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * @ORM\PreUpdate
     */
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThreadId(): string
    {
        return $this->threadId;
    }

    public function getIdEmployee(): int
    {
        return $this->idEmployee;
    }

    public function getIdShop(): int
    {
        return $this->idShop;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getParentThreadId(): ?string
    {
        return $this->parentThreadId;
    }

    public function getRootThreadId(): string
    {
        return $this->rootThreadId;
    }

    public function isRoot(): bool
    {
        return $this->parentThreadId === null;
    }

    public function getBranchPoint(): ?int
    {
        return $this->branchPoint;
    }

    public function getActiveLeafThreadId(): ?string
    {
        return $this->activeLeafThreadId;
    }

    public function setActiveLeafThreadId(?string $activeLeafThreadId): self
    {
        $this->activeLeafThreadId = $activeLeafThreadId;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }

    public function archive(): self
    {
        $this->archivedAt = new \DateTimeImmutable();

        return $this;
    }
}
