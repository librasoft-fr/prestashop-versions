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
 * Schema-only entity matching the table neuron-ai's SQLChatHistory writes to via raw PDO.
 * Doctrine is aware of the table; runtime read/write goes through neuron-ai.
 *
 * @ORM\Table()
 *
 * @ORM\Entity(readOnly=true)
 */
class PsAskAiChatHistory
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="bigint", options={"unsigned": true})
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @phpstan-ignore property.unusedType
     */
    private ?string $id = null;

    /**
     * @ORM\Column(name="thread_id", type="string", length=255, unique=true)
     *
     * @phpstan-ignore property.onlyRead
     */
    private string $threadId;

    /**
     * @ORM\Column(name="messages", type="text", length=4294967295)
     */
    private string $messages = '[]';

    /**
     * @ORM\Column(name="created_at", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     *
     * @phpstan-ignore property.onlyRead
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     *
     * @phpstan-ignore property.onlyRead
     */
    private \DateTimeInterface $updatedAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getThreadId(): string
    {
        return $this->threadId;
    }

    public function getMessages(): string
    {
        return $this->messages;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}
