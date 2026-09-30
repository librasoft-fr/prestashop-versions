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
 * MCP tools to read and manage cart rules (discount vouchers).
 *
 * Write tools are NOT read-only, so the assistant must route them through the
 * operator approval flow before they execute.
 */
class CartRuleTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_cart_rules',
        description: 'List discount vouchers (id, name, code, reductions, validity, active). Default limit 10.',
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
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active vouchers (default false)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getCartRules(?int $langId = null, bool $activeOnly = false, int $limit = 10): array
    {
        try {
            $idLang = $this->resolveLangId($langId);
            $query = new \DbQuery();
            $query->select('cr.id_cart_rule, cr.code, cr.reduction_percent, cr.reduction_amount, cr.free_shipping, cr.active, cr.date_from, cr.date_to, crl.name')
                ->from('cart_rule', 'cr')
                ->leftJoin('cart_rule_lang', 'crl', 'crl.id_cart_rule = cr.id_cart_rule AND crl.id_lang = ' . (int) $idLang);
            if ($activeOnly) {
                $query->where('cr.active = 1');
            }
            $query->orderBy('cr.id_cart_rule DESC')->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_cart_rule_by_id',
        description: 'Get a single discount voucher by id with its full configuration.',
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
            'id' => ['type' => 'integer', 'description' => 'Cart rule id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: ['id']
    )]
    public function getCartRuleById(int $id, ?int $langId = null): array
    {
        try {
            $cartRule = new \CartRule($id, $this->resolveLangId($langId));
            if (!\Validate::isLoadedObject($cartRule)) {
                throw new \RuntimeException(sprintf('Cart rule %d not found', $id));
            }

            return ['content' => [
                'id' => (int) $cartRule->id,
                'name' => $cartRule->name,
                'code' => $cartRule->code,
                'reduction_percent' => (float) $cartRule->reduction_percent,
                'reduction_amount' => (float) $cartRule->reduction_amount,
                'free_shipping' => (bool) $cartRule->free_shipping,
                'date_from' => $cartRule->date_from,
                'date_to' => $cartRule->date_to,
                'quantity' => (int) $cartRule->quantity,
                'quantity_per_user' => (int) $cartRule->quantity_per_user,
                'minimum_amount' => (float) $cartRule->minimum_amount,
                'active' => (bool) $cartRule->active,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_cart_rule',
        description: 'Create a discount voucher. Provide at least one of reductionPercent, reductionAmount or freeShipping.',
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
            'name' => ['type' => 'string', 'description' => 'Internal name of the voucher'],
            'code' => ['type' => 'string', 'description' => 'Voucher code customers enter at checkout (optional, leave empty for an automatic rule)'],
            'reductionPercent' => ['type' => ['number', 'null'], 'description' => 'Percentage discount, e.g. 10 for 10%'],
            'reductionAmount' => ['type' => ['number', 'null'], 'description' => 'Fixed amount discount in the shop default currency'],
            'freeShipping' => ['type' => 'boolean', 'description' => 'Grant free shipping (default false)'],
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start date "Y-m-d H:i:s", or "Y-m-d" for the start of that day (default now)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End date "Y-m-d H:i:s", or "Y-m-d" for the end of that day (default +1 year)'],
            'quantity' => ['type' => 'integer', 'description' => 'Total number of times the voucher can be used (default 1)'],
            'quantityPerUser' => ['type' => 'integer', 'description' => 'Uses allowed per customer (default 1)'],
            'minimumAmount' => ['type' => ['number', 'null'], 'description' => 'Minimum cart total required to use the voucher'],
            'active' => ['type' => 'boolean', 'description' => 'Whether the voucher is active (default true)'],
        ],
        required: ['name']
    )]
    public function createCartRule(
        string $name,
        string $code = '',
        ?float $reductionPercent = null,
        ?float $reductionAmount = null,
        bool $freeShipping = false,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $quantity = 1,
        int $quantityPerUser = 1,
        ?float $minimumAmount = null,
        bool $active = true,
    ): array {
        try {
            if ($reductionPercent === null && $reductionAmount === null && !$freeShipping) {
                throw new \InvalidArgumentException('Provide at least one of reductionPercent, reductionAmount or freeShipping.');
            }

            $cartRule = new \CartRule();
            $this->applyCartRuleFields($cartRule, [
                'name' => $name, 'code' => $code, 'reductionPercent' => $reductionPercent,
                'reductionAmount' => $reductionAmount, 'freeShipping' => $freeShipping,
                'dateFrom' => $this->normaliseDate($dateFrom, date('Y-m-d H:i:s'), false),
                'dateTo' => $this->normaliseDate($dateTo, date('Y-m-d H:i:s', strtotime('+1 year')), true),
                'quantity' => $quantity, 'quantityPerUser' => $quantityPerUser,
                'minimumAmount' => $minimumAmount, 'active' => $active,
            ]);

            if (!$cartRule->add()) {
                throw new \RuntimeException('Failed to create the cart rule.');
            }

            return ['content' => ['id' => (int) $cartRule->id, 'code' => $cartRule->code]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_cart_rule_by_id',
        description: 'Update fields of an existing discount voucher. Only the provided fields change.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'promotions']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'description' => 'Cart rule id'],
            'name' => ['type' => ['string', 'null'], 'description' => 'New internal name'],
            'code' => ['type' => ['string', 'null'], 'description' => 'New voucher code'],
            'reductionPercent' => ['type' => ['number', 'null'], 'description' => 'New percentage discount'],
            'reductionAmount' => ['type' => ['number', 'null'], 'description' => 'New fixed amount discount'],
            'active' => ['type' => ['boolean', 'null'], 'description' => 'Enable/disable the voucher'],
        ],
        required: ['id']
    )]
    public function updateCartRuleById(
        int $id,
        ?string $name = null,
        ?string $code = null,
        ?float $reductionPercent = null,
        ?float $reductionAmount = null,
        ?bool $active = null,
    ): array {
        try {
            $cartRule = new \CartRule($id);
            if (!\Validate::isLoadedObject($cartRule)) {
                throw new \RuntimeException(sprintf('Cart rule %d not found', $id));
            }

            if ($name !== null) {
                $cartRule->name = $this->multilangValue($name);
            }
            if ($code !== null) {
                $cartRule->code = $code;
            }
            if ($reductionPercent !== null) {
                $cartRule->reduction_percent = $reductionPercent;
            }
            if ($reductionAmount !== null) {
                $cartRule->reduction_amount = $reductionAmount;
                $cartRule->reduction_currency = (int) \Configuration::get('PS_CURRENCY_DEFAULT');
            }
            if ($active !== null) {
                $cartRule->active = (bool) $active;
            }

            if (!$cartRule->update()) {
                throw new \RuntimeException('Failed to update the cart rule.');
            }

            return ['content' => ['id' => (int) $cartRule->id, 'updated' => true]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Populate a fresh CartRule from the validated create payload.
     *
     * @param array<string, mixed> $data
     */
    private function applyCartRuleFields(\CartRule $cartRule, array $data): void
    {
        $cartRule->name = $this->multilangValue((string) $data['name']);
        $cartRule->code = (string) $data['code'];
        $cartRule->free_shipping = (bool) $data['freeShipping'];
        $cartRule->date_from = (string) $data['dateFrom'];
        $cartRule->date_to = (string) $data['dateTo'];
        $cartRule->quantity = max(0, (int) $data['quantity']);
        $cartRule->quantity_per_user = max(0, (int) $data['quantityPerUser']);
        $cartRule->active = (bool) $data['active'];
        $cartRule->highlight = true;
        $cartRule->partial_use = true;

        if ($data['reductionPercent'] !== null) {
            $cartRule->reduction_percent = (float) $data['reductionPercent'];
        }
        if ($data['reductionAmount'] !== null) {
            $cartRule->reduction_amount = (float) $data['reductionAmount'];
            $cartRule->reduction_tax = true;
            $cartRule->reduction_currency = (int) \Configuration::get('PS_CURRENCY_DEFAULT');
        }
        if ($data['minimumAmount'] !== null) {
            $cartRule->minimum_amount = (float) $data['minimumAmount'];
            $cartRule->minimum_amount_currency = (int) \Configuration::get('PS_CURRENCY_DEFAULT');
        }
    }
}
