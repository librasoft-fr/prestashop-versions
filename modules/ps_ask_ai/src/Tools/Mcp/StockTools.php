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
 * MCP tools to set available stock for products and combinations.
 *
 * These are write operations (not read-only), so they pass through the
 * operator approval flow before executing.
 */
class StockTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'update_stock_by_product',
        description: 'Set the available quantity of a simple product (no combinations).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'catalog']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'Product id'],
            'quantity' => ['type' => 'integer', 'description' => 'New available quantity (absolute value, not a delta)'],
        ],
        required: ['productId', 'quantity']
    )]
    public function updateStockByProduct(int $productId, int $quantity): array
    {
        return $this->setQuantity($productId, 0, $quantity);
    }

    #[PsMcpTool(
        name: 'update_stock_by_combination',
        description: 'Set the available quantity of a specific product combination (variant).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'catalog']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'Product id'],
            'combinationId' => ['type' => 'integer', 'description' => 'Product attribute (combination) id'],
            'quantity' => ['type' => 'integer', 'description' => 'New available quantity (absolute value, not a delta)'],
        ],
        required: ['productId', 'combinationId', 'quantity']
    )]
    public function updateStockByCombination(int $productId, int $combinationId, int $quantity): array
    {
        return $this->setQuantity($productId, $combinationId, $quantity);
    }

    /**
     * Apply an absolute stock quantity and read it back for confirmation.
     *
     * @return array<string, array<string, int>>
     */
    private function setQuantity(int $productId, int $combinationId, int $quantity): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            \StockAvailable::setQuantity($productId, $combinationId, $quantity, $idShop);
            $confirmed = \StockAvailable::getQuantityAvailableByProduct($productId, $combinationId, $idShop);

            return ['content' => [
                'id_product' => $productId,
                'id_product_attribute' => $combinationId,
                'quantity' => (int) $confirmed,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
