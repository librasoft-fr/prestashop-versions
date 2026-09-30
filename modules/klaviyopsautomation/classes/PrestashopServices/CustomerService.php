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

use ArrayObject;
use Configuration;
use Customer;
use DateTime;
use Db;
use DbQuery;
use Gender;
use Group;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use Shop;
use ShopGroup;
use Validate;

class CustomerService
{
    /**
     * @var ValidateService
     */
    protected $validateService;

    /**
     * @var DateTimeService
     */
    protected $dateTimeService;

    /**
     * @var ContextService
     */
    protected $contextService;

    public function __construct(
        ValidateService $validateService,
        DateTimeService $dateTimeService,
        ContextService $contextService
    ) {
        $this->validateService = $validateService;
        $this->dateTimeService = $dateTimeService;
        $this->contextService = $contextService;
    }

    /**
     * Normalizing data from PrestaShop allows to use them in a uniform and secure way.
     * To use the methods of this class, start by normalizing data.
     *
     * @param Customer|array $customer
     * @return ArrayObject
     */
    public function normalize(
        $customer,
        ArrayObject $context
    ) {
        $customerObject = null;

        if (is_object($customer)) {
            $customerObject = $customer;

            $customer = (array) $customer;
            $customer['id_customer'] = $customerObject->id;
            $customer['cache_object'] = $customerObject;

            unset($customer['id']);
        }

        if (!isset($customer['id_customer'])) {
            $customer['id_customer'] = 0;
        }

        if (!isset($customer['id_shop'])) {
            $customer['id_shop'] = $context['id_shop'];
        }

        if (!isset($customer['id_shop_group'])) {
            $customer['id_shop_group'] = $context['id_shop_group'];
        }

        if (!isset($customer['id_gender'])) {
            $customer['id_gender'] = 0;
        }

        if (!isset($customer['id_default_group'])) {
            $customer['id_default_group'] = 0;
        }

        $customer['id_customer'] = (int) $customer['id_customer'];
        $customer['id_gender'] = (int) $customer['id_gender'];
        $customer['id_default_group'] = (int) $customer['id_default_group'];

        if ($customer['id_default_group'] === 0) {
            // If the current customer has not yet an account
            // We must set the default group which corresponds with the "unidentified" group
            // This allows us to have the same data returned by getAllGroups in this case
            // Because it's the behaviour of Customer::getGroups
            if ($customer['id_customer'] === 0) {
                $customer['id_default_group'] = (int) Configuration::get('PS_UNIDENTIFIED_GROUP');
            } else {
                $customer['id_default_group'] = (int) Customer::getDefaultGroupId(
                    $customer['id_customer']
                );
            }
        }

        $customer['context'] = $context;

        // Do not use these data as context
        // Indeed, This represents the context that user belongs to
        // But not the current context
        // There's the "context" entry for this
        $customer['id_shop'] = (int) $customer['id_shop'];
        $customer['id_shop_group'] = (int) $customer['id_shop_group'];

        return new ArrayObject($customer);
    }

    /**
     * Get Customer object from normalized data
     *
     * @param ArrayObject $customer
     * @return Customer
     * @throws KlaviyoException
     */
    public function getObject(ArrayObject $customer)
    {
        if (!$customer->offsetExists('cache_object')) {
            $customer['cache_object'] = new Customer($customer['id_customer']);

            // If the current customer has not yet an account
            // We must return a not "loaded" Customer object
            // And set email and shops properties
            if (!Validate::isLoadedObject($customer['cache_object'])) {
                $customer['cache_object']->email = $this->getEmail($customer);
                // By default, shop data are retrieved from the context
                // See self::normalize
                $customer['cache_object']->id_shop = $customer['id_shop'];
                $customer['cache_object']->id_shop_group = $customer['id_shop_group'];
            }
        }

        return $customer['cache_object'];
    }

    /**
     * Get the email address of a customer.
     *
     * Removed validation of email address as of 1.9.0 preferring
     * to let Klaviyo validate email addresses during event processing.
     *
     * @param ArrayObject $customer
     * @return string|null
     */
    public function getEmail(ArrayObject $customer)
    {
        return $customer['email'] ?? null;
    }

    /**
     * @return DateTime|null
     */
    public function getRegisterDate(ArrayObject $customer)
    {
        if (!$customer->offsetExists('date_add')) {
            return null;
        }

        return $this->dateTimeService->convertFromUTC($customer['date_add']);
    }

    /**
     * @return string|null
     */
    public function getBirthday(ArrayObject $customer)
    {
        if (
            !$customer->offsetExists('birthday') ||
            !Validate::isBirthDate($customer['birthday']) ||
            $customer['birthday'] === '0000-00-00'
        ) {
            return null;
        }

        return $customer['birthday'];
    }

    /**
     * @return Group[]
     * @throws KlaviyoException
     */
    public function getAllGroups(ArrayObject $customer)
    {
        $result = [];
        $allAccounts = $this->getAllAccounts($customer);

        foreach ($allAccounts as $account) {
            $accountObject = $this->getObject($account);

            foreach ($accountObject->getGroups() as $idGroup) {
                $group = $this->getGroupById(
                    (int) $idGroup,
                    $customer['context']
                );

                if ($group === null) {
                    continue;
                }

                // Avoids duplication by indexing by id_shop
                $result[(int) $group->id] = $group;
            }
        }

        // Removing indexing by id_shop
        return array_values($result);
    }

    /**
     * @return Group[]
     * @throws KlaviyoException
     */
    public function getDefaultGroups(ArrayObject $customer)
    {
        $result = [];
        // A customer can have an account on differents shops
        // And the same customer can be on differents customer group
        // So, a customer can have multiple default customer groups
        // Therefore, we need to retrieve the accounts on the differents shops for the customer
        // And get default group for each account
        $allAccounts = $this->getAllAccounts($customer);

        foreach ($allAccounts as $account) {
            // We need to get associated shops for an account
            // In order to provide the default group for each shops
            // Where the customer have an account
            $allShops = $this->getAssociatedShops($account);

            foreach ($allShops as $shop) {
                $group = $this->getGroupById(
                    $account['id_default_group'],
                    $account['context']
                );

                if ($group === null) {
                    continue;
                }

                // Indexing by shop id to have the default group for each shop
                $result[(int) $shop->id] = $group;
            }
        }

        return $result;
    }

    /**
     * @return Gender|null
     */
    public function getGender(ArrayObject $customer)
    {
        if (!$customer->offsetExists('cache_gender')) {
            $gender = new Gender(
                $customer['id_gender'],
                $customer['context']['id_default_lang']
            );

            if (!Validate::isLoadedObject($gender)) {
                $gender = null;
            }

            $customer['cache_gender'] = $gender;
        }

        return $customer['cache_gender'];
    }

    /**
     * A customer can have account in another shop
     * So, we get all its accounts by his mail
     *
     * @return ArrayObject[]
     * @throws KlaviyoException
     */
    public function getAllAccounts(ArrayObject $customer)
    {
        static $cache = [];

        // If multishop isn't enabled
        // Then, there's no need to get accounts over the shops
        if (!$this->contextService->hasMultishop()) {
            return [$customer];
        }

        $email = $this->getEmail($customer);

        if (!array_key_exists($email, $cache)) {
            $result = [];

            $emailEscaped = pSQL($email);

            $query = (new DbQuery())
                ->select('c.id_customer')
                ->from('customer', 'c')
                ->where("c.email = '{$emailEscaped}'")
            ;
            $otherAccounts = Db::getInstance()->executeS($query);

            if (!is_array($otherAccounts)) {
                throw new KlaviyoException('Impossible to get other accounts from the customer');
            }

            foreach ($otherAccounts as $account) {
                $o = new Customer((int) $account['id_customer']);

                if (!Validate::isLoadedObject($o)) {
                    continue;
                }

                $accountContext = $this->contextService->normalize([
                    'id_shop' => $o->id_shop,
                    'id_lang' => $o->id_lang,
                    'id_customer' => $o->id,
                ]);
                $result[] = $this->normalize(
                    $o,
                    $accountContext
                );
            }

            $cache[$email] = $result;
        }

        return $cache[$email];
    }

    /**
     * Retrieve the associated shops for this customer
     *
     * @return Shop[]
     * @throws KlaviyoException
     */
    public function getAssociatedShops(ArrayObject $customer)
    {
        if (!$customer->offsetExists('cache_associated_shops')) {
            // By default, return the shop where the customer is registered
            $result = [
                (new Shop($customer['id_shop'])),
            ];

            // If multishop isn't enabled
            // Then, there's no need to get associated shops for the customer
            // Get the shop where the customer is registered is enough
            if ($this->contextService->hasMultishop()) {
                $shopGroup = new ShopGroup($customer['id_shop_group']);

                if (!Validate::isLoadedObject($shopGroup)) {
                    throw new KlaviyoException('Customer doesn\'t have associated shop');
                }

                // If the store group is configured to share customers
                // Then, we must return all Shops in group
                // Else, we must return only the shop where the customer is
                if ($shopGroup->share_customer) {
                    $result = $this->contextService->getAllShops([
                        'id_shop_group' => $customer['id_shop_group'],
                    ]);
                }
            }

            $customer['cache_associated_shops'] = $result;
        }

        return $customer['cache_associated_shops'];
    }

    /**
     * Retrieves all stores where the customer has an account
     *
     * @return Shop[]
     * @throws KlaviyoException
     * @see self::getAllAccounts
     * @see self::getAssociatedShops
     */
    public function getAllShops(ArrayObject $customer)
    {
        if (!$customer->offsetExists('cache_all_shops')) {
            $result = [];
            /** @var Shop[] $temp */
            $temp = [];

            $otherAccounts = $this->getAllAccounts($customer);

            foreach ($otherAccounts as $account) {
                $temp = array_merge(
                    $temp,
                    $this->getAssociatedShops($account)
                );
            }

            // Avoids duplication by indexing by id_shop
            foreach ($temp as $shop) {
                $result[(int) $shop->id] = $shop;
            }

            // Removing indexing by id_shop
            $customer['cache_all_shops'] = array_values($result);
        }

        return $customer['cache_all_shops'];
    }

    /**
     * @param int $idGroup
     * @return Group|null
     */
    protected function getGroupById(
        $idGroup,
        ArrayObject $context
    ) {
        static $cache = [];

        if (!array_key_exists($idGroup, $cache)) {
            $group = new Group(
                $idGroup,
                $context['id_default_lang']
            );

            if (!Validate::isLoadedObject($group)) {
                $group = null;
            }

            $cache[$idGroup] = $group;
        }

        return $cache[$idGroup];
    }
}
