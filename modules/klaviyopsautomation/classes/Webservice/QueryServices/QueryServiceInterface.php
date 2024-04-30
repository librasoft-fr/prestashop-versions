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

use DateTime;
use DateTimeZone;
use Exception;
use Configuration;
use Db;
use ObjectModel;
use PrestaShopDatabaseException;
use WebserviceException;

abstract class QueryServiceInterface
{
    /** @var array Query parameters from webservice request. */
    protected $params;

    /** @var array Result records from db query. */
    protected $records;

    const DEFAULT_ERROR_CODE = 0;

    const DEFAULT_BATCH_SIZE = 50;
    const MAX_BATCH_SIZE = 100;

    // 24 hours
    const MAX_SINCE_ONLY_TIME_RANGE_SECONDS = 86400;
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    const WHERE_CLAUSE_PARAMS = array('since', 'until', 'next', 'shop');

    public function __construct(array $queryParams)
    {
        $this->params = $this->handleRequestParams($queryParams);
    }

    /**
     * Get the corresponding table name (without prefix) for queries in this QueryService.
     *
     * @return string
     */
    abstract protected function getTableName();

    /**
     * Fetch specific object by ID.
     *
     * @param $objectId
     * @return array
     */
    abstract public function getObjectById($objectId);

    /**
     * Fetch object IDs for a given time range.
     *
     * @return array
     */
    abstract public function getObjectsByTimeRange();

    /**
     * Fetch count of objects for a given time range
     * @return array
     */
    public function getCountByTimeRange()
    {
        $sql = $this->buildCountQuery();
        $result = $this->handleDbQuery($sql);

        $returnCount = 0;
        if ($result && array_key_exists('COUNT(*)', $result[0])) {
            $returnCount = $result[0]['COUNT(*)'];
        }
        return array('count' => (int) $returnCount);
    }

    /**
     * Build query to return count of objects for given time range.
     *
     * @return string
     */
    protected function buildCountQuery()
    {
        $select = 'SELECT COUNT(*) ';
        $from = 'FROM ' . _DB_PREFIX_ . $this->getTableName() . ' ';
        $where = $this->buildTimeRangeWhereClause();

        return $select . $from . $where;
    }

    /**
     * Build sql query and fetch record IDs from database for time range query.
     *
     * @param $idField
     * @return array
     * @throws WebserviceException
     */
    protected function getTimeRangeRecords($idField)
    {
        $sql = $this->buildTimeRangeQuery($idField);
        return $this->handleDbQuery($sql);
    }

    /**
     * Build array of hydrated objects to return for time range webservice query.
     *
     * @param $objectClass
     * @param $idField
     * @param $payloadServiceClass
     * @return array
     */
    protected function buildTimeRangeReturnRecords($objectClass, $idField, $payloadServiceClass)
    {
        $returnRecords = array();
        foreach ($this->records as $record) {
            $customer = new $objectClass($record[$idField]);
            array_push(
                $returnRecords,
                call_user_func_array(array($payloadServiceClass, 'buildPayload'), array($customer))
            );
        }

        return $returnRecords;
    }

    /**
     * Build query to return object ids for given time range.
     *
     * @param $idField
     * @return string
     */
    protected function buildTimeRangeQuery($idField)
    {
        $select = 'SELECT ' . $idField . ' ';
        $from = 'FROM ' . _DB_PREFIX_ . $this->getTableName() . ' ';
        $where = $this->buildTimeRangeWhereClause();
        // Filtering by time range by ordering by id (primary key) so we can use cursor pagination.
        $order_by = 'ORDER BY ' . $idField . ' DESC ';
        $limit = 'LIMIT ' . (int) $this->params['batch_size'];

        return $select . $from . $where . $order_by . $limit;
    }

    /**
     * Build where clause for time range query. Handle queries with neither
     * since nor until, just one or the other and both parameters.
     *
     * @param string $alias Table alias for joins.
     */
    protected function buildTimeRangeWhereClause($alias = '')
    {
        if ($this->isWhereClauseEmpty()) {
            return '';
        }

        $predicates = array();
        foreach (self::WHERE_CLAUSE_PARAMS as $param) {
            if (isset($this->params[$param])) {
                array_push($predicates, $alias . call_user_func(array($this, 'getPredicate' . $param)));
            }
        }

        return 'WHERE ' . implode(' AND ', $predicates) . ' ';
    }

    /**
     * Build where clause predicate for since query parameter.
     *
     * @return string
     */
    protected function getPredicateSince()
    {
        return 'date_upd >= \'' . pSQL($this->params['since']) . '\'';
    }

    /**
     * Build where clause predicate for until query parameter.
     *
     * @return string
     */
    protected function getPredicateUntil()
    {
        return 'date_upd <= \'' . pSQL($this->params['until']) . '\'';
    }

    /**
     * Build where clause predicate for next query parameter.
     *
     * @return string
     */
    protected function getPredicateNext()
    {
        return '`' . bqSQL($this->params['next'][0]) . '` <= ' . (int) $this->params['next'][1];
    }

    /**
     * Build where clause predicate for shop query parameter.
     *
     * @return string
     */
    protected function getPredicateShop()
    {
        return 'id_shop = ' . (int) $this->params['shop'];
    }

    /**
     * Check to ensure at least one of the where clause parameters is set.
     *
     * @return bool
     */
    protected function isWhereClauseEmpty()
    {
        return count(array_filter(self::WHERE_CLAUSE_PARAMS, function ($param) {
            return isset($this->params[$param]);
        })) === 0;
    }

    /**
     * Process request query parameters.
     *
     * @param array $queryParams
     * @return array
     */
    protected function handleRequestParams(array $queryParams)
    {
        $processedParams = array(
            'since' => $this->handleDateParam(isset($queryParams['since']) ? $queryParams['since'] : null),
            'until' => $this->handleDateParam(isset($queryParams['until']) ? $queryParams['until'] : null),
            'shop' => $this->handleShopParam(isset($queryParams['shop']) ? $queryParams['shop'] : null),
            'batch_size' => $this->handleBatchSizeParam(isset($queryParams['batch_size']) ? $queryParams['batch_size'] : self::DEFAULT_BATCH_SIZE),
            'next' => $this->handleNextParam(isset($queryParams['next']) ? $queryParams['next'] : null),
        );

        if (
            isset($processedParams['since']) &&
            isset($processedParams['until']) &&
            $processedParams['since'] > $processedParams['until']
        ) {
            throw new WebserviceException(
                'Invalid request parameters, \'since\' must be before \'until\'',
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }

        if (
            isset($processedParams['since']) &&
            !isset($processedParams['until'])
        ) {
            $this->validateSinceParam($processedParams['since']);
        }

        return $processedParams;
    }

    /**
     * Transform string to datetime object. Convert to local time then transform back to string.
     * Incoming date will be in UTC.
     *
     * @param $date
     * @return string|null
     */
    private function handleDateParam($date)
    {
        // It's OK to not pass in either of these date parameters.
        if (is_null($date)) {
            return null;
        }
        $serverTimezone = new DateTimeZone(Configuration::get('PS_TIMEZONE'));
        $datetime = DateTime::createFromFormat(self::DATE_TIME_FORMAT, $date, new DateTimeZone('UTC'));
        if ($datetime) {
            return $datetime->setTimezone($serverTimezone)->format(self::DATE_TIME_FORMAT);
        }
        throw new WebserviceException(
            sprintf('Could not parse date format. Please use: %s', gmdate(self::DATE_TIME_FORMAT)),
            array(self::DEFAULT_ERROR_CODE, 400)
        );
    }

    /**
     * Validate since parameter is not more than 24 hours before now. Since parameter will be in local timezone.
     * This is only called if we don't pass in an until parameter with since.
     *
     * @param $date
     * @throws WebserviceException
     */
    private function validateSinceParam($date)
    {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $since = DateTime::createFromFormat(
            self::DATE_TIME_FORMAT,
            $date,
            new DateTimeZone(date_default_timezone_get())
        );
        $since->setTimezone(new DateTimeZone('UTC'));

        $timeDeltaSeconds = abs($now->getTimestamp() - $since->getTimestamp());
        if ($timeDeltaSeconds > self::MAX_SINCE_ONLY_TIME_RANGE_SECONDS) {
            throw new WebserviceException(
                'Since parameter with no \'until\' cannot be more than 24 hours before now.',
                array(self::DEFAULT_ERROR_CODE, 400)
            );
        }
    }

    /**
     * Validate batch size parameter does not exceed maximum, cast to integer and increment by one for cursor pagination.
     *
     * @param string $batchSize
     * @return int
     */
    private function handleBatchSizeParam($batchSize)
    {
        $validatedBatchSize = self::DEFAULT_BATCH_SIZE;
        if (ctype_digit($batchSize)) {
            $validatedBatchSize = $batchSize <= self::MAX_BATCH_SIZE ? $batchSize : self::MAX_BATCH_SIZE;
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
    private function handleShopParam($shopId)
    {
        if ('' === $shopId || !$shopId) {
            return null;
        } elseif (ctype_digit($shopId) && ObjectModel::existsInDatabase((int) $shopId, 'shop')) {
            return (int) $shopId;
        } else {
            throw new WebserviceException(
                sprintf('\'%s\' is not a valid Shop ID.', $shopId),
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }
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
    private function handleNextParam($nextParam)
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
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }
        if (count($nextValue) != 2) {
            throw new WebserviceException(
                'Invalid next parameter.',
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }
        return $nextValue;
    }

    /**
     * Attempt Db query and raise WebserviceException if there is a Db exception.
     *
     * @param string $sql
     * @return array
     * @throws WebserviceException
     */
    protected function handleDbQuery($sql)
    {
        try {
            $result = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            throw new WebserviceException(
                $e->getMessage(),
                [self::DEFAULT_ERROR_CODE, 500]
            );
        }

        // An invalid query above will return false.
        if (is_bool($result) && !$result) {
            throw new WebserviceException(
                'Invalid request, database returned false.',
                [self::DEFAULT_ERROR_CODE, 400]
            );
        }

        return $result;
    }

    /**
     * Format array containing query results and cursor to retrieve next page.
     *
     * @param $records
     * @param $cursorValue
     * @return array
     */
    protected function buildTimeRangeResultsPayload($records, $cursorValue)
    {
        return array(
            'data' => $records,
            'next_cursor' => $cursorValue,
        );
    }

    /**
     * Removes last record returned from query if results count equals one greater than batch size. Format and encode
     * cursor from this record.
     *
     * Example cursor to be encoded (primary key field name and value): 'id_cart:10'
     *
     * @return string
     */
    protected function getCursorValue()
    {
        $nextCursor = '';
        if (!(count($this->records) < $this->params['batch_size'])) {
            $cursorRecord = array_pop($this->records);
            $recordKey = key($cursorRecord);
            $nextCursor = base64_encode($recordKey . ':' . $cursorRecord[$recordKey]);
        }

        return $nextCursor;
    }
}
