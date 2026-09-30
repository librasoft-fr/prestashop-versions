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
 * Read-only MCP tools that aggregate shop data into the figures a merchant
 * actually asks about: revenue, best sellers, top customers, catalog health and
 * abandoned carts.
 *
 * All amounts are normalised to the shop default currency (divided by each
 * order's conversion_rate) and revenue counts only validated orders, matching
 * how the back-office Stats dashboard reports turnover. Every query is scoped to
 * the current shop and a date window (last 30 days when the caller omits it).
 */
class AnalyticsTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_sales_summary',
        description: 'Sales KPIs over a date range (default last 30 days): number of valid orders, total revenue and average order value, in the shop default currency.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" or "Y-m-d H:i:s" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" or "Y-m-d H:i:s" (default now)'],
        ],
        required: []
    )]
    public function getSalesSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'");

            $row = $this->rows($query)[0] ?? [];
            $ordersCount = (int) ($row['orders_count'] ?? 0);
            $revenue = (float) ($row['revenue'] ?? 0);

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'orders_count' => $ordersCount,
                'revenue' => round($revenue, 2),
                'average_order_value' => $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0.0,
                'currency_iso' => $this->defaultCurrencyIso(),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_best_selling_products',
        description: 'Top products by quantity sold over a date range (default last 30 days), with revenue per product. Default limit 10.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getBestSellingProducts(?string $dateFrom = null, ?string $dateTo = null, int $limit = 10): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('od.product_id, od.product_name')
                ->select('SUM(od.product_quantity) AS quantity_sold')
                ->select('SUM(od.total_price_tax_excl / o.conversion_rate) AS revenue')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('od.product_id')
                ->orderBy('quantity_sold DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_product' => (int) $r['product_id'],
                    'name' => (string) $r['product_name'],
                    'quantity_sold' => (int) $r['quantity_sold'],
                    'revenue' => round((float) $r['revenue'], 2),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_top_customers',
        description: 'Customers ranked by total spend over a date range (default last 30 days), with their order count. Default limit 10.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getTopCustomers(?string $dateFrom = null, ?string $dateTo = null, int $limit = 10): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('o.id_customer, c.firstname, c.lastname')
                ->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS total_spent')
                ->from('orders', 'o')
                ->innerJoin('customer', 'c', 'c.id_customer = o.id_customer')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('o.id_customer')
                ->orderBy('total_spent DESC')
                ->limit($this->clampLimit($limit));

            // Customer name is returned for context; email and other PII are not.
            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_customer' => (int) $r['id_customer'],
                    'name' => trim(((string) $r['firstname']) . ' ' . ((string) $r['lastname'])),
                    'orders_count' => (int) $r['orders_count'],
                    'total_spent' => round((float) $r['total_spent'], 2),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_catalog_health',
        description: 'Catalog health snapshot for the current shop: total/active/disabled products, out-of-stock count, products with no image and products with an empty description.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function getCatalogHealth(): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId(null);

            $counts = new \DbQuery();
            $counts->select('COUNT(*) AS total')
                ->select('SUM(ps.active = 1) AS active')
                ->select('SUM(ps.active = 0) AS disabled')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop);
            $countsRow = $this->rows($counts)[0] ?? [];

            $outOfStock = new \DbQuery();
            $outOfStock->select('COUNT(DISTINCT sa.id_product) AS nb')
                ->from('stock_available', 'sa')
                ->where('sa.quantity <= 0')
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)');

            $noImage = new \DbQuery();
            $noImage->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where('ps.id_product NOT IN (SELECT i.id_product FROM ' . _DB_PREFIX_ . 'image i)');

            $noDescription = new \DbQuery();
            $noDescription->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->innerJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_shop = ps.id_shop AND pl.id_lang = ' . (int) $idLang)
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where("pl.description = '' OR pl.description IS NULL");

            return ['content' => [
                'total_products' => (int) ($countsRow['total'] ?? 0),
                'active_products' => (int) ($countsRow['active'] ?? 0),
                'disabled_products' => (int) ($countsRow['disabled'] ?? 0),
                'out_of_stock_products' => (int) (($this->rows($outOfStock)[0] ?? [])['nb'] ?? 0),
                'products_without_image' => (int) (($this->rows($noImage)[0] ?? [])['nb'] ?? 0),
                'products_without_description' => (int) (($this->rows($noDescription)[0] ?? [])['nb'] ?? 0),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_abandoned_carts',
        description: 'Carts created in a date range (default last 30 days) that never converted to an order, with item count and estimated tax-excluded value. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getAbandonedCarts(?string $dateFrom = null, ?string $dateTo = null, int $limit = 20): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('ca.id_cart, ca.id_customer, ca.date_add, ca.date_upd')
                ->select('COALESCE(SUM(cp.quantity), 0) AS total_items')
                ->select('COALESCE(SUM(cp.quantity * ps.price), 0) AS estimated_value_tax_excl')
                ->from('cart', 'ca')
                ->leftJoin('orders', 'o', 'o.id_cart = ca.id_cart')
                ->leftJoin('cart_product', 'cp', 'cp.id_cart = ca.id_cart')
                ->leftJoin('product_shop', 'ps', 'ps.id_product = cp.id_product AND ps.id_shop = ca.id_shop')
                ->where('o.id_order IS NULL')
                ->where('ca.id_shop = ' . (int) $idShop)
                ->where("ca.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('ca.id_cart')
                ->having('total_items > 0')
                ->orderBy('ca.date_upd DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_cart' => (int) $r['id_cart'],
                    'id_customer' => (int) $r['id_customer'],
                    'date_add' => (string) $r['date_add'],
                    'date_upd' => (string) $r['date_upd'],
                    'total_items' => (int) $r['total_items'],
                    'estimated_value_tax_excl' => round((float) $r['estimated_value_tax_excl'], 2),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_new_customers',
        description: 'Daily customer registration counts over a date range (default last 30 days), oldest day first.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'analytics']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
        ],
        required: []
    )]
    public function getNewCustomers(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('DATE(c.date_add) AS day')
                ->select('COUNT(*) AS registrations')
                ->from('customer', 'c')
                ->where('c.deleted = 0')
                ->where('c.id_shop = ' . (int) $idShop)
                ->where("c.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('DATE(c.date_add)')
                ->orderBy('day ASC');

            $rows = array_map(static function (array $r): array {
                return ['day' => (string) $r['day'], 'registrations' => (int) $r['registrations']];
            }, $this->rows($query));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'total' => array_sum(array_column($rows, 'registrations')),
                'by_day' => $rows,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
