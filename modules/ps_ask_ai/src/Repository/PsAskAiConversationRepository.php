<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PrestaShop\Module\PsAskAi\Entity\PsAskAiConversation;

/**
 * @extends ServiceEntityRepository<PsAskAiConversation>
 *
 * @method PsAskAiConversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PsAskAiConversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PsAskAiConversation[] findAll()
 * @method PsAskAiConversation[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PsAskAiConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PsAskAiConversation::class);
    }

    public function findOneByThreadId(string $threadId): ?PsAskAiConversation
    {
        return $this->findOneBy(['threadId' => $threadId]);
    }

    /**
     * Active ROOT conversations only. Forks (parentThreadId set) are variations
     * of a root and never surface as their own entry in the recents list — they
     * are reached through the in-drawer variation navigation instead (PE-1721).
     *
     * @return PsAskAiConversation[]
     */
    public function findActiveByEmployee(int $idEmployee, int $idShop): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.idEmployee = :idEmployee')
            ->andWhere('c.idShop = :idShop')
            ->andWhere('c.archivedAt IS NULL')
            ->andWhere('c.parentThreadId IS NULL')
            ->setParameter('idEmployee', $idEmployee)
            ->setParameter('idShop', $idShop)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * The most recently updated (non-archived) conversation in a fork tree,
     * i.e. the active leaf of the tree rooted at $rootThreadId. Used to resolve
     * a root the merchant opened to the branch they were last on. Returns null
     * only when the whole tree is archived / missing.
     */
    public function findNewestInTree(string $rootThreadId): ?PsAskAiConversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.rootThreadId = :root')
            ->andWhere('c.archivedAt IS NULL')
            ->setParameter('root', $rootThreadId)
            ->orderBy('c.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * All conversations (any archive state) belonging to a fork tree. Used to
     * archive the whole tree at once when a root is deleted.
     *
     * @return PsAskAiConversation[]
     */
    public function findByRoot(string $rootThreadId): array
    {
        return $this->findBy(['rootThreadId' => $rootThreadId]);
    }

    public function save(PsAskAiConversation $conversation, bool $flush = true): void
    {
        $this->getEntityManager()->persist($conversation);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
