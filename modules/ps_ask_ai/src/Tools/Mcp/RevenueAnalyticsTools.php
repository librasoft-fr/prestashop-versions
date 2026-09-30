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
 * Read-only MCP tools that break revenue down along the dimensions a merchant
 * reasons about: product category, brand, time, tax rate and refunds.
 *
 * Like AnalyticsTools, every amount is normalised to the shop default currency
 * (divided by each order's conversion_rate), revenue counts only validated
 * orders (o.valid = 1), and queries are scoped to the current shop and a date
 * window (last 30 days when the caller omits it).
 */
class RevenueAnalyticsTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_revenue_by_category',
        description: 'Revenue and units sold grouped by product default category over a date range (default last 30 days), highest revenue first. Amounts are tax-excluded and in the shop default currency. Default limit 20.',
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
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getRevenueByCategory(?string $dateFrom = null, ?string $dateTo = null, ?int $langId = null, int $limit = 20): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            // Group by the product's default category to avoid double-counting a
            // product that belongs to several categories.
            $query = new \DbQuery();
            $query->select('p.id_category_default AS id_category, cl.name')
                ->select('SUM(od.product_quantity) AS quantity_sold')
                ->select('SUM(od.total_price_tax_excl / o.conversion_rate) AS revenue')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
                ->innerJoin('product', 'p', 'p.id_product = od.product_id')
                ->leftJoin('category_lang', 'cl', 'cl.id_category = p.id_category_default AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . (int) $idShop)
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('p.id_category_default')
                ->orderBy('revenue DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'categories' => array_map(static function (array $r): array {
                    return [
                        'id_category' => (int) $r['id_category'],
                        'name' => (string) ($r['name'] ?? ''),
                        'quantity_sold' => (int) $r['quantity_sold'],
                        'revenue' => round((float) $r['revenue'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_revenue_by_manufacturer',
        description: 'Revenue and units sold grouped by brand/manufacturer over a date range (default last 30 days), highest revenue first. Products with no brand are grouped under id 0. Amounts are tax-excluded, in the shop default currency. Default limit 20.',
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
    public function getRevenueByManufacturer(?string $dateFrom = null, ?string $dateTo = null, int $limit = 20): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('p.id_manufacturer, m.name')
                ->select('SUM(od.product_quantity) AS quantity_sold')
                ->select('SUM(od.total_price_tax_excl / o.conversion_rate) AS revenue')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
                ->innerJoin('product', 'p', 'p.id_product = od.product_id')
                ->leftJoin('manufacturer', 'm', 'm.id_manufacturer = p.id_manufacturer')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('p.id_manufacturer')
                ->orderBy('revenue DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'manufacturers' => array_map(static function (array $r): array {
                    return [
                        'id_manufacturer' => (int) $r['id_manufacturer'],
                        'name' => ((int) $r['id_manufacturer'] === 0) ? 'No brand' : (string) ($r['name'] ?? ''),
                        'quantity_sold' => (int) $r['quantity_sold'],
                        'revenue' => round((float) $r['revenue'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_revenue_trend',
        description: 'Revenue and order count bucketed by day, week or month over a date range (default last 30 days), oldest bucket first, to spot growth patterns. Amounts in the shop default currency.',
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
            'granularity' => ['type' => 'string', 'enum' => ['day', 'week', 'month'], 'description' => 'Bucket size: "day" (default), "week" or "month"'],
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
        ],
        required: []
    )]
    public function getRevenueTrend(string $granularity = 'day', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            // Map the granularity to a safe, fixed SQL expression — never
            // interpolate the caller value into the query.
            $bucket = match ($granularity) {
                'week' => "DATE_FORMAT(o.date_add, '%x-W%v')",
                'month' => "DATE_FORMAT(o.date_add, '%Y-%m')",
                default => 'DATE(o.date_add)',
            };

            $query = new \DbQuery();
            $query->select($bucket . ' AS bucket')
                ->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('bucket')
                ->orderBy('bucket ASC');

            $rows = array_map(static function (array $r): array {
                return [
                    'bucket' => (string) $r['bucket'],
                    'orders_count' => (int) $r['orders_count'],
                    'revenue' => round((float) $r['revenue'], 2),
                ];
            }, $this->rows($query));

            return ['content' => [
                'granularity' => in_array($granularity, ['day', 'week', 'month'], true) ? $granularity : 'day',
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'total_revenue' => round((float) array_sum(array_column($rows, 'revenue')), 2),
                'buckets' => $rows,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_refund_analytics',
        description: 'Refund summary over a date range (default last 30 days): number of credit slips, distinct refunded orders, total amount refunded and refund rate vs valid orders. Note: PrestaShop does not store a refund reason, so none is reported.',
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
    public function getRefundAnalytics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            // Credit slips (order_slip) are PrestaShop's record of a refund.
            $slips = new \DbQuery();
            $slips->select('COUNT(*) AS slips_count')
                ->select('COUNT(DISTINCT os.id_order) AS refunded_orders')
                ->select('SUM((os.total_products_tax_incl + os.total_shipping_tax_incl) / o.conversion_rate) AS total_refunded')
                ->from('order_slip', 'os')
                ->innerJoin('orders', 'o', 'o.id_order = os.id_order')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("os.date_add BETWEEN '" . $from . "' AND '" . $to . "'");
            $slipRow = $this->rows($slips)[0] ?? [];

            $orders = new \DbQuery();
            $orders->select('COUNT(*) AS orders_count')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'");
            $ordersCount = (int) (($this->rows($orders)[0] ?? [])['orders_count'] ?? 0);

            $refundedOrders = (int) ($slipRow['refunded_orders'] ?? 0);

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'credit_slips' => (int) ($slipRow['slips_count'] ?? 0),
                'refunded_orders' => $refundedOrders,
                'total_refunded' => round((float) ($slipRow['total_refunded'] ?? 0), 2),
                'valid_orders' => $ordersCount,
                'refund_rate_percent' => $ordersCount > 0 ? round($refundedOrders / $ordersCount * 100, 2) : 0.0,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_tax_report',
        description: 'Tax collected grouped by tax rate over a date range (default last 30 days), for compliance reporting. Amounts normalised to the shop default currency.',
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
    public function getTaxReport(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            // order_detail_tax holds the per-line tax actually charged; join up to
            // the order for currency normalisation and the tax row for the rate.
            $query = new \DbQuery();
            $query->select('t.rate')
                ->select('SUM(odt.total_amount / o.conversion_rate) AS tax_collected')
                ->from('order_detail_tax', 'odt')
                ->innerJoin('order_detail', 'od', 'od.id_order_detail = odt.id_order_detail')
                ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
                ->innerJoin('tax', 't', 't.id_tax = odt.id_tax')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('t.rate')
                ->orderBy('t.rate DESC');

            $rows = array_map(static function (array $r): array {
                return [
                    'rate' => round((float) $r['rate'], 3),
                    'tax_collected' => round((float) $r['tax_collected'], 2),
                ];
            }, $this->rows($query));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'total_tax_collected' => round((float) array_sum(array_column($rows, 'tax_collected')), 2),
                'by_rate' => $rows,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_sales_report',
        description: 'Consolidated sales report over a date range (default last 30 days): headline KPIs, top products and a daily revenue breakdown, optionally also as a CSV string. This returns structured data, not a rendered PDF or charts.',
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
            'topProducts' => ['type' => 'integer', 'description' => 'How many top products to include, 1-100 (default 10)'],
            'includeCsv' => ['type' => 'boolean', 'description' => 'Also return the daily breakdown as a CSV string (default false)'],
        ],
        required: []
    )]
    public function getSalesReport(?string $dateFrom = null, ?string $dateTo = null, int $topProducts = 10, bool $includeCsv = false): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $summary = new \DbQuery();
            $summary->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'");
            $summaryRow = $this->rows($summary)[0] ?? [];
            $ordersCount = (int) ($summaryRow['orders_count'] ?? 0);
            $revenue = (float) ($summaryRow['revenue'] ?? 0);

            $top = new \DbQuery();
            $top->select('od.product_id, od.product_name')
                ->select('SUM(od.product_quantity) AS quantity_sold')
                ->select('SUM(od.total_price_tax_excl / o.conversion_rate) AS revenue')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order = od.id_order')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('od.product_id')
                ->orderBy('revenue DESC')
                ->limit($this->clampLimit($topProducts));
            $topRows = array_map(static function (array $r): array {
                return [
                    'id_product' => (int) $r['product_id'],
                    'name' => (string) $r['product_name'],
                    'quantity_sold' => (int) $r['quantity_sold'],
                    'revenue' => round((float) $r['revenue'], 2),
                ];
            }, $this->rows($top));

            $daily = new \DbQuery();
            $daily->select('DATE(o.date_add) AS day')
                ->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('DATE(o.date_add)')
                ->orderBy('day ASC');
            $dailyRows = array_map(static function (array $r): array {
                return [
                    'day' => (string) $r['day'],
                    'orders_count' => (int) $r['orders_count'],
                    'revenue' => round((float) $r['revenue'], 2),
                ];
            }, $this->rows($daily));

            $content = [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'kpis' => [
                    'orders_count' => $ordersCount,
                    'revenue' => round($revenue, 2),
                    'average_order_value' => $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0.0,
                ],
                'top_products' => $topRows,
                'by_day' => $dailyRows,
            ];

            if ($includeCsv) {
                $content['csv'] = $this->buildDailyCsv($dailyRows);
            }

            return ['content' => $content];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Render the daily breakdown as a small CSV document (header + one row per
     * day). Kept deliberately simple — no external CSV/PDF dependency exists in
     * the MCP front-controller runtime.
     *
     * @param array<int, array{day: string, orders_count: int, revenue: float}> $dailyRows
     */
    private function buildDailyCsv(array $dailyRows): string
    {
        $lines = ['day,orders_count,revenue'];
        foreach ($dailyRows as $row) {
            $lines[] = $row['day'] . ',' . $row['orders_count'] . ',' . $row['revenue'];
        }

        return implode("\n", $lines);
    }
}
