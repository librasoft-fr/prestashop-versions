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
use Context;
use ObjectModel;
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
        $context['id_lang'] = $this->extractId(
            $context,
            $this->context->language,
            'id_lang'
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

        return new ArrayObject($context);
    }

    /**
     * @param array $context
     * @param ObjectModel|null
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
