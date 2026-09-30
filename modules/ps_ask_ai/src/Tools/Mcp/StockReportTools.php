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
 * Read-only MCP tools for stock reporting (low-stock alerts, per-product stock).
 */
class StockReportTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_low_stock_products',
        description: 'List products whose available quantity is at or below a threshold, ordered by quantity ascending. Default threshold 5, limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'catalog']
    )]
    #[PsMcpSchema(
        properties: [
            'threshold' => ['type' => 'integer', 'description' => 'Quantity at or below which a product is considered low stock (default 5)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getLowStockProducts(int $threshold = 5, ?int $langId = null, int $limit = 20): array
    {
        try {
            $idLang = $this->resolveLangId($langId);
            $idShop = $this->resolveShopId(null);
            $query = new \DbQuery();
            $query->select('sa.id_product, sa.id_product_attribute, sa.quantity, pl.name')
                ->from('stock_available', 'sa')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = sa.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . (int) $idShop)
                ->where('sa.quantity <= ' . (int) $threshold)
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)')
                ->orderBy('sa.quantity ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_stock_by_product',
        description: 'Get the available quantity for a product (optionally a specific combination).',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'catalog']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'Product id'],
            'combinationId' => ['type' => ['integer', 'null'], 'description' => 'Product attribute (combination) id, omit for the whole product'],
        ],
        required: ['productId']
    )]
    public function getStockByProduct(int $productId, ?int $combinationId = null): array
    {
        try {
            $combination = ($combinationId !== null && $combinationId > 0) ? $combinationId : 0;
            $quantity = \StockAvailable::getQuantityAvailableByProduct($productId, $combination, $this->resolveShopId(null));

            return ['content' => [
                'id_product' => $productId,
                'id_product_attribute' => $combination,
                'quantity' => (int) $quantity,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
