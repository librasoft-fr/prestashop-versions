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
 * MCP tools to query orders by status/carrier/payment, prepare shipping data and
 * change order status in bulk.
 *
 * Amounts are normalised to the shop default currency (divided by each order's
 * conversion_rate) and scoped to the current shop. bulk_update_order_status is a
 * write op (routes through approval) and changes status without sending the
 * customer notification email.
 */
class OrderTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_orders_by_status',
        description: 'List orders, optionally filtered to one status (pass stateId), newest first, over a date range (default last 30 days). Use the back-office order statuses (e.g. Awaiting payment, Processing, Shipped, Delivered, Cancelled). Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'stateId' => ['type' => ['integer', 'null'], 'description' => 'Order status id to filter by (optional)'],
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id for status names (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getOrdersByStatus(?int $stateId = null, ?string $dateFrom = null, ?string $dateTo = null, ?int $langId = null, int $limit = 20): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('o.id_order, o.reference, o.id_customer, o.date_add, o.current_state, osl.name AS status')
                ->select('(o.total_paid_real / o.conversion_rate) AS total_paid')
                ->from('orders', 'o')
                ->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = o.current_state AND osl.id_lang = ' . (int) $idLang)
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'");
            if ($stateId !== null && $stateId > 0) {
                $query->where('o.current_state = ' . (int) $stateId);
            }
            $query->orderBy('o.date_add DESC')->limit($this->clampLimit($limit));

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'orders' => array_map(static function (array $r): array {
                    return [
                        'id_order' => (int) $r['id_order'],
                        'reference' => (string) $r['reference'],
                        'id_customer' => (int) $r['id_customer'],
                        'date_add' => (string) $r['date_add'],
                        'status' => (string) ($r['status'] ?? ''),
                        'total_paid' => round((float) $r['total_paid'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_unpaid_orders',
        description: 'List orders where the amount actually paid is less than the amount due (outstanding balance), newest first, with the amount still due. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id for status names (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getUnpaidOrders(?int $langId = null, int $limit = 20): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('o.id_order, o.reference, o.id_customer, o.date_add, osl.name AS status')
                ->select('(o.total_paid / o.conversion_rate) AS total_due')
                ->select('(o.total_paid_real / o.conversion_rate) AS total_paid')
                ->select('((o.total_paid - o.total_paid_real) / o.conversion_rate) AS amount_due')
                ->from('orders', 'o')
                ->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = o.current_state AND osl.id_lang = ' . (int) $idLang)
                ->where('o.id_shop = ' . (int) $idShop)
                ->where('o.total_paid_real < o.total_paid')
                ->orderBy('o.date_add DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'currency_iso' => $this->defaultCurrencyIso(),
                'orders' => array_map(static function (array $r): array {
                    return [
                        'id_order' => (int) $r['id_order'],
                        'reference' => (string) $r['reference'],
                        'id_customer' => (int) $r['id_customer'],
                        'date_add' => (string) $r['date_add'],
                        'status' => (string) ($r['status'] ?? ''),
                        'total_due' => round((float) $r['total_due'], 2),
                        'total_paid' => round((float) $r['total_paid'], 2),
                        'amount_due' => round((float) $r['amount_due'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_orders_by_carrier',
        description: 'Orders grouped by shipping carrier over a date range (default last 30 days): order count and revenue per carrier, highest revenue first. Amounts in the shop default currency.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d" (default 30 days ago)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d" (default now)'],
        ],
        required: []
    )]
    public function getOrdersByCarrier(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);
            $idShop = $this->resolveShopId(null);

            $query = new \DbQuery();
            $query->select('o.id_carrier, c.name')
                ->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS revenue')
                ->from('orders', 'o')
                ->leftJoin('carrier', 'c', 'c.id_carrier = o.id_carrier')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->where("o.date_add BETWEEN '" . $from . "' AND '" . $to . "'")
                ->groupBy('o.id_carrier')
                ->orderBy('revenue DESC');

            return ['content' => [
                'date_from' => $from,
                'date_to' => $to,
                'currency_iso' => $this->defaultCurrencyIso(),
                'carriers' => array_map(static function (array $r): array {
                    return [
                        'id_carrier' => (int) $r['id_carrier'],
                        'name' => (string) ($r['name'] ?? ''),
                        'orders_count' => (int) $r['orders_count'],
                        'revenue' => round((float) $r['revenue'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'generate_shipping_labels',
        description: 'Return the data needed to print shipping labels for the given orders: reference, recipient, delivery address, country, phone, carrier and total weight. This returns structured data, not a rendered label/PDF.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'orderIds' => ['type' => 'array', 'description' => 'Order ids to prepare labels for', 'items' => ['type' => 'integer']],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id for country names (defaults to the current back-office language)'],
        ],
        required: ['orderIds']
    )]
    public function generateShippingLabels(array $orderIds, ?int $langId = null): array
    {
        try {
            $ids = array_values(array_filter(array_map('intval', $orderIds), static fn (int $id): bool => $id > 0));
            if ($ids === []) {
                throw new \InvalidArgumentException('Provide at least one order id.');
            }
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('o.id_order, o.reference, o.total_weight')
                ->select('car.name AS carrier')
                ->select('a.firstname, a.lastname, a.company, a.address1, a.address2, a.postcode, a.city, a.phone, a.phone_mobile')
                ->select('cl.name AS country')
                ->from('orders', 'o')
                ->leftJoin('address', 'a', 'a.id_address = o.id_address_delivery')
                ->leftJoin('carrier', 'car', 'car.id_carrier = o.id_carrier')
                ->leftJoin('country_lang', 'cl', 'cl.id_country = a.id_country AND cl.id_lang = ' . (int) $idLang)
                ->where('o.id_order IN (' . implode(',', $ids) . ')')
                ->orderBy('o.id_order ASC');

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_order' => (int) $r['id_order'],
                    'reference' => (string) $r['reference'],
                    'carrier' => (string) ($r['carrier'] ?? ''),
                    'total_weight' => round((float) $r['total_weight'], 3),
                    'recipient' => trim(((string) $r['firstname']) . ' ' . ((string) $r['lastname'])),
                    'company' => (string) ($r['company'] ?? ''),
                    'address' => trim(((string) $r['address1']) . ' ' . ((string) $r['address2'])),
                    'postcode' => (string) ($r['postcode'] ?? ''),
                    'city' => (string) ($r['city'] ?? ''),
                    'country' => (string) ($r['country'] ?? ''),
                    'phone' => (string) ($r['phone'] ?: $r['phone_mobile']),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'bulk_update_order_status',
        description: 'Change the status of several orders at once. Each item: orderId and stateId (target order status). Does not send the customer notification email. Returns the result per order.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'items' => [
                'type' => 'array',
                'description' => 'Order status changes to apply',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'orderId' => ['type' => 'integer', 'description' => 'Order id'],
                        'stateId' => ['type' => 'integer', 'description' => 'Target order status id'],
                    ],
                    'required' => ['orderId', 'stateId'],
                ],
            ],
        ],
        required: ['items']
    )]
    public function bulkUpdateOrderStatus(array $items): array
    {
        try {
            if ($items === []) {
                throw new \InvalidArgumentException('Provide at least one item to update.');
            }

            $results = [];
            $updated = 0;
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['orderId'], $item['stateId'])) {
                    throw new \InvalidArgumentException('Each item needs orderId and stateId.');
                }
                $orderId = (int) $item['orderId'];
                $stateId = (int) $item['stateId'];

                $order = new \Order($orderId);
                if (!\Validate::isLoadedObject($order)) {
                    $results[] = ['id_order' => $orderId, 'updated' => false, 'error' => 'Order not found'];
                    continue;
                }
                $state = new \OrderState($stateId);
                if (!\Validate::isLoadedObject($state)) {
                    $results[] = ['id_order' => $orderId, 'updated' => false, 'error' => 'Order status not found'];
                    continue;
                }

                $history = new \OrderHistory();
                $history->id_order = $orderId;
                $history->changeIdOrderState($stateId, $order);
                $ok = (bool) $history->add();
                if ($ok) {
                    ++$updated;
                }

                $results[] = ['id_order' => $orderId, 'updated' => $ok, 'state_id' => $stateId];
            }

            return ['content' => ['updated' => $updated, 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
