<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Psshipping\Domain\Carriers\PickupPoints;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PrestaShop\Module\Psshipping\Entity\PsshippingAddress;

/**
 * @extends ServiceEntityRepository<PsshippingAddress>
 */
class PsshippingAddressRepository extends ServiceEntityRepository
{
    public function __construct(\Psshipping $module)
    {
        /** @var ManagerRegistry $registry */
        $registry = $module->getService('doctrine');
        parent::__construct($registry, PsshippingAddress::class);
    }

    public function add(PsshippingAddress $address, bool $flush = true): PsshippingAddress
    {
        $existing = $this->findOneByPickupPointId($address->getPickupPointId());
        if ($existing) {
            return $existing;
        }

        $entityManager = $this->getEntityManager();
        $entityManager->persist($address);

        if ($flush) {
            $entityManager->flush();
        }

        return $address;
    }

    public function findOneById(int $id): ?PsshippingAddress
    {
        return $this->find($id);
    }

    public function findOneByPickupPointId(int $pickupPointId): ?PsshippingAddress
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.pickupPointId = :ppid')
            ->setParameter('ppid', $pickupPointId);

        /** @var PsshippingAddress|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }
}
