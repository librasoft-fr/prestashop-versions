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
use Customer;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use Validate;

class CustomerService
{
    /**
     * @var ValidateService
     */
    protected $validateService;

    public function __construct(
        ValidateService $validateService
    ) {
        $this->validateService = $validateService;
    }

    /**
     * Normalizing data from PrestaShop allows to use them in a uniform and secure way.
     * To use the methods of this class, start by normalizing data.
     *
     * @param Customer|array $customer
     * @return ArrayObject
     */
    public function normalize($customer)
    {
        $customerObject = null;

        if (is_object($customer)) {
            $customerObject = $customer;

            $customer = (array)$customer;
            $customer['id_customer'] = $customerObject->id;
            $customer['cache_object'] = $customerObject;

            unset($customer['id']);
        }

        if (!isset($customer['id_customer'])) {
            $customer['id_customer'] = 0;
        }

        $customer['id_customer'] = (int) $customer['id_customer'];

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

            if (!Validate::isLoadedObject($customer['cache_object'])) {
                throw new KlaviyoException('Customer is not valid');
            }
        }

        return $customer['cache_object'];
    }

    /**
     * Get the email address of a customer by checking if this one is valid for Klaviyo
     *
     * @param ArrayObject $customer
     * @return string
     * @throws KlaviyoException
     */
    public function getEmail(ArrayObject $customer)
    {
        if (
            !isset($customer['email']) ||
            !$this->validateService->isEmail($customer['email'])
        ) {
            throw new KlaviyoException('Customer email is invalid');
        }

        return $customer['email'];
    }
}
