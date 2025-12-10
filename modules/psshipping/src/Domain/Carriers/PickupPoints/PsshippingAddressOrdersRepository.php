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
use PrestaShop\Module\Psshipping\Entity\PsshippingAddressOrders;

/**
 * @extends ServiceEntityRepository<PsshippingAddressOrders>
 */
class PsshippingAddressOrdersRepository extends ServiceEntityRepository
{
    public function __construct(\Psshipping $module)
    {
        /** @var ManagerRegistry $registry */
        $registry = $module->getService('doctrine');
        parent::__construct($registry, PsshippingAddressOrders::class);
    }

    public function add(PsshippingAddressOrders $mapping, bool $flush = true): PsshippingAddressOrders
    {
        $em = $this->getEntityManager();
        $em->persist($mapping);

        if ($flush) {
            $em->flush();
        }

        return $mapping;
    }

    public function remove(PsshippingAddressOrders $mapping, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($mapping);

        if ($flush) {
            $em->flush();
        }
    }

    public function findOneByIdOrderAndShop(int $idOrder, int $idShop): ?PsshippingAddressOrders
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.idOrder = :order')
            ->andWhere('o.idShop = :shop')
            ->setParameters([
                'order' => $idOrder,
                'shop' => $idShop,
            ]);

        /** @var PsshippingAddressOrders|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }
}
