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

namespace KlaviyoPs\Classes\BusinessLogicServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use DateTime;
use DateTimeZone;
use KlaviyoPs\Classes\KlaviyoUtils;
use ObjectModelCore;

class PayloadServiceInterface
{
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Build payload for object either to return in API response or send via webhook.
     *
     * @param ObjectModelCore $objectModel
     * @param $id_shop
     * @param int $shopId
     * @return array
     * @throws \BadMethodCallException Method must be implemented by child classes.
     */
    public static function buildPayload(ObjectModelCore $objectModel, $id_shop = null)
    {
        throw new \BadMethodCallException('buildPayload() method must be implemented by child of ' . self::class);
    }

    /**
     * Remove sensitive keys from Objects so we don't return this information in payloads. Need to convert
     * to an array using encode/decode otherwise we will get lots of additional properties we don't want.
     *
     * @param ObjectModelCore $toClean
     * @param array $sensitiveKeys
     * @return array
     */
    protected static function removeSensitiveKeys(ObjectModelCore $toClean, array $sensitiveKeys)
    {
        return self::recursivelyRemoveSensitiveKeys(self::objectToArray($toClean), $sensitiveKeys);
    }

    /**
     * Remove sensitive keys from Objects recursively.
     *
     * @param Any $toClean
     * @param array $sensitiveKeys
     * @return Any
     */
    private static function recursivelyRemoveSensitiveKeys($toClean, array $sensitiveKeys)
    {
        if (!is_array($toClean)) {
            return $toClean;
        }
        if (!KlaviyoUtils::hasStringKeys($toClean)) {
            // Calls recursivelyRemoveSensitiveKeys for each item in the indexed array.
            return array_map(
                function ($v) use (&$sensitiveKeys) {
                    return self::recursivelyRemoveSensitiveKeys($v, $sensitiveKeys);
                },
                $toClean
            );
        }
        $cleaned = array_diff_key($toClean, array_flip($sensitiveKeys));
        foreach ($cleaned as $k => $v) {
            $cleaned[$k] = self::recursivelyRemoveSensitiveKeys($v, $sensitiveKeys);
        }
        return $cleaned;
    }

    /**
     * Convert ObjectModelCore and children to array.
     *
     * @param ObjectModelCore $obj
     * @return mixed
     */
    protected static function objectToArray(ObjectModelCore $obj)
    {
        return json_decode(json_encode($obj), true);
    }

    /**
     * Convert date and time from local timezone to UTC.
     *
     * @param $date
     * @return string
     */
    protected static function convertDateStringToUTC($date)
    {
        $shopTimezone = new DateTimeZone(Configuration::get('PS_TIMEZONE'));
        $datetime = DateTime::createFromFormat(self::DATE_TIME_FORMAT, $date, $shopTimezone);

        return $datetime->setTimezone(new DateTimeZone('UTC'))->format(self::DATE_TIME_FORMAT);
    }
}
