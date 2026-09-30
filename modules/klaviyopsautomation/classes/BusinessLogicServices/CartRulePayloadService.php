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

use ObjectModelCore;
use KlaviyoPs\Classes\BusinessLogicServices\PayloadServiceInterface;
use CartRule;
use Configuration;
use DateTime;
use DateTimeZone;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\PrestashopServices\CartRuleService;
use KlaviyoPsModule;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoV3Sdk\KlaviyoV3Api;
use Tools;

class CartRulePayloadService extends PayloadServiceInterface
{
    const SENSITIVE_KEYS = [];

    const GET_METHOD_KEY_PAYLOAD = 'coupon';
    const POST_METHOD_KEY_PAYLOAD = 'coupons';

    /**
     * @inheritDoc
     * @param \CartRule $cartRule
     */
    public static function buildPayload(ObjectModelCore $cartRule, $id_shop = null)
    {
        return [
            KlaviyoV3Api::DATA_KEY_PAYLOAD => [
                self::GET_METHOD_KEY_PAYLOAD => self::removeSensitiveKeys($cartRule, self::SENSITIVE_KEYS),
            ]
        ];
    }

    /**
     * @param  array $cartRules
     * @return array
     */
    public static function buildPostPayload($cartRules)
    {
        $coupons = [];
        foreach ($cartRules as $value) {
            $coupons[] = [
                'code' => $value->code,
                'id' => $value->id,
                'date_from' => self::convertDateStringToUTC($value->date_from),
                'date_to' => self::convertDateStringToUTC($value->date_to),
            ];
        }

        return [
            KlaviyoV3Api::DATA_KEY_PAYLOAD => [
                self::POST_METHOD_KEY_PAYLOAD => $coupons,
            ]
        ];
    }

    /**
     * Generate multiple cart rules from a cart rule template
     *
     * @param  int $cartRuleId
     * @param  int $quantity
     * @param  DateTime $dateFrom
     * @param  DateTime $dateTo
     * @param  string $codePrefix
     * @return array
     */
    public static function generateFromId($cartRuleId, $quantity, $dateFrom, $dateTo, $codePrefix = '')
    {
        $klaviyops = KlaviyoPsModule::getInstance();
        /** @var CartRuleService $cartRuleService */
        $cartRuleService = $klaviyops->getService('klaviyops.prestashop_services.cart_rule');

        // Check count limit
        if ($quantity <= 0 || $quantity > KlaviyoValue::WEBSERVICE_CREATE_DELETE_LIMIT) {
            throw new KlaviyoException(sprintf('The count must be >0 and <=%s', KlaviyoValue::WEBSERVICE_CREATE_DELETE_LIMIT));
        }

        // Check timeframe
        $serverTimezone = new DateTimeZone(Configuration::get('PS_TIMEZONE'));
        $now = new DateTime('now', $serverTimezone);
        if (
            empty($dateFrom) ||
            empty($dateTo) ||
            $dateTo < $now
        ) {
            throw new KlaviyoException('date_to must be in the future');
        }
        if ($dateFrom > $dateTo) {
            throw new KlaviyoException('date_to must be later than date_from');
        }

        $cartRule = $cartRuleService->getObjectById($cartRuleId);

        $res = [];
        for ($i = 0; $i < $quantity; $i++) {
            $generatedCode = Tools::passwdGen(10, 'ALPHANUMERIC');
            if ($codePrefix !== '') {
                $generatedCode = "{$codePrefix}_" . $generatedCode;
            }

            $res[] = $cartRuleService->duplicate($cartRule, [
                'code' => $generatedCode,
                'quantity' => 1,
                'quantity_per_user' => 1,
                'highlight' => 0,
                'active' => 1,
                'id_customer' => 0,
                'date_from' => $dateFrom->format(KlaviyoValue::WEBSERVICE_DATE_TIME_FORMAT),
                'date_to' => $dateTo->format(KlaviyoValue::WEBSERVICE_DATE_TIME_FORMAT),
            ]);
        }

        return $res;
    }

    /**
     * Generate multiple cart rules from a cart rule template
     *
     * @param  string $code
     * @param  int $quantity
     * @param  DateTime $dateFrom
     * @param  DateTime $dateTo
     * @param  string $codePrefix
     * @return array
     */
    public static function generateFromCode($code, $quantity, ?DateTime $dateFrom, ?DateTime $dateTo, $codePrefix)
    {
        // Check if code exist
        $cartRuleId = CartRule::getIdByCode($code);

        if (!$cartRuleId) {
            throw new KlaviyoException(sprintf("The cart rule '%s' does not exist.", $code));
        }

        return self::generateFromId($cartRuleId, $quantity, $dateFrom, $dateTo, $codePrefix);
    }
}
