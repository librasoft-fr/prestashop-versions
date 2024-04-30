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

use Context;
use Db;
use Product;
use Shop;
use WebserviceException;
use KlaviyoPs\Classes\BusinessLogicServices\ProductPayloadService;

class ProductQueryService extends QueryServiceInterface
{
    /**
     * ProductQueryService constructor. Require shop parameter in query.
     *
     * @param array $queryParams
     */
    public function __construct(array $queryParams)
    {
        parent::__construct($queryParams);
        if (!$this->params['shop']) {
            throw new WebserviceException(
                'Invalid request parameters, \'shop\' parameter is required for \'products\' resource.',
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function getTableName()
    {
        return 'product_shop';
    }

    /**
     * @inheritDoc
     */
    public function getObjectById($productId)
    {
        return ProductPayloadService::buildPayload(
            new Product($productId, false, null, $this->params['shop']),
            $id_shop = $this->params['shop']
        );
    }

    /**
     * @inheritDoc
     */
    public function getObjectsByTimeRange()
    {
        $this->records = $this->getTimeRangeRecords('id_product');
        $cursorValue = $this->getCursorValue();

        $returnRecords = array();
        foreach ($this->records as $record) {
            array_push(
                $returnRecords,
                ProductPayloadService::buildPayload(
                    new Product($record['id_product'], false, null, $this->params['shop']),
                    $id_shop = $this->params['shop']
                )
            );
        }
        return $this->buildTimeRangeResultsPayload($returnRecords, $cursorValue);
    }
}
