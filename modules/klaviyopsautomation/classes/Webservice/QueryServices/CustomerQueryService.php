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

use Customer;
use KlaviyoPs\Classes\BusinessLogicServices\CustomerPayloadService;

class CustomerQueryService extends QueryServiceInterface
{
    /**
     * @inheritDoc
     */
    protected function getTableName()
    {
        return 'customer';
    }

    /**
     * @inheritDoc
     */
    public function getObjectById($customerId)
    {
        $customer = new Customer($customerId);
        return CustomerPayloadService::buildPayload($customer);
    }

    /**
     * @inheritDoc
     */
    public function getObjectsByTimeRange()
    {
        $this->records = $this->getTimeRangeRecords('id_customer');

        $cursorValue = $this->getCursorValue();
        $returnRecords = $this->buildTimeRangeReturnRecords(
            'Customer',
            'id_customer',
            'KlaviyoPs\Classes\BusinessLogicServices\CustomerPayloadService'
        );

        return $this->buildTimeRangeResultsPayload($returnRecords, $cursorValue);
    }
}
