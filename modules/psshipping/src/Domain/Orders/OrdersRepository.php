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

namespace PrestaShop\Module\Psshipping\Domain\Orders;

use Context;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PrestaShop\PrestaShop\Adapter\Configuration;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class OrdersRepository
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
     * @param int $limit
     * @param int $offset
     * @param array<int,int> $carriersId
     *
     * @return array<int, array<string, string|int|bool>>
     */
    public function getOrders($limit, $offset, $carriersId)
    {
        $context = Context::getContext();
        $shopId = 0;
        $langId = 0;

        if (!empty($context) && !empty($context->language)) {
            $langId = $context->language->id;
        }

        if (!empty($context->shop)) {
            $shopId = (int) $context->shop->id;
        }

        if (count($carriersId) === 0) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('o.id_order, o.date_add, osl.name, o.reference, o.total_shipping_tax_incl, o.id_currency, os.color, ca.external_module_name')
            ->from($this->dbPrefix . 'orders', 'o')
            ->innerJoin('o', $this->dbPrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state AND osl.id_lang = :idLang')
            ->innerJoin('osl', $this->dbPrefix . 'order_state', 'os', 'osl.id_order_state = os.id_order_state')
            ->innerJoin('o', $this->dbPrefix . 'carrier', 'ca', 'o.id_carrier = ca.id_carrier')
            ->leftJoin('o', $this->dbPrefix . 'customer', 'c', 'c.id_customer = o.id_customer')
            ->where('o.id_carrier IN (:carriersId) AND o.id_shop = :idShop')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('o.date_add', 'DESC')
            ->setParameter('carriersId', $carriersId, Connection::PARAM_INT_ARRAY)
            ->setParameter('idShop', $shopId)
            ->setParameter('idLang', $langId);

        /** @var Statement $execute */
        $execute = $qb->execute();

        $result = $execute->fetchAll();

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @param array<int,int> $carriersId
     *
     * @return array<int, array<string, string|int|bool>>
     */
    public function getNbOrders($carriersId)
    {
        $context = Context::getContext();
        $langId = 0;
        $shopId = 0;

        if (!empty($context) && !empty($context->language)) {
            $langId = $context->language->id;
        }

        if (!empty($context->shop)) {
            $shopId = (int) $context->shop->id;
        }

        if (count($carriersId) === 0) {
            return [];
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->dbPrefix . 'orders', 'o')
            ->innerJoin('o', $this->dbPrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state AND osl.id_lang = :idLang')
            ->innerJoin('osl', $this->dbPrefix . 'order_state', 'os', 'osl.id_order_state = os.id_order_state')
            ->innerJoin('o', $this->dbPrefix . 'carrier', 'ca', 'o.id_carrier = ca.id_carrier')
            ->leftJoin('o', $this->dbPrefix . 'customer', 'c', 'c.id_customer = o.id_customer')
            ->where('o.id_carrier IN (:carriersId) AND o.id_shop = :idShop')
            ->orderBy('o.date_add', 'DESC')
            ->setParameter('carriersId', $carriersId, Connection::PARAM_INT_ARRAY)
            ->setParameter('idShop', $shopId)
            ->setParameter('idLang', $langId);

        /** @var Statement $execute */
        $execute = $qb->execute();

        $result = $execute->fetchAll();

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function saveTrackingNumber(int $orderId, string $trackingNumber): bool
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update($this->dbPrefix . 'order_carrier', 'oc')
            ->set('oc.tracking_number', ':trackingNumber')
            ->where('oc.id_order = :orderId')
            ->setParameter('trackingNumber', $trackingNumber)
            ->setParameter('orderId', $orderId)
            ->execute();

        return true;
    }

    /**
     * getLastTrackingNumberByCarrierId
     *
     * @param array<int,int> $carriersId
     *
     * @return array<int, array<string, bool|int|string>>
     */
    public function getLastTrackingNumberByCarrierId($carriersId)
    {
        $context = Context::getContext();
        $shopId = 0;

        if (!empty($context) && !empty($context->shop)) {
            $shopId = (int) $context->shop->id;
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('oc.tracking_number')
            ->from($this->dbPrefix . 'orders', 'o')
            ->innerJoin('o', $this->dbPrefix . 'order_carrier', 'oc', 'oc.id_order = o.id_order')
            ->where('oc.tracking_number != "" AND o.id_shop = :idShop')
            ->andWhere('oc.id_carrier IN (:carriersId)')
            ->orderBy('o.date_upd', 'DESC')
            ->setMaxResults(1)
            ->groupBy('oc.id_order')
            ->setParameter('carriersId', $carriersId, Connection::PARAM_INT_ARRAY)
            ->setParameter('idShop', $shopId);

        /** @var Statement $execute */
        $execute = $qb->execute();

        $result = $execute->fetchAll();

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    public function getOrdersStatus()
    {
        $context = Context::getContext();
        $lang = 0;
        $result = [];

        if (!empty($context) && !empty($context->language)) {
            $lang = $context->language->id;
        }

        foreach (\OrderState::getOrderStates($lang) as $state) {
            $result[] = [
                'name' => $state['name'],
                'id' => $state['id_order_state'],
            ];
        }

        return $result;
    }

    public function updateOrder(int $orderId, string $status): void
    {
        $configuration = new Configuration();
        $context = Context::getContext();
        $mapping = '';

        if (is_string($configuration->get('PS_SHIPPING_ORDER_STATUS_MAPPING', '')) && !empty($configuration->get('PS_SHIPPING_ORDER_STATUS_MAPPING', ''))) {
            $mapping = json_decode($configuration->get('PS_SHIPPING_ORDER_STATUS_MAPPING', ''));
        }

        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        if (empty($context->language)) {
            return;
        }

        $filterStatus = array_filter($mapping, function ($value) use ($status) {
            return $value->mbeStatus === $status;
        });

        if (empty($filterStatus)) {
            return;
        }

        $statusPs = array_values($filterStatus)[0];

        if (empty($statusPs->statusMapped)) {
            return;
        }

        $order = new \OrderCore($orderId);
        $order->setCurrentState((int) $statusPs->statusMapped);
        $order->save();
    }
}
