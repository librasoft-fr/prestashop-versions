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

namespace KlaviyoPs\Classes\KlaviyoServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ArrayObject;
use Country;
use Gender;
use Group;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoPs\Classes\PrestashopServices\ContextService;
use KlaviyoPs\Classes\PrestashopServices\CustomerService;
use KlaviyoPs\Classes\PrestashopServices\DateTimeService;
use Language;
use Shop;

class CustomerEventService
{
    const CUSTOMER_JS_PROPERTIES_MAP = [
        '$email' => 'email',
        'firstname' => '$first_name',
        'lastname' => '$last_name',
    ];

    const CUSTOMER_WEBSERVICE_PROPERTIES_MAP = [
        '$email' => 'email',
    ];

    /**
     * @var DateTimeService
     */
    protected $dateTimeService;

    /**
     * @var ContextService
     */
    protected $contextService;

    /**
     * @var CustomerService
     */
    protected $customerService;

    public function __construct(
        DateTimeService $dateTimeService,
        ContextService $contextService,
        CustomerService $customerService
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->contextService = $contextService;
        $this->customerService = $customerService;
    }

    /**
     * Build payload for webservice to identify a PrestaShop customer into Klaviyo
     *
     * @param ArrayObject $customer
     * @return array
     * @throws KlaviyoException
     */
    public function buildPayload(ArrayObject $customer)
    {
        if (!isset($customer['cache_payload'])) {
            $result = [];

            $allGroups = $this->customerService->getAllGroups($customer);
            $defaultGroups = $this->customerService->getDefaultGroups($customer);
            $gender = $this->customerService->getGender($customer);
            $registerDate = $this->customerService->getRegisterDate($customer);
            $birthday = $this->customerService->getBirthday($customer);
            $lang = $this->contextService->getLang($customer['context']);
            $country = $this->contextService->getCountry($customer['context']);

            $result['$email'] = $this->customerService->getEmail($customer);

            if (!empty($customer['firstname'])) {
                $result['firstname'] = $customer['firstname'];
            }

            if (!empty($customer['lastname'])) {
                $result['lastname'] = $customer['lastname'];
            }

            if ($lang !== null && $country !== null) {
                $result['$locale'] = $this->extractLocale(
                    $lang,
                    $country
                );
            }

            if ($gender !== null) {
                $result['Gender'] = $this->extractGenderName(
                    $gender
                );
            }

            if (!empty($allGroups)) {
                $result['PrestaShop Groups'] = $this->extractAllGroupsName(
                    $allGroups
                );
            }

            if (!empty($defaultGroups)) {
                $result['PrestaShop Default Group'] = $this->extractAllDefaultGroupsName(
                    $defaultGroups
                );
            }

            if ($registerDate !== null) {
                $result['PrestaShop Account Created Date'] = $this->dateTimeService->format(
                    $registerDate
                );
            }

            if ($birthday !== null) {
                $result['Birthday'] = $birthday;
            }

            if ($this->contextService->hasMultishop()) {
                $shops = $this->customerService->getAllShops($customer);

                if (!empty($shops)) {
                    $result['PrestaShop Shops'] = $this->extractAllShopsName(
                        $shops
                    );
                }
            }

            $customer['cache_payload'] = $result;
        }

        return $customer['cache_payload'];
    }

    /**
     * Build payload for JS SDK to identify a PrestaShop customer into Klaviyo
     * Data are adapted from buildPayload for JS SDK
     *
     * @return array
     * @see self::buildPayload
     * @throws KlaviyoException
     */
    public function buildPayloadForJs(ArrayObject $customer)
    {
        return $this->adaptPayload($customer, self::CUSTOMER_JS_PROPERTIES_MAP);
    }

    /**
     * Build payload for PrestaShop Webservice to identify a PrestaShop customer into Klaviyo
     * Data are adapted from buildPayload for PrestaShop Webservice
     *
     * @return array
     * @see self::buildPayload
     */
    public function buildPayloadForWebservice(ArrayObject $customer)
    {
        return $this->adaptPayload($customer, self::CUSTOMER_WEBSERVICE_PROPERTIES_MAP);
    }

    /**
     * Build $locale data for user by using IETF BCP 47 format
     * https://help.klaviyo.com/hc/en-us/articles/360053679071#:~:text=%24locale%3A%C2%A0The,from%20%24locale%20property
     *
     * @param Language $lang
     * @param Country|null $country
     * @return string
     */
    protected function extractLocale(
        Language $lang,
        Country $country
    ) {
        $langIso = strtolower($lang->iso_code);
        $countryIso = strtoupper($country->iso_code);

        return "{$langIso}-{$countryIso}";
    }

    /**
     * @return string
     */
    protected function extractGenderName(Gender $gender)
    {
        $genderName = trim((string) $gender->name);

        // Not supposed to happen
        if (empty($genderName)) {
            return (string) $gender->id;
        }

        return $genderName;
    }

    /**
     * @return string
     */
    protected function extractGroupName(Group $group)
    {
        $idGroup = (string) $group->id;
        $groupName = trim((string) $group->name);

        return trim("{$idGroup} {$groupName}");
    }

    /**
     * @param Group[] $allGroups
     * @return string[]
     */
    protected function extractAllGroupsName($allGroups)
    {
        $result = [];

        foreach ($allGroups as $group) {
            $result[] = $this->extractGroupName($group);
        }

        return $result;
    }

    /**
     * @param Group[] $allGroups
     * @return string|string[]
     */
    protected function extractAllDefaultGroupsName($allGroups)
    {
        // If multishop is not enabled
        // Then, send Default group with array format and idShop will complexify the data format
        // Whereas this is not useful
        if (!$this->contextService->hasMultishop()) {
            $result = $this->extractAllGroupsName($allGroups);

            return $result[0];
        }

        $result = [];

        foreach ($allGroups as $idShop => $group) {
            $shop = new Shop($idShop);

            $shopPart = $this->extractShopName($shop);
            $groupPart = $this->extractGroupName($group);

            $result[] = trim("{$shopPart} - {$groupPart}");
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function extractShopName(Shop $shop)
    {
        $idShop = (string) $shop->id;
        $shopName = trim((string) $shop->name);

        return trim("{$idShop} {$shopName}");
    }

    /**
     * @param Shop[] $allShops
     * @return string[]
     */
    protected function extractAllShopsName($allShops)
    {
        $result = [];

        foreach ($allShops as $shop) {
            $result[] = $this->extractShopName($shop);
        }

        return $result;
    }

    /**
     * @param ArrayObject $customer
     * @param array $mapping
     * @return array
     * @throws KlaviyoException
     */
    protected function adaptPayload(ArrayObject $customer, $mapping)
    {
        $result = $this->buildPayload($customer);

        // Convert properties for JS SDK payload
        // And keep only custom properties in $temp
        foreach ($mapping as $old => $new) {
            if (!isset($result[$old])) {
                continue;
            }

            $result[$new] = $result[$old];
            unset($result[$old]);
        }

        return $result;
    }
}
