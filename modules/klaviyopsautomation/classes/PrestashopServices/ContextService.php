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
use Context;
use Country;
use Language;
use ObjectModel;
use Shop;
use Validate;

class ContextService
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Normalizing data from PrestaShop allows to use them in a uniform and secure way.
     * To use the methods of this class, start by normalizing data.
     * You can get the current context without passing $context
     *
     * @param array $context
     * @return ArrayObject
     */
    public function normalize($context = [])
    {
        $context['id_shop'] = $this->extractId(
            $context,
            $this->context->shop,
            'id_shop'
        );
        $context['id_shop_group'] = 0; // Getting id_shop_group must be done with id_shop
        $context['id_lang'] = $this->extractId(
            $context,
            $this->context->language,
            'id_lang'
        );
        // The Country from Context can be wrong for some reasons (ex: Cloudflare)
        // A customer can have multiples address with different countries.
        // In this case, how to decide what is the which is the most relevant address ?
        // It is for this reason that currently, we get country only for the order event
        // Indeed, using the delivery address when an order is validated is the best way to retrieve the most relevant address
        $context['id_country'] = $this->extractId(
            $context,
            null, // id_country will always pull from the provided context
            'id_country'
        );
        $context['id_customer'] = $this->extractId(
            $context,
            $this->context->customer,
            'id_customer'
        );
        $context['id_currency'] = $this->extractId(
            $context,
            $this->context->currency,
            'id_currency'
        );
        // Using Configuration::getGlobalValue provides the same data regardless of the context
        // This allows to have normalized data because id_default_lang will be stable
        // Therefore it avoid to have different data depending on the context for the same thing
        $context['id_default_lang'] = (int) Configuration::getGlobalValue(
            'PS_LANG_DEFAULT'
        );

        if ($context['id_shop'] !== 0) {
            $shop = new Shop($context['id_shop']);

            $context['cache_shop'] = $shop;
            $context['id_shop_group'] = $shop->id_shop_group;
        }

        return new ArrayObject($context);
    }

    /**
     * @return bool
     */
    public function hasMultishop()
    {
        return Shop::isFeatureActive();
    }

    /**
     * @param array $options
     * @return Shop[]
     */
    public function getAllShops($options = [])
    {
        if (!isset($options['active'])) {
            $options['active'] = true;
        }

        if (!isset($options['id_shop_group'])) {
            $options['id_shop_group'] = null;
        }

        $result = [];
        $temp = Shop::getShops(
            $options['active'],
            ($options['id_shop_group'] !== null) ? $options['id_shop_group'] : null,
            true
        );

        foreach ($temp as $idShop) {
            $o = new Shop((int) $idShop);

            if (!Validate::isLoadedObject($o)) {
                continue;
            }

            $result[] = $o;
        }

        return $result;
    }

    /**
     * @return Language|null
     */
    public function getLang(ArrayObject $context)
    {
        if (!$context->offsetExists('cache_lang')) {
            $result = new Language($context['id_lang']);

            if (!Validate::isLoadedObject($result)) {
                $result = null;
            }

            $context['cache_lang'] = $result;
        }

        return $context['cache_lang'];
    }

    /**
     * Currently country is defined only on Order event
     *
     * @return Country|null
     */
    public function getCountry(ArrayObject $context)
    {
        if (!$context->offsetExists('cache_country')) {
            $result = new Country($context['id_country']);

            if (!Validate::isLoadedObject($result)) {
                $result = null;
            }

            $context['cache_country'] = $result;
        }

        return $context['cache_country'];
    }

    /**
     * @param array $context
     * @param ObjectModel|null $object
     * @param string $column
     * @return int
     */
    protected function extractId(
        $context,
        $object,
        $column
    ) {
        if (
            !isset($context[$column]) ||
            $context[$column] <= 0
        ) {
            $id = 0;
            if (Validate::isLoadedObject($object)) {
                $id = $object->id;
            }

            $context[$column] = $id;
        }

        return (int) $context[$column];
    }
}
