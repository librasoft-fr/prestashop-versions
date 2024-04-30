<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

namespace KlaviyoPs\Classes\Webservice\QueryServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Db;
use Order;
use KlaviyoPs\Classes\BusinessLogicServices\OrderPayloadService;
use WebserviceException;

class OrderQueryService extends QueryServiceInterface
{
    /**
     * @inheritDoc
     */
    protected function getTableName()
    {
        return 'orders';
    }

    /**
     * Retrieve and build single order by its ID.
     *
     * @param $orderId
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws WebserviceException
     */
    public function getObjectById($orderId)
    {
        $order = new Order($orderId);
        // New Order object can be created without that particular orderId existing. All attributes of the
        // Order return null if the order doesn't exist. Check against id attribute to ensure order exists.
        if (!$order->id) {
            throw new WebserviceException(
                'Invalid order ID, order does not exist',
                [self::DEFAULT_ERROR_CODE, 404]
            );
        }

        return OrderPayloadService::buildPayload($order);
    }

    /**
     * Fetch order IDs for a given time range.
     *
     * @param $queryParams
     * @throws \WebserviceException
     */
    public function getObjectsByTimeRange()
    {
        $this->records = $this->getTimeRangeRecords('id_order');

        $cursorValue = $this->getCursorValue();
        $returnRecords = $this->buildTimeRangeReturnRecords(
            'Order',
            'id_order',
            'KlaviyoPs\Classes\BusinessLogicServices\OrderPayloadService'
        );

        return $this->buildTimeRangeResultsPayload($returnRecords, $cursorValue);
    }
}
