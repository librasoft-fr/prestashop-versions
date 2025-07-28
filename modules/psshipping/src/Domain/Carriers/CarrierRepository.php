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

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Domain\Carriers;

use Context;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class CarrierRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var Psshipping
     */
    private $module;

    /**
     * @param Psshipping $module
     */
    public function __construct(Psshipping $module)
    {
        $this->module = $module;
        $this->dbPrefix = _DB_PREFIX_;
        /** @var Connection $connection */
        $connection = $this->module->getService('doctrine.dbal.default_connection');
        $this->connection = $connection;
    }

    /**
     * @param bool $active
     *
     * @return array
     */
    public function getCarriers($active = false)
    {
        $context = Context::getContext();

        if (!empty($context->language) && !empty($context->language->id)) {
            $qb = $this->connection->createQueryBuilder();

            $qb->select('c.*, cl.delay');
            $qb->from($this->dbPrefix . 'carrier', 'c');
            $qb->leftJoin('c', $this->dbPrefix . 'carrier_lang', 'cl', 'c.id_carrier = cl.id_carrier');
            $qb->leftJoin('c', $this->dbPrefix . 'carrier_zone', 'cz', 'c.id_carrier = c.id_carrier');
            if ($active) {
                $qb->where('c.active = 1');
            }
            $qb->where('c.is_module = 0');
            $qb->andWhere('c.external_module_name IN (:names)');
            $qb->groupBy('c.id_carrier');
            $qb->orderBy('c.id_carrier', 'DESC');
            $qb->setParameter('id_lang', (int) $context->language->id);
            $qb->setParameter('names', CarrierService::CARRIERS_CREATED_BY_MODULE, Connection::PARAM_STR_ARRAY);

            /** @var Statement $execute */
            $execute = $qb->execute();

            return $execute->fetchAll();
        }

        return [];
    }
}
