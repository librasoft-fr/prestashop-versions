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
 * MCP tools to value, monitor and adjust inventory.
 *
 * Stock figures come from `stock_available`, reading the product-level row
 * (id_product_attribute = 0), which PrestaShop keeps as the sum of a product's
 * combinations — so summing those rows never double-counts variants.
 *
 * Movement history (`get_inventory_movement`) and warehouse locations
 * (`get_warehouse_locations`) only return data when Advanced Stock Management is
 * enabled; on a shop that doesn't use ASM they return empty results.
 *
 * Reorder points are stored as Configuration keys (`PS_ASK_AI_REORDER_<id>`):
 * PrestaShop has no native reorder-threshold store and no background alerting,
 * so `get_reorder_points` reports which products are currently below their
 * stored threshold but nothing fires automatically.
 */
class InventoryTools extends AbstractMcpTool
{
    /** Configuration key prefix for stored reorder thresholds. */
    private const REORDER_PREFIX = 'PS_ASK_AI_REORDER_';

    #[PsMcpTool(
        name: 'get_inventory_value',
        description: 'Total inventory value for the current shop (units on hand × cost), valued at wholesale price where set, otherwise sale price, plus the highest-value products. Amounts in the shop default currency. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max high-value products to list, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getInventoryValue(?int $langId = null, int $limit = 20): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);
            $cost = 'IF(p.wholesale_price > 0, p.wholesale_price, p.price)';

            $totals = new \DbQuery();
            $totals->select('SUM(sa.quantity) AS total_units')
                ->select('SUM(sa.quantity * ' . $cost . ') AS total_value')
                ->from('stock_available', 'sa')
                ->innerJoin('product', 'p', 'p.id_product = sa.id_product')
                ->where('sa.id_product_attribute = 0')
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)');
            $totalsRow = $this->rows($totals)[0] ?? [];

            $top = new \DbQuery();
            $top->select('sa.id_product, sa.quantity, pl.name')
                ->select('(sa.quantity * ' . $cost . ') AS stock_value')
                ->from('stock_available', 'sa')
                ->innerJoin('product', 'p', 'p.id_product = sa.id_product')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = sa.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . (int) $idShop)
                ->where('sa.id_product_attribute = 0')
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)')
                ->where('sa.quantity > 0')
                ->orderBy('stock_value DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'currency_iso' => $this->defaultCurrencyIso(),
                'total_units' => (int) ($totalsRow['total_units'] ?? 0),
                'total_value' => round((float) ($totalsRow['total_value'] ?? 0), 2),
                'top_value_products' => array_map(static function (array $r): array {
                    return [
                        'id_product' => (int) $r['id_product'],
                        'name' => (string) ($r['name'] ?? ''),
                        'quantity' => (int) $r['quantity'],
                        'stock_value' => round((float) $r['stock_value'], 2),
                    ];
                }, $this->rows($top)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_overstock_products',
        description: 'Find slow-moving stock: products with stock at or above a threshold that sold at most maxUnitsSold units in the last N days, highest stock first. PrestaShop has no "optimal level" field, so this heuristic flags high stock + low recent sales. Defaults: stock >= 100, 30 days, <= 5 sold, limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'stockThreshold' => ['type' => 'integer', 'description' => 'Minimum units on hand to consider (default 100)'],
            'days' => ['type' => 'integer', 'description' => 'Sales look-back window in days (default 30)'],
            'maxUnitsSold' => ['type' => 'integer', 'description' => 'Maximum units sold in the window to still count as slow-moving (default 5)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getOverstockProducts(int $stockThreshold = 100, int $days = 30, int $maxUnitsSold = 5, ?int $langId = null, int $limit = 20): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);
            $cutoff = date('Y-m-d 00:00:00', (int) strtotime('-' . max(1, $days) . ' days'));

            $soldSubquery = '(SELECT COALESCE(SUM(od.product_quantity), 0)'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order'
                . ' WHERE od.product_id = sa.id_product AND o.valid = 1 AND o.id_shop = ' . (int) $idShop
                . " AND o.date_add >= '" . pSQL($cutoff) . "')";

            $query = new \DbQuery();
            $query->select('sa.id_product, sa.quantity, pl.name')
                ->select($soldSubquery . ' AS units_sold')
                ->from('stock_available', 'sa')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = sa.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . (int) $idShop)
                ->where('sa.id_product_attribute = 0')
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)')
                ->where('sa.quantity >= ' . (int) $stockThreshold)
                ->having('units_sold <= ' . (int) max(0, $maxUnitsSold))
                ->orderBy('sa.quantity DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'stock_threshold' => (int) $stockThreshold,
                'sales_since' => $cutoff,
                'products' => array_map(static function (array $r): array {
                    return [
                        'id_product' => (int) $r['id_product'],
                        'name' => (string) ($r['name'] ?? ''),
                        'quantity' => (int) $r['quantity'],
                        'units_sold' => (int) $r['units_sold'],
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_backorder_status',
        description: 'List items that are out of stock (quantity <= 0) but still orderable (backorder allowed by their out-of-stock policy). PrestaShop stores no expected restock date, so none is reported. Default limit 50.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getBackorderStatus(?int $langId = null, int $limit = 50): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);
            $globalAllows = (int) \Configuration::get('PS_ORDER_OUT_OF_STOCK') === 1;

            // out_of_stock: 0 = deny, 1 = allow, 2 = use the global shop setting.
            $orderable = $globalAllows ? '(sa.out_of_stock IN (1, 2))' : '(sa.out_of_stock = 1)';

            $query = new \DbQuery();
            $query->select('sa.id_product, sa.id_product_attribute, sa.quantity, pl.name')
                ->from('stock_available', 'sa')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = sa.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . (int) $idShop)
                ->where('sa.quantity <= 0')
                ->where($orderable)
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)')
                ->orderBy('sa.quantity ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_product' => (int) $r['id_product'],
                    'id_product_attribute' => (int) $r['id_product_attribute'],
                    'name' => (string) ($r['name'] ?? ''),
                    'quantity' => (int) $r['quantity'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_inventory_movement',
        description: 'Stock movement history (in/out, quantity, reason) newest first, optionally for one product. Only populated when Advanced Stock Management is enabled; otherwise returns an empty list. Default limit 50.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => ['integer', 'null'], 'description' => 'Only movements for this product (optional)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id for reason labels (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getInventoryMovement(?int $productId = null, ?int $langId = null, int $limit = 50): array
    {
        try {
            $idLang = $this->resolveLangId($langId);
            $query = new \DbQuery();
            $query->select('sm.id_stock_mvt, sm.date_add, sm.sign, sm.physical_quantity, s.id_product, s.id_product_attribute, pl.name, smrl.name AS reason')
                ->from('stock_mvt', 'sm')
                ->innerJoin('stock', 's', 's.id_stock = sm.id_stock')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = s.id_product AND pl.id_lang = ' . (int) $idLang)
                ->leftJoin('stock_mvt_reason_lang', 'smrl', 'smrl.id_stock_mvt_reason = sm.id_stock_mvt_reason AND smrl.id_lang = ' . (int) $idLang);
            if ($productId !== null && $productId > 0) {
                $query->where('s.id_product = ' . (int) $productId);
            }
            $query->orderBy('sm.date_add DESC')->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_stock_mvt' => (int) $r['id_stock_mvt'],
                    'date_add' => (string) $r['date_add'],
                    'id_product' => (int) $r['id_product'],
                    'id_product_attribute' => (int) $r['id_product_attribute'],
                    'name' => (string) ($r['name'] ?? ''),
                    'direction' => ((int) $r['sign'] >= 0) ? 'in' : 'out',
                    'quantity' => (int) $r['physical_quantity'],
                    'reason' => (string) ($r['reason'] ?? ''),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_warehouse_locations',
        description: 'List warehouses, or (when productId is given) the storage locations of one product across warehouses. Requires Advanced Stock Management; returns empty when no warehouses are configured. Default limit 50.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => ['integer', 'null'], 'description' => 'List this product\'s locations instead of warehouses (optional)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getWarehouseLocations(?int $productId = null, int $limit = 50): array
    {
        try {
            if ($productId !== null && $productId > 0) {
                $query = new \DbQuery();
                $query->select('wpl.id_warehouse, w.name AS warehouse, wpl.id_product, wpl.id_product_attribute, wpl.location')
                    ->from('warehouse_product_location', 'wpl')
                    ->innerJoin('warehouse', 'w', 'w.id_warehouse = wpl.id_warehouse')
                    ->where('wpl.id_product = ' . (int) $productId)
                    ->limit($this->clampLimit($limit));

                return ['content' => array_map(static function (array $r): array {
                    return [
                        'id_warehouse' => (int) $r['id_warehouse'],
                        'warehouse' => (string) $r['warehouse'],
                        'id_product' => (int) $r['id_product'],
                        'id_product_attribute' => (int) $r['id_product_attribute'],
                        'location' => (string) ($r['location'] ?? ''),
                    ];
                }, $this->rows($query))];
            }

            $query = new \DbQuery();
            $query->select('w.id_warehouse, w.name, w.management_type')
                ->select('(SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'warehouse_product_location wpl WHERE wpl.id_warehouse = w.id_warehouse) AS product_locations')
                ->from('warehouse', 'w')
                ->where('w.deleted = 0')
                ->orderBy('w.name ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_warehouse' => (int) $r['id_warehouse'],
                    'name' => (string) $r['name'],
                    'management_type' => (string) $r['management_type'],
                    'product_locations' => (int) $r['product_locations'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'bulk_update_stock',
        description: 'Set the available quantity (absolute, not a delta) for several products/combinations in one call. Each item: productId, optional combinationId (0 = simple product) and quantity. Returns the confirmed quantity for each.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'items' => [
                'type' => 'array',
                'description' => 'Stock updates to apply',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => ['type' => 'integer', 'description' => 'Product id'],
                        'combinationId' => ['type' => 'integer', 'description' => 'Combination id, 0 for a simple product (default 0)'],
                        'quantity' => ['type' => 'integer', 'description' => 'New available quantity (absolute value)'],
                    ],
                    'required' => ['productId', 'quantity'],
                ],
            ],
        ],
        required: ['items']
    )]
    public function bulkUpdateStock(array $items): array
    {
        try {
            if ($items === []) {
                throw new \InvalidArgumentException('Provide at least one item to update.');
            }
            $idShop = $this->resolveShopId(null);

            $results = [];
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['productId'], $item['quantity'])) {
                    throw new \InvalidArgumentException('Each item needs productId and quantity.');
                }
                $productId = (int) $item['productId'];
                $combinationId = isset($item['combinationId']) ? max(0, (int) $item['combinationId']) : 0;
                $quantity = (int) $item['quantity'];

                \StockAvailable::setQuantity($productId, $combinationId, $quantity, $idShop);
                $confirmed = \StockAvailable::getQuantityAvailableByProduct($productId, $combinationId, $idShop);

                $results[] = [
                    'id_product' => $productId,
                    'id_product_attribute' => $combinationId,
                    'quantity' => (int) $confirmed,
                ];
            }

            return ['content' => ['updated' => count($results), 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'set_reorder_point',
        description: 'Store a reorder threshold for a product so get_reorder_points can flag it when stock falls to or below that level. Note: there is no background alerting — thresholds are only evaluated when get_reorder_points is called.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'Product id'],
            'threshold' => ['type' => 'integer', 'description' => 'Stock level at or below which the product should be reordered (0 removes the reorder point)'],
        ],
        required: ['productId', 'threshold']
    )]
    public function setReorderPoint(int $productId, int $threshold): array
    {
        try {
            $product = new \Product($productId);
            if (!\Validate::isLoadedObject($product)) {
                throw new \RuntimeException(sprintf('Product %d not found.', $productId));
            }

            $key = self::REORDER_PREFIX . $productId;
            if ($threshold <= 0) {
                \Configuration::deleteByName($key);

                return ['content' => ['id_product' => $productId, 'reorder_point' => null, 'removed' => true]];
            }

            \Configuration::updateValue($key, (int) $threshold);

            return ['content' => ['id_product' => $productId, 'reorder_point' => (int) $threshold]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_reorder_points',
        description: 'List products that have a stored reorder threshold, with their current stock and whether they are at or below it (i.e. need reordering). Stored via set_reorder_point.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'inventory']
    )]
    #[PsMcpSchema(
        properties: [
            'belowOnly' => ['type' => 'boolean', 'description' => 'Only return products at or below their reorder point (default false)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: []
    )]
    public function getReorderPoints(bool $belowOnly = false, ?int $langId = null): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('name, value')
                ->from('configuration')
                ->where("name LIKE '" . pSQL(self::REORDER_PREFIX) . "%'");

            $rows = [];
            foreach ($this->rows($query) as $row) {
                $productId = (int) substr((string) $row['name'], strlen(self::REORDER_PREFIX));
                if ($productId <= 0) {
                    continue;
                }
                $threshold = (int) $row['value'];
                $current = (int) \StockAvailable::getQuantityAvailableByProduct($productId, 0, $idShop);
                $needsReorder = $current <= $threshold;
                if ($belowOnly && !$needsReorder) {
                    continue;
                }

                $product = new \Product($productId, false, $idLang);
                $rows[] = [
                    'id_product' => $productId,
                    'name' => \Validate::isLoadedObject($product) ? (string) $product->name : '',
                    'reorder_point' => $threshold,
                    'current_stock' => $current,
                    'needs_reorder' => $needsReorder,
                ];
            }

            return ['content' => $rows];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
