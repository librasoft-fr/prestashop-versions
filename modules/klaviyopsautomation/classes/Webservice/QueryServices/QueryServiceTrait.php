<?php

namespace KlaviyoPs\Classes\Webservice\QueryServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPs\Classes\KlaviyoValue;
use ObjectModel;
use PrestaShop\PrestaShop\Adapter\Entity\WebserviceRequest;
use WebserviceException;

trait QueryServiceTrait
{
    /**
     * Removes last record returned from query if results count equals one greater than batch size. Format and encode
     * cursor from this record.
     *
     * Example cursor to be encoded (primary key field name and value): 'id_cart:10'
     *
     * @param $records array
     * @return string
     */
    public function getCursorValue(&$records, $batchSize): string
    {
        $nextCursor = '';
        if (!(count($records) < $batchSize)) {
            $cursorRecord = array_pop($records);
            $recordKey = key($cursorRecord);
            $nextCursor = base64_encode($recordKey . ':' . $cursorRecord[$recordKey]);
        }

        return $nextCursor;
    }

    /**
     * Validate batch size parameter does not exceed maximum, cast to integer and increment by one for cursor pagination.
     *
     * Note that actually a max of ($batchSize + 1) records will be returned from the DB.
     * @param string $batchSize
     * @return int
     */
    public function handleBatchSizeParam($batchSize): int
    {
        $validatedBatchSize = KlaviyoValue::WEBSERVICE_DEFAULT_BATCH_SIZE;
        if (ctype_digit((string) $batchSize)) {
            $validatedBatchSize = min($batchSize, KlaviyoValue::WEBSERVICE_MAX_BATCH_SIZE);
        }
        // Fetch one extra record than batch size to use as next cursor.
        return $validatedBatchSize + 1;
    }

    /**
     * Ensure that shop ID exists in the db.
     *
     * @param string $shopId
     * @return int|null
     * @throws WebserviceException
     */
    public function handleShopParam($shopId)
    {
        if ('' === $shopId || !$shopId) {
            return null;
        } elseif (ctype_digit((string) $shopId) && ObjectModel::existsInDatabase((int) $shopId, 'shop')) {
            return (int) $shopId;
        } else {
            throw new WebserviceException(
                sprintf('\'%s\' is not a valid Shop ID.', $shopId),
                [$this->DEFAULT_ERROR_CODE, 400]
            );
        }
    }

    /**
     * Handles productIds params. Ensures it is valid stringified array literal
     *
     *  handleProductIdsParam('[1,2,4]') => [1,2,4]
     *  handleProductIdsParam('[1,,4]') => WebserviceException
     *  handleProductIdsParam('[word]') => WebserviceException
     *  handleProductIdsParam('[  1]') => [1]
     *
     *
     * @param string $productIds
     * @return non-empty-array|null
     * @throws WebserviceException in case of malformed literal or non-int values as members
     */
    public function handleProductIdsParam(string $productIds)
    {
        $idFields = $this->parseStrArray($productIds);
        if (!$idFields) {
            throw new WebserviceException(
                sprintf('\'%s\' is not a valid array of Product IDs.', $productIds),
                [$this->DEFAULT_ERROR_CODE, 400]
            );
        }
        foreach ($idFields as $val) {
            $val = (int)$val;
            if (!$val) { // product ids are autoinc values.
                throw new WebserviceException(
                    sprintf('\'%s\' is not a valid array of Product IDs.', $productIds),
                    [$this->DEFAULT_ERROR_CODE, 400]
                );
            }
        }

        return $idFields;
    }


    /**
     * Parses string representation of an array
     *
     * @param string $str
     * @return array Parsed array
     */
    public static function parseStrArray(string $str)
    {
        $wsProxy = new class extends WebserviceRequest {
            public function parseDisplayFields($str)
            {
                return array_values(parent::parseDisplayFields($str))[0];
            }
        };
        return $wsProxy->parseDisplayFields($str);
    }

    /**
     * Decode and validate next parameter for cursor pagination.
     *
     * Example cursor: array('id_cart' => 10)
     *
     * @param $nextParam
     * @return false|string[]|null
     * @throws WebserviceException
     */
    public function handleNextParam($nextParam)
    {
        // Return null if $nextParam is empty string or null (wasn't included in query params).
        if (!$nextParam) {
            return null;
        }
        try {
            $nextValue = explode(':', base64_decode($nextParam));
        } catch (Exception $e) {
            throw new WebserviceException(
                $e->getMessage(),
                [$this->DEFAULT_ERROR_CODE, 400]
            );
        }
        if (count($nextValue) != 2) {
            throw new WebserviceException(
                'Invalid next parameter.',
                [$this->DEFAULT_ERROR_CODE, 400]
            );
        }
        return $nextValue;
    }

    /**
     * Format array containing query results and cursor to retrieve next page.
     *
     * @param $records
     * @param $cursorValue
     * @return array
     */
    public function buildCursorResultsPayload($records, $cursorValue)
    {
        return array(
            'data' => $records,
            'next_cursor' => $cursorValue,
        );
    }
}
