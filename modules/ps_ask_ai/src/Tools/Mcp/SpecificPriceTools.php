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
 * MCP tools for catalog price rules (specific prices).
 *
 * Cart rules only discount the cart; a specific price changes the price shown on
 * the product page (per product/combination, optionally scoped to a group,
 * currency, country, customer or quantity). create_specific_price is a write op
 * and routes through operator approval.
 */
class SpecificPriceTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_specific_prices',
        description: 'List catalog specific prices (product price reductions), optionally filtered to one product. Default limit 20.',
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
            'productId' => ['type' => ['integer', 'null'], 'description' => 'Only specific prices for this product (optional)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getSpecificPrices(?int $productId = null, int $limit = 20): array
    {
        try {
            $query = new \DbQuery();
            $query->select('sp.id_specific_price, sp.id_product, sp.id_product_attribute, sp.id_shop, sp.id_currency, sp.id_country, sp.id_group, sp.id_customer, sp.price, sp.from_quantity, sp.reduction, sp.reduction_type, sp.`from`, sp.`to`')
                ->from('specific_price', 'sp');
            if ($productId !== null && $productId > 0) {
                $query->where('sp.id_product = ' . (int) $productId);
            }
            $query->orderBy('sp.id_specific_price DESC')->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_specific_price',
        description: 'Create a catalog specific price (reduction) for a product. reductionType is "amount" (fixed in default currency) or "percentage". For percentage, pass either a fraction (0.1) or a percent (10).',
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
            'productId' => ['type' => 'integer', 'description' => 'Product the reduction applies to'],
            'reductionType' => ['type' => 'string', 'enum' => ['amount', 'percentage'], 'description' => '"amount" or "percentage"'],
            'reduction' => ['type' => 'number', 'description' => 'Reduction value. For percentage pass 0.1 or 10 for 10%; for amount pass the value in the default currency'],
            'combinationId' => ['type' => 'integer', 'description' => 'Combination (product attribute) id, 0 = all combinations (default 0)'],
            'fromQuantity' => ['type' => 'integer', 'description' => 'Minimum quantity for the price to apply (default 1)'],
            'groupId' => ['type' => 'integer', 'description' => 'Customer group id, 0 = all groups (default 0)'],
            'currencyId' => ['type' => 'integer', 'description' => 'Currency id, 0 = all currencies (default 0)'],
            'countryId' => ['type' => 'integer', 'description' => 'Country id, 0 = all countries (default 0)'],
            'customerId' => ['type' => 'integer', 'description' => 'Customer id, 0 = all customers (default 0)'],
            'dateFrom' => ['type' => ['string', 'null'], 'description' => 'Start "Y-m-d H:i:s", or "Y-m-d" for the start of that day (optional, omit for no start limit)'],
            'dateTo' => ['type' => ['string', 'null'], 'description' => 'End "Y-m-d H:i:s", or "Y-m-d" for the end of that day (optional, omit for no end limit)'],
        ],
        required: ['productId', 'reductionType', 'reduction']
    )]
    public function createSpecificPrice(
        int $productId,
        string $reductionType,
        float $reduction,
        int $combinationId = 0,
        int $fromQuantity = 1,
        int $groupId = 0,
        int $currencyId = 0,
        int $countryId = 0,
        int $customerId = 0,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        try {
            if (!in_array($reductionType, ['amount', 'percentage'], true)) {
                throw new \InvalidArgumentException('reductionType must be "amount" or "percentage".');
            }

            $product = new \Product($productId);
            if (!\Validate::isLoadedObject($product)) {
                throw new \RuntimeException(sprintf('Product %d not found.', $productId));
            }

            // Percentage reductions are stored as a 0-1 fraction; accept a
            // percent value (e.g. 10) and normalise it.
            if ($reductionType === 'percentage' && $reduction > 1) {
                $reduction /= 100;
            }

            $specificPrice = new \SpecificPrice();
            $specificPrice->id_product = $productId;
            $specificPrice->id_product_attribute = max(0, $combinationId);
            $specificPrice->id_shop = 0;
            $specificPrice->id_shop_group = 0;
            $specificPrice->id_currency = max(0, $currencyId);
            $specificPrice->id_country = max(0, $countryId);
            $specificPrice->id_group = max(0, $groupId);
            $specificPrice->id_customer = max(0, $customerId);
            $specificPrice->price = -1; // keep the product base price, only apply the reduction
            $specificPrice->from_quantity = max(1, $fromQuantity);
            $specificPrice->reduction = $reduction;
            $specificPrice->reduction_type = $reductionType;
            $specificPrice->reduction_tax = 1;
            // '0000-00-00 00:00:00' is PrestaShop's "no limit" sentinel.
            $specificPrice->from = $this->normaliseDate($dateFrom, '0000-00-00 00:00:00', false);
            $specificPrice->to = $this->normaliseDate($dateTo, '0000-00-00 00:00:00', true);

            if (!$specificPrice->add()) {
                throw new \RuntimeException('Failed to create the specific price.');
            }

            return ['content' => [
                'id_specific_price' => (int) $specificPrice->id,
                'id_product' => $productId,
                'reduction' => $reduction,
                'reduction_type' => $reductionType,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
