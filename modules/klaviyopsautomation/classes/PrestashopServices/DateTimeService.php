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

namespace KlaviyoPs\Classes\PrestashopServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use Configuration;
use DateTime;
use DateTimeZone;
use Exception;
use Validate;

class DateTimeService
{
    /**
     * Transform string to datetime object. Convert to local time.
     * Incoming date will be in UTC.
     *
     * @param string $date
     * @return DateTime|null
     */
    public function convertFromUTC($date)
    {
        try {
            // It's OK to not pass in either of these date parameters.
            if (
                empty($date) ||
                !Validate::isDate($date) ||
                $date === '0000-00-00'
            ) {
                throw new KlaviyoException();
            }

            $serverTimezone = new DateTimeZone(Configuration::get('PS_TIMEZONE'));
            $datetime = DateTime::createFromFormat(KlaviyoValue::WEBSERVICE_DATE_TIME_FORMAT, $date, new DateTimeZone('UTC'));
            if ($datetime === false) {
                throw new KlaviyoException();
            }
            return $datetime->setTimezone($serverTimezone);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Format date in order to send it to Klaviyo
     *
     * @return string
     * @see https://developers.klaviyo.com/en/docs/acceptable_date_and_timestamp_formats_for_profile_and_event_properties
     */
    public function format(DateTime $date)
    {
        // ISO8601
        return $date->format('Y-m-d\TH:i:sP');
    }
}
