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

use Cart;
use Db;
use KlaviyoPs\Classes\BusinessLogicServices\CartPayloadService;

class CartQueryService extends QueryServiceInterface
{
    /**
     * @inheritDoc
     */
    protected function getTableName()
    {
        return 'cart';
    }

    /**
     * Retrieve and build single cart by its ID.
     *
     * @param $cartId
     */
    public function getObjectById($cartId)
    {
        $cart = new Cart($cartId);
        return CartPayloadService::buildPayload($cart);
    }

    /**
     * Fetch cart IDs for a given time range.
     *
     * @return array
     */
    public function getObjectsByTimeRange()
    {
        $this->records = $this->getTimeRangeRecords('id_cart');

        $cursorValue = $this->getCursorValue();
        $returnRecords = $this->buildTimeRangeReturnRecords(
            'Cart',
            'id_cart',
            'KlaviyoPs\Classes\BusinessLogicServices\CartPayloadService'
        );

        return $this->buildTimeRangeResultsPayload($returnRecords, $cursorValue);
    }
}
