<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * This file is part of the PrestaShop Enterprise solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Tools\Mcp;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Read-only MCP tools exposing the shop's localisation/shipping configuration.
 *
 * This is the context the assistant needs to answer correctly (which
 * currencies/countries/carriers exist, what the shop defaults are). Languages
 * are intentionally omitted — get_languages already exists in ps_mcp_tools.
 */
class ShopConfigurationTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_currencies',
        description: 'List shop currencies (id, name, iso code, symbol, conversion rate, active).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'configuration']
    )]
    #[PsMcpSchema(
        properties: [
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active currencies (default true)'],
        ],
        required: []
    )]
    public function getCurrencies(bool $activeOnly = true): array
    {
        try {
            $currencies = \Currency::getCurrencies(false, $activeOnly);

            return ['content' => is_array($currencies) ? $currencies : []];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_countries',
        description: 'List countries known to the shop (id, name, iso code, call prefix, active). Default limit 50.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'configuration']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active countries (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getCountries(?int $langId = null, bool $activeOnly = true, int $limit = 50): array
    {
        try {
            $countries = \Country::getCountries($this->resolveLangId($langId), $activeOnly);
            $countries = is_array($countries) ? array_values($countries) : [];

            return ['content' => array_slice($countries, 0, $this->clampLimit($limit))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_carriers',
        description: 'List shipping carriers available to the shop (id, name, active).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'configuration']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active carriers (default true)'],
        ],
        required: []
    )]
    public function getCarriers(?int $langId = null, bool $activeOnly = true): array
    {
        try {
            $carriers = \Carrier::getCarriers($this->resolveLangId($langId), $activeOnly);

            return ['content' => is_array($carriers) ? $carriers : []];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_zones',
        description: 'List geographic zones used for shipping (id, name, active).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'configuration']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function getZones(): array
    {
        try {
            $zones = \Zone::getZones();

            return ['content' => is_array($zones) ? $zones : []];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_shop_info',
        description: 'Get high-level shop information: name, PrestaShop version, and default language/currency/country.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'configuration']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function getShopInfo(): array
    {
        try {
            $defaultLang = new \Language((int) \Configuration::get('PS_LANG_DEFAULT'));
            $defaultCurrency = new \Currency((int) \Configuration::get('PS_CURRENCY_DEFAULT'));
            $defaultCountry = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'), (int) $defaultLang->id);

            return ['content' => [
                'shop_name' => (string) \Configuration::get('PS_SHOP_NAME'),
                'shop_email' => (string) \Configuration::get('PS_SHOP_EMAIL'),
                'prestashop_version' => _PS_VERSION_,
                'default_language' => ['id' => (int) $defaultLang->id, 'iso_code' => $defaultLang->iso_code],
                'default_currency' => ['id' => (int) $defaultCurrency->id, 'iso_code' => $defaultCurrency->iso_code],
                'default_country' => ['id' => (int) $defaultCountry->id, 'iso_code' => $defaultCountry->iso_code, 'name' => $defaultCountry->name],
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
