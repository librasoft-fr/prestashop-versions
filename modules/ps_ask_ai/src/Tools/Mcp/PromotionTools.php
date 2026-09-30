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
 * MCP tools that measure and generate promotions.
 *
 * get_discount_performance reads actual voucher usage from `order_cart_rule`;
 * create_bulk_discount_codes is a write op (routes through approval) that mints
 * many CartRules for a campaign. get_products_not_in_promotions looks at
 * product-level catalog specific prices only (it does not resolve every possible
 * cart-rule reduction), which is documented on the tool.
 */
class PromotionTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_discount_performance',
        description: 'How each discount voucher performed over a date range (default last 30 days): times used, total discount given and revenue of the orders that used it, most-used first. Amounts in the shop default currency. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'promotions']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getDiscountPerformance(?string $dateFrom = null, ?string $dateTo = null, int $limit = 20): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('ocr.id_cart_rule, cr.code')
                ->select('MAX(ocr.name) AS name')
                ->select('COUNT(DISTINCT ocr.id_order) AS times_used')
                ->select('SUM(ocr.value / o.conversion_rate) AS total_discount')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('order_cart_rule', 'ocr')
                ->innerJoin('orders', 'o', 'o.id_order = ocr.id_order')
                ->leftJoin('cart_rule', 'cr', 'cr.id_cart_rule = ocr.id_cart_rule')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('ocr.id_cart_rule')
                ->orderBy('times_used DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'vouchers' => array_map(static function (array $r): array {
                    return [
                        'id_cart_rule' => (int) $r['id_cart_rule'],
                        'code' => (string) ($r['code'] ?? ''),
                        'name' => (string) ($r['name'] ?? ''),
                        'times_used' => (int) $r['times_used'],
                        'total_discount' => round((float) $r['total_discount'], 2),
                        'revenue' => round((float) $r['revenue'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_not_in_promotions',
        description: 'List active products that currently have no product-level catalog specific price (reduction) in effect, so you can spot items never put on promotion. Note: this checks catalog specific prices, not every possible cart-rule reduction. Default limit 50.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'promotions']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getProductsNotInPromotions(?int $langId = null, int $limit = 50): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $activeSpecificPrice = 'SELECT sp.id_product FROM ' . _DB_PREFIX_ . 'specific_price sp'
                . ' WHERE sp.id_product > 0'
                . " AND (sp.`from` = '0000-00-00 00:00:00' OR sp.`from` <= NOW())"
                . " AND (sp.`to` = '0000-00-00 00:00:00' OR sp.`to` >= NOW())";

            $query = new \DbQuery();
            $query->select('ps.id_product, p.reference, p.price, pl.name')
                ->from('product_shop', 'ps')
                ->innerJoin('product', 'p', 'p.id_product = ps.id_product')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ps.id_shop')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where('ps.active = 1')
                ->where('ps.id_product NOT IN (' . $activeSpecificPrice . ')')
                ->orderBy('ps.id_product ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_product' => (int) $r['id_product'],
                    'reference' => (string) ($r['reference'] ?? ''),
                    'name' => (string) ($r['name'] ?? ''),
                    'price' => round((float) $r['price'], 2),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_bulk_discount_codes',
        description: 'Generate several single-use discount vouchers for a campaign, each with a unique code (prefix + random suffix) and the same reduction and validity. Provide at least one of reductionPercent, reductionAmount or freeShipping.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'promotions']
    )]
    #[PsMcpSchema(
        properties: [
            'count' => ['type' => 'integer', 'description' => 'How many codes to create, 1-100'],
            'prefix' => ['type' => 'string', 'description' => 'Code prefix, e.g. "SUMMER" (a random suffix is appended)'],
            'reductionPercent' => ['type' => ['number', 'null'], 'description' => 'Percentage discount, e.g. 10 for 10%'],
            'reductionAmount' => ['type' => ['number', 'null'], 'description' => 'Fixed amount discount in the shop default currency'],
            'freeShipping' => ['type' => 'boolean', 'description' => 'Grant free shipping (default false)'],
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d H:i:s", or "Y-m-d" for the start of that day (default now)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d H:i:s", or "Y-m-d" for the end of that day (default +1 year)'],
            'minimumAmount' => ['type' => ['number', 'null'], 'description' => 'Minimum cart total required to use a voucher'],
        ],
        required: ['count', 'prefix']
    )]
    public function createBulkDiscountCodes(
        int $count,
        string $prefix,
        ?float $reductionPercent = null,
        ?float $reductionAmount = null,
        bool $freeShipping = false,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?float $minimumAmount = null,
    ): array {
        try {
            if ($reductionPercent === null && $reductionAmount === null && !$freeShipping) {
                throw new \InvalidArgumentException('Provide at least one of reductionPercent, reductionAmount or freeShipping.');
            }
            $count = max(1, min($count, (int) self::MAX_LIMIT));
            $prefix = trim($prefix);
            if ($prefix === '') {
                throw new \InvalidArgumentException('A code prefix is required.');
            }

            $from = $this->normaliseDate($dateFrom, date('Y-m-d H:i:s'), false);
            $to = $this->normaliseDate($dateTo, date('Y-m-d H:i:s', strtotime('+1 year')), true);
            $idCurrency = (int) \Configuration::get('PS_CURRENCY_DEFAULT');

            $created = [];
            for ($i = 0; $i < $count; ++$i) {
                $code = $prefix . '-' . \Tools::strtoupper(\Tools::passwdGen(8, 'NO_NUMERIC'));

                $cartRule = new \CartRule();
                $cartRule->name = $this->multilangValue($prefix . ' campaign');
                $cartRule->code = $code;
                $cartRule->free_shipping = $freeShipping;
                $cartRule->date_from = $from;
                $cartRule->date_to = $to;
                $cartRule->quantity = 1;
                $cartRule->quantity_per_user = 1;
                $cartRule->highlight = true;
                $cartRule->partial_use = true;
                $cartRule->active = true;

                if ($reductionPercent !== null) {
                    $cartRule->reduction_percent = $reductionPercent;
                }
                if ($reductionAmount !== null) {
                    $cartRule->reduction_amount = $reductionAmount;
                    $cartRule->reduction_tax = true;
                    $cartRule->reduction_currency = $idCurrency;
                }
                if ($minimumAmount !== null) {
                    $cartRule->minimum_amount = $minimumAmount;
                    $cartRule->minimum_amount_currency = $idCurrency;
                }

                if ($cartRule->add()) {
                    $created[] = ['id_cart_rule' => (int) $cartRule->id, 'code' => $code];
                }
            }

            return ['content' => ['created' => count($created), 'codes' => $created]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
