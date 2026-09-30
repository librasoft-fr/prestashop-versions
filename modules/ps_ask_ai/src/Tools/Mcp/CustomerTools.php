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
 * MCP tools to look up customers, analyse their value and manage them.
 *
 * The lookup/history tools return contact details (email) only when the caller
 * explicitly identifies a customer; the value-ranking tools stay aggregate and
 * name-only, matching how AnalyticsTools::getTopCustomers handles PII. Spend is
 * computed from validated orders, normalised to the shop default currency.
 *
 * "Segments" map to PrestaShop customer Groups: PrestaShop has no free-form
 * customer tagging, and Groups are the native way to bucket customers and later
 * target them with prices, vouchers and emails.
 */
class CustomerTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_customer_by_email',
        description: 'Find a customer account by exact email address. Returns account id, name, company, registration date and newsletter/active flags.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'email' => ['type' => 'string', 'description' => 'Exact customer email address'],
        ],
        required: ['email']
    )]
    public function getCustomerByEmail(string $email): array
    {
        try {
            $email = trim($email);
            if ($email === '' || !\Validate::isEmail($email)) {
                throw new \InvalidArgumentException('A valid email address is required.');
            }

            $query = new \DbQuery();
            $query->select('c.id_customer, c.firstname, c.lastname, c.email, c.company, c.date_add, c.newsletter, c.active')
                ->from('customer', 'c')
                ->where('c.deleted = 0')
                ->where("c.email = '" . pSQL($email) . "'")
                ->orderBy('c.id_customer ASC');

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_customer' => (int) $r['id_customer'],
                    'name' => trim(((string) $r['firstname']) . ' ' . ((string) $r['lastname'])),
                    'email' => (string) $r['email'],
                    'company' => (string) ($r['company'] ?? ''),
                    'date_add' => (string) $r['date_add'],
                    'newsletter' => (bool) $r['newsletter'],
                    'active' => (bool) $r['active'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_inactive_customers',
        description: 'List customers who placed at least one valid order in the past but none in the last N days (lapsed buyers), most-recently-active first. Default 90 days, limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'days' => ['type' => 'integer', 'description' => 'Inactivity window in days (default 90)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getInactiveCustomers(int $days = 90, int $limit = 20): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $days = max(1, $days);
            $cutoff = date('Y-m-d 00:00:00', (int) strtotime('-' . $days . ' days'));

            // Inner join keeps only customers who actually ordered; HAVING then
            // drops anyone whose latest valid order is within the window.
            $query = new \DbQuery();
            $query->select('c.id_customer, c.firstname, c.lastname')
                ->select('COUNT(o.id_order) AS orders_count')
                ->select('MAX(o.date_add) AS last_order_date')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS total_spent')
                ->from('customer', 'c')
                ->innerJoin('orders', 'o', 'o.id_customer = c.id_customer AND o.valid = 1 AND o.id_shop = ' . (int) $idShop)
                ->where('c.deleted = 0')
                ->groupBy('c.id_customer')
                ->having("MAX(o.date_add) < '" . pSQL($cutoff) . "'")
                ->orderBy('last_order_date DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'inactive_since' => $cutoff,
                'currency_iso' => $this->defaultCurrencyIso(),
                'customers' => array_map(static function (array $r): array {
                    return [
                        'id_customer' => (int) $r['id_customer'],
                        'name' => trim(((string) $r['firstname']) . ' ' . ((string) $r['lastname'])),
                        'orders_count' => (int) $r['orders_count'],
                        'last_order_date' => (string) $r['last_order_date'],
                        'total_spent' => round((float) $r['total_spent'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_customer_order_history',
        description: 'List a customer\'s orders (reference, date, current status, total paid), newest first. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'customerId' => ['type' => 'integer', 'description' => 'Customer id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id for status names (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: ['customerId']
    )]
    public function getCustomerOrderHistory(int $customerId, ?int $langId = null, int $limit = 20): array
    {
        try {
            $idLang = $this->resolveLangId($langId);
            $query = new \DbQuery();
            $query->select('o.id_order, o.reference, o.date_add, o.valid, o.current_state, osl.name AS status')
                ->select('(o.total_paid_real / o.conversion_rate) AS total_paid')
                ->from('orders', 'o')
                ->leftJoin('order_state_lang', 'osl', 'osl.id_order_state = o.current_state AND osl.id_lang = ' . (int) $idLang)
                ->where('o.id_customer = ' . (int) $customerId)
                ->orderBy('o.date_add DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'id_customer' => $customerId,
                'currency_iso' => $this->defaultCurrencyIso(),
                'orders' => array_map(static function (array $r): array {
                    return [
                        'id_order' => (int) $r['id_order'],
                        'reference' => (string) $r['reference'],
                        'date_add' => (string) $r['date_add'],
                        'status' => (string) ($r['status'] ?? ''),
                        'valid' => (bool) $r['valid'],
                        'total_paid' => round((float) $r['total_paid'], 2),
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_customer_lifetime_value',
        description: 'Lifetime spend for a single customer (pass customerId) or the top spenders overall (omit it). Returns order count, total spent, average order value and first/last order dates, from validated orders in the shop default currency. Default limit 10.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'customerId' => ['type' => ['integer', 'null'], 'description' => 'Restrict to one customer; omit to rank the top spenders'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows when ranking, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getCustomerLifetimeValue(?int $customerId = null, int $limit = 10): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $query = new \DbQuery();
            $query->select('o.id_customer, c.firstname, c.lastname')
                ->select('COUNT(*) AS orders_count')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS total_spent')
                ->select('MIN(o.date_add) AS first_order_date')
                ->select('MAX(o.date_add) AS last_order_date')
                ->from('orders', 'o')
                ->innerJoin('customer', 'c', 'c.id_customer = o.id_customer')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->groupBy('o.id_customer');

            if ($customerId !== null && $customerId > 0) {
                $query->where('o.id_customer = ' . (int) $customerId);
            } else {
                $query->orderBy('total_spent DESC')->limit($this->clampLimit($limit));
            }

            return ['content' => [
                'currency_iso' => $this->defaultCurrencyIso(),
                'customers' => array_map(static function (array $r): array {
                    $orders = (int) $r['orders_count'];
                    $spent = (float) $r['total_spent'];

                    return [
                        'id_customer' => (int) $r['id_customer'],
                        'name' => trim(((string) $r['firstname']) . ' ' . ((string) $r['lastname'])),
                        'orders_count' => $orders,
                        'total_spent' => round($spent, 2),
                        'average_order_value' => $orders > 0 ? round($spent / $orders, 2) : 0.0,
                        'first_order_date' => (string) $r['first_order_date'],
                        'last_order_date' => (string) $r['last_order_date'],
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_high_value_customer_threshold',
        description: 'Suggest VIP/loyalty spend thresholds from the lifetime-spend distribution: the minimum total spent to be in the top 5%, 10% and 20% of buyers, plus the average and maximum. Use this to set loyalty tiers.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(properties: [], required: [])]
    public function getHighValueCustomerThreshold(): array
    {
        try {
            $idShop = $this->resolveShopId(null);

            $countRow = $this->rows((new \DbQuery())
                ->select('COUNT(DISTINCT o.id_customer) AS nb')
                ->select('SUM(o.total_paid_real / o.conversion_rate) AS total_spent')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop))[0] ?? [];
            $buyers = (int) ($countRow['nb'] ?? 0);
            $totalSpent = (float) ($countRow['total_spent'] ?? 0);

            $thresholds = [];
            foreach ([5, 10, 20] as $pct) {
                $thresholds['top_' . $pct . '_percent'] = $buyers > 0
                    ? round($this->spendAtTopPercentile($idShop, $buyers, $pct), 2)
                    : 0.0;
            }

            return ['content' => array_merge([
                'currency_iso' => $this->defaultCurrencyIso(),
                'buyers' => $buyers,
                // Average of per-customer totals == total revenue / number of buyers.
                'average_spent' => $buyers > 0 ? round($totalSpent / $buyers, 2) : 0.0,
                // The top spender is the first row when ranked descending (offset 0).
                'max_spent' => $buyers > 0 ? round($this->spendAtTopPercentile($idShop, $buyers, 0), 2) : 0.0,
            ], $thresholds)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_customer_info',
        description: 'Update a customer account\'s details (name, company, SIRET, private note, newsletter/optin/active flags). Only the provided fields change. Postal addresses are separate objects and are not updated here.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'customerId' => ['type' => 'integer', 'description' => 'Customer id'],
            'firstname' => ['type' => ['string', 'null'], 'description' => 'New first name'],
            'lastname' => ['type' => ['string', 'null'], 'description' => 'New last name'],
            'company' => ['type' => ['string', 'null'], 'description' => 'New company name'],
            'siret' => ['type' => ['string', 'null'], 'description' => 'New SIRET number'],
            'note' => ['type' => ['string', 'null'], 'description' => 'Private back-office note'],
            'newsletter' => ['type' => ['boolean', 'null'], 'description' => 'Newsletter subscription'],
            'optin' => ['type' => ['boolean', 'null'], 'description' => 'Partner offers opt-in'],
            'active' => ['type' => ['boolean', 'null'], 'description' => 'Whether the account is enabled'],
        ],
        required: ['customerId']
    )]
    public function updateCustomerInfo(
        int $customerId,
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $company = null,
        ?string $siret = null,
        ?string $note = null,
        ?bool $newsletter = null,
        ?bool $optin = null,
        ?bool $active = null,
    ): array {
        try {
            $customer = new \Customer($customerId);
            if (!\Validate::isLoadedObject($customer)) {
                throw new \RuntimeException(sprintf('Customer %d not found.', $customerId));
            }

            if ($firstname !== null) {
                $customer->firstname = $firstname;
            }
            if ($lastname !== null) {
                $customer->lastname = $lastname;
            }
            if ($company !== null) {
                $customer->company = $company;
            }
            if ($siret !== null) {
                $customer->siret = $siret;
            }
            if ($note !== null) {
                $customer->note = $note;
            }
            if ($newsletter !== null) {
                $customer->newsletter = $newsletter;
            }
            if ($optin !== null) {
                $customer->optin = $optin;
            }
            if ($active !== null) {
                $customer->active = $active;
            }

            if (!$customer->update()) {
                throw new \RuntimeException('Failed to update the customer.');
            }

            return ['content' => ['id_customer' => (int) $customer->id, 'updated' => true]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_customer_segment',
        description: 'Create a customer Group (PrestaShop\'s native segment) and assign every customer matching the given criteria (minimum lifetime spend and/or minimum number of valid orders). Returns the new group id and how many customers were added.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'customers']
    )]
    #[PsMcpSchema(
        properties: [
            'name' => ['type' => 'string', 'description' => 'Name of the segment/group, e.g. "VIP"'],
            'minSpend' => ['type' => ['number', 'null'], 'description' => 'Minimum lifetime spend (shop default currency) to include a customer'],
            'minOrders' => ['type' => ['integer', 'null'], 'description' => 'Minimum number of valid orders to include a customer'],
        ],
        required: ['name']
    )]
    public function createCustomerSegment(string $name, ?float $minSpend = null, ?int $minOrders = null): array
    {
        try {
            $name = trim($name);
            if ($name === '') {
                throw new \InvalidArgumentException('A segment name is required.');
            }
            if ($minSpend === null && $minOrders === null) {
                throw new \InvalidArgumentException('Provide at least one of minSpend or minOrders.');
            }

            $idShop = $this->resolveShopId(null);

            $group = new \Group();
            $group->name = $this->multilangValue($name);
            $group->reduction = '0';
            $group->price_display_method = (int) \Configuration::get('PRICE_DISPLAY_METHOD');
            $group->show_prices = true;
            if (!$group->add()) {
                throw new \RuntimeException('Failed to create the customer group.');
            }
            $groupId = (int) $group->id;

            $match = new \DbQuery();
            $match->select('o.id_customer')
                ->from('orders', 'o')
                ->where('o.valid = 1')
                ->where('o.id_shop = ' . (int) $idShop)
                ->groupBy('o.id_customer');
            if ($minSpend !== null) {
                $match->having('SUM(o.total_paid_real / o.conversion_rate) >= ' . (float) $minSpend);
            }
            if ($minOrders !== null) {
                $match->having('COUNT(*) >= ' . (int) max(1, $minOrders));
            }

            $assigned = 0;
            foreach ($this->rows($match) as $row) {
                $customer = new \Customer((int) $row['id_customer']);
                if (!\Validate::isLoadedObject($customer)) {
                    continue;
                }
                $customer->addGroups([$groupId]);
                ++$assigned;
            }

            return ['content' => [
                'id_group' => $groupId,
                'name' => $name,
                'customers_assigned' => $assigned,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Return the lifetime spend at the boundary of the top $pct percent of
     * buyers — i.e. the minimum spend required to be in that tier.
     */
    private function spendAtTopPercentile(int $idShop, int $buyers, int $pct): float
    {
        $rank = (int) ceil($buyers * $pct / 100);
        $offset = max(0, $rank - 1);

        $query = new \DbQuery();
        $query->select('SUM(o.total_paid_real / o.conversion_rate) AS spent')
            ->from('orders', 'o')
            ->where('o.valid = 1')
            ->where('o.id_shop = ' . (int) $idShop)
            ->groupBy('o.id_customer')
            ->orderBy('spent DESC')
            ->limit(1, $offset);

        return (float) (($this->rows($query)[0] ?? [])['spent'] ?? 0);
    }
}
