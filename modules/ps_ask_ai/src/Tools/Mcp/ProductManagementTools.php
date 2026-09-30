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
 * MCP tools to audit and bulk-maintain the product catalogue: spot gaps
 * (missing images, untranslated products, duplicates), score products and apply
 * price / SEO / status changes across many products at once.
 *
 * The write tools mutate the shop and route through operator approval. Bulk
 * tools are best-effort per item: a failure on one item is reported in its
 * result row rather than aborting the whole batch.
 */
class ProductManagementTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_products_without_images',
        description: 'List active-catalogue products that have no image attached, for catalogue cleanup. Default limit 50.',
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
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getProductsWithoutImages(?int $langId = null, int $limit = 50): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('ps.id_product, p.reference, pl.name')
                ->from('product_shop', 'ps')
                ->innerJoin('product', 'p', 'p.id_product = ps.id_product')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ps.id_shop')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where('ps.id_product NOT IN (SELECT i.id_product FROM ' . _DB_PREFIX_ . 'image i)')
                ->orderBy('ps.id_product ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_product' => (int) $r['id_product'],
                    'reference' => (string) ($r['reference'] ?? ''),
                    'name' => (string) ($r['name'] ?? ''),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_with_missing_translations',
        description: 'List products incomplete in a given language: no translation row, or an empty name or description. Defaults to the current back-office language. Default limit 50.',
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
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id to check (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getProductsWithMissingTranslations(?int $langId = null, int $limit = 50): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $query = new \DbQuery();
            $query->select('ps.id_product, pl.name, pl.description')
                ->from('product_shop', 'ps')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ps.id_shop')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where("pl.id_product IS NULL OR pl.name IS NULL OR pl.name = '' OR pl.description IS NULL OR pl.description = ''")
                ->orderBy('ps.id_product ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'lang_id' => $idLang,
                'products' => array_map(static function (array $r): array {
                    $name = (string) ($r['name'] ?? '');
                    $description = (string) ($r['description'] ?? '');

                    return [
                        'id_product' => (int) $r['id_product'],
                        'name' => $name,
                        'missing_name' => $name === '',
                        'missing_description' => $description === '',
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_duplicate_products',
        description: 'Find likely duplicate products grouped by identical reference (default) or identical name. Returns each duplicated value with the matching product ids. Default limit 50 groups.',
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
            'by' => ['type' => 'string', 'enum' => ['reference', 'name'], 'description' => 'Match on "reference" (default) or "name"'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id when matching by name (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max duplicate groups, 1-100 (default 50)'],
        ],
        required: []
    )]
    public function getDuplicateProducts(string $by = 'reference', ?int $langId = null, int $limit = 50): array
    {
        try {
            $by = in_array($by, ['reference', 'name'], true) ? $by : 'reference';
            $query = new \DbQuery();

            if ($by === 'name') {
                $idLang = $this->resolveLangId($langId);
                $idShop = $this->resolveShopId(null);
                $query->select('pl.name AS value')
                    ->select('COUNT(*) AS occurrences')
                    ->select('GROUP_CONCAT(pl.id_product) AS product_ids')
                    ->from('product_lang', 'pl')
                    ->where('pl.id_lang = ' . (int) $idLang)
                    ->where('pl.id_shop = ' . (int) $idShop)
                    ->where("pl.name <> ''")
                    ->groupBy('pl.name');
            } else {
                $query->select('p.reference AS value')
                    ->select('COUNT(*) AS occurrences')
                    ->select('GROUP_CONCAT(p.id_product) AS product_ids')
                    ->from('product', 'p')
                    ->where("p.reference <> ''")
                    ->groupBy('p.reference');
            }
            $query->having('occurrences > 1')
                ->orderBy('occurrences DESC')
                ->limit($this->clampLimit($limit));

            return ['content' => [
                'matched_by' => $by,
                'groups' => array_map(static function (array $r): array {
                    $ids = array_map('intval', array_filter(explode(',', (string) $r['product_ids'])));

                    return [
                        'value' => (string) $r['value'],
                        'occurrences' => (int) $r['occurrences'],
                        'product_ids' => $ids,
                    ];
                }, $this->rows($query)),
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_product_performance_score',
        description: 'Score products 0-100 from recent sales, stock health and catalogue completeness (image + description). Pass productId for one product, or omit to rank the best recent sellers. PrestaShop has no native reviews, so reviews are not factored in. Defaults: 30-day window, limit 20.',
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
            'productId' => ['type' => ['integer', 'null'], 'description' => 'Score one product; omit to rank recent sellers'],
            'days' => ['type' => 'integer', 'description' => 'Sales look-back window in days (default 30)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows when ranking, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getProductPerformanceScore(?int $productId = null, int $days = 30, ?int $langId = null, int $limit = 20): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);
            $cutoff = date('Y-m-d 00:00:00', (int) strtotime('-' . max(1, $days) . ' days'));

            $soldSubquery = '(SELECT COALESCE(SUM(od.product_quantity), 0)'
                . ' FROM ' . _DB_PREFIX_ . 'order_detail od'
                . ' INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order'
                . ' WHERE od.product_id = ps.id_product AND o.valid = 1 AND o.id_shop = ' . (int) $idShop
                . " AND o.date_add >= '" . pSQL($cutoff) . "')";
            $stockSubquery = '(SELECT COALESCE(SUM(sa.quantity), 0) FROM ' . _DB_PREFIX_ . 'stock_available sa'
                . ' WHERE sa.id_product = ps.id_product AND sa.id_product_attribute = 0'
                . ' AND (sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0))';
            $hasImageSubquery = '(SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'image i WHERE i.id_product = ps.id_product)';

            $query = new \DbQuery();
            $query->select('ps.id_product, pl.name')
                ->select($soldSubquery . ' AS units_sold')
                ->select($stockSubquery . ' AS stock')
                ->select($hasImageSubquery . ' AS image_count')
                ->select("(pl.description IS NOT NULL AND pl.description <> '') AS has_description")
                ->from('product_shop', 'ps')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ps.id_shop')
                ->where('ps.id_shop = ' . (int) $idShop);

            if ($productId !== null && $productId > 0) {
                $query->where('ps.id_product = ' . (int) $productId);
            } else {
                $query->orderBy('units_sold DESC')->limit($this->clampLimit($limit));
            }

            return ['content' => array_map(function (array $r): array {
                $unitsSold = (int) $r['units_sold'];
                $stock = (int) $r['stock'];
                $hasImage = (int) $r['image_count'] > 0;
                $hasDescription = (bool) $r['has_description'];

                return [
                    'id_product' => (int) $r['id_product'],
                    'name' => (string) ($r['name'] ?? ''),
                    'units_sold' => $unitsSold,
                    'stock' => $stock,
                    'has_image' => $hasImage,
                    'has_description' => $hasDescription,
                    'score' => $this->performanceScore($unitsSold, $stock, $hasImage, $hasDescription),
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'bulk_update_product_prices',
        description: 'Set the base (tax-excluded) price of several products in one call. Each item: productId and price. Returns the old and new price for each product.',
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
            'items' => [
                'type' => 'array',
                'description' => 'Price updates to apply',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => ['type' => 'integer', 'description' => 'Product id'],
                        'price' => ['type' => 'number', 'description' => 'New tax-excluded base price in the shop default currency'],
                    ],
                    'required' => ['productId', 'price'],
                ],
            ],
        ],
        required: ['items']
    )]
    public function bulkUpdateProductPrices(array $items): array
    {
        try {
            if ($items === []) {
                throw new \InvalidArgumentException('Provide at least one item to update.');
            }

            $results = [];
            $updated = 0;
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['productId'], $item['price'])) {
                    throw new \InvalidArgumentException('Each item needs productId and price.');
                }
                $productId = (int) $item['productId'];
                $newPrice = (float) $item['price'];

                $product = new \Product($productId);
                if (!\Validate::isLoadedObject($product)) {
                    $results[] = ['id_product' => $productId, 'updated' => false, 'error' => 'Product not found'];
                    continue;
                }
                $oldPrice = (float) $product->price;
                $product->price = $newPrice;
                $ok = (bool) $product->update();
                if ($ok) {
                    ++$updated;
                }
                $results[] = [
                    'id_product' => $productId,
                    'updated' => $ok,
                    'old_price' => round($oldPrice, 6),
                    'new_price' => round($newPrice, 6),
                ];
            }

            return ['content' => ['updated' => $updated, 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_product_seo_metadata',
        description: 'Update SEO meta fields (meta title, meta description, meta keywords) of several products for one language. Only the provided fields change; other languages are preserved. Defaults to the current back-office language.',
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
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id to write (defaults to the current back-office language)'],
            'items' => [
                'type' => 'array',
                'description' => 'SEO updates to apply',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => ['type' => 'integer', 'description' => 'Product id'],
                        'metaTitle' => ['type' => 'string', 'description' => 'New meta title (optional)'],
                        'metaDescription' => ['type' => 'string', 'description' => 'New meta description (optional)'],
                        'metaKeywords' => ['type' => 'string', 'description' => 'New meta keywords (optional)'],
                    ],
                    'required' => ['productId'],
                ],
            ],
        ],
        required: ['items']
    )]
    public function updateProductSeoMetadata(array $items, ?int $langId = null): array
    {
        try {
            if ($items === []) {
                throw new \InvalidArgumentException('Provide at least one item to update.');
            }
            $idLang = $this->resolveLangId($langId);

            $results = [];
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['productId'])) {
                    throw new \InvalidArgumentException('Each item needs a productId.');
                }
                $productId = (int) $item['productId'];
                $product = new \Product($productId);
                if (!\Validate::isLoadedObject($product)) {
                    $results[] = ['id_product' => $productId, 'updated' => false, 'error' => 'Product not found'];
                    continue;
                }

                if (isset($item['metaTitle'])) {
                    $this->setLangField($product, 'meta_title', $idLang, (string) $item['metaTitle']);
                }
                if (isset($item['metaDescription'])) {
                    $this->setLangField($product, 'meta_description', $idLang, (string) $item['metaDescription']);
                }
                if (isset($item['metaKeywords'])) {
                    $this->setLangField($product, 'meta_keywords', $idLang, (string) $item['metaKeywords']);
                }

                $ok = (bool) $product->update();
                $results[] = ['id_product' => $productId, 'updated' => $ok];
            }

            return ['content' => ['lang_id' => $idLang, 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'bulk_update_product_attributes',
        description: 'Apply the same status flags (active, available for order, show price, online only) to several products at once. Only the provided flags change. This sets plain product fields — it does not edit combinations/variants or feature values.',
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
            'productIds' => ['type' => 'array', 'description' => 'Product ids to update', 'items' => ['type' => 'integer']],
            'active' => ['type' => ['boolean', 'null'], 'description' => 'Enable/disable the products'],
            'availableForOrder' => ['type' => ['boolean', 'null'], 'description' => 'Whether the products can be ordered'],
            'showPrice' => ['type' => ['boolean', 'null'], 'description' => 'Whether the price is shown'],
            'onlineOnly' => ['type' => ['boolean', 'null'], 'description' => 'Web-only (not sold in store)'],
        ],
        required: ['productIds']
    )]
    public function bulkUpdateProductAttributes(
        array $productIds,
        ?bool $active = null,
        ?bool $availableForOrder = null,
        ?bool $showPrice = null,
        ?bool $onlineOnly = null,
    ): array {
        try {
            if ($productIds === []) {
                throw new \InvalidArgumentException('Provide at least one product id.');
            }
            if ($active === null && $availableForOrder === null && $showPrice === null && $onlineOnly === null) {
                throw new \InvalidArgumentException('Provide at least one flag to change.');
            }

            $results = [];
            $updated = 0;
            foreach ($productIds as $rawId) {
                $productId = (int) $rawId;
                $product = new \Product($productId);
                if (!\Validate::isLoadedObject($product)) {
                    $results[] = ['id_product' => $productId, 'updated' => false, 'error' => 'Product not found'];
                    continue;
                }
                if ($active !== null) {
                    $product->active = $active;
                }
                if ($availableForOrder !== null) {
                    $product->available_for_order = $availableForOrder;
                }
                if ($showPrice !== null) {
                    $product->show_price = $showPrice;
                }
                if ($onlineOnly !== null) {
                    $product->online_only = $onlineOnly;
                }
                $ok = (bool) $product->update();
                if ($ok) {
                    ++$updated;
                }
                $results[] = ['id_product' => $productId, 'updated' => $ok];
            }

            return ['content' => ['updated' => $updated, 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'schedule_price_changes',
        description: 'Queue price changes to take effect at a future date by creating dated catalog specific prices (PrestaShop applies them automatically within the from/to window — no external scheduler needed). Each item sets either an absolute newPrice or a reduction (amount/percentage) and a "from" datetime, with an optional "to".',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'catalog']
    )]
    #[PsMcpSchema(
        properties: [
            'items' => [
                'type' => 'array',
                'description' => 'Scheduled price changes to create',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'productId' => ['type' => 'integer', 'description' => 'Product id'],
                        'from' => ['type' => 'string', 'description' => 'When the change applies, "Y-m-d H:i:s", or "Y-m-d" for the start of that day'],
                        'to' => ['type' => 'string', 'description' => 'When the change ends, "Y-m-d H:i:s", or "Y-m-d" for the end of that day (optional)'],
                        'newPrice' => ['type' => 'number', 'description' => 'Absolute tax-excluded price to set during the window (optional)'],
                        'reductionType' => ['type' => 'string', 'enum' => ['amount', 'percentage'], 'description' => 'Reduction type if not setting an absolute price'],
                        'reduction' => ['type' => 'number', 'description' => 'Reduction value; for percentage pass 0.1 or 10 for 10%'],
                    ],
                    'required' => ['productId', 'from'],
                ],
            ],
        ],
        required: ['items']
    )]
    public function schedulePriceChanges(array $items): array
    {
        try {
            if ($items === []) {
                throw new \InvalidArgumentException('Provide at least one scheduled change.');
            }

            $results = [];
            $scheduled = 0;
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['productId'], $item['from'])) {
                    throw new \InvalidArgumentException('Each item needs productId and from.');
                }
                $productId = (int) $item['productId'];
                $from = (string) $item['from'];
                if (!\Validate::isDate($from)) {
                    throw new \InvalidArgumentException(sprintf('Invalid "from" date for product %d.', $productId));
                }
                $from = $this->widenDateOnly($from, false);
                // '0000-00-00 00:00:00' is PrestaShop's "no limit" sentinel.
                $to = $this->normaliseDate(isset($item['to']) ? (string) $item['to'] : null, '0000-00-00 00:00:00', true);

                $product = new \Product($productId);
                if (!\Validate::isLoadedObject($product)) {
                    $results[] = ['id_product' => $productId, 'scheduled' => false, 'error' => 'Product not found'];
                    continue;
                }

                $specificPrice = new \SpecificPrice();
                $specificPrice->id_product = $productId;
                $specificPrice->id_product_attribute = 0;
                $specificPrice->id_shop = 0;
                $specificPrice->id_shop_group = 0;
                $specificPrice->id_currency = 0;
                $specificPrice->id_country = 0;
                $specificPrice->id_group = 0;
                $specificPrice->id_customer = 0;
                $specificPrice->from_quantity = 1;
                $specificPrice->from = $from;
                $specificPrice->to = $to;

                if (isset($item['newPrice'])) {
                    $specificPrice->price = (float) $item['newPrice'];
                    $specificPrice->reduction = 0;
                    $specificPrice->reduction_type = 'amount';
                    $specificPrice->reduction_tax = 1;
                } elseif (isset($item['reductionType'], $item['reduction'])) {
                    $type = (string) $item['reductionType'];
                    if (!in_array($type, ['amount', 'percentage'], true)) {
                        throw new \InvalidArgumentException('reductionType must be "amount" or "percentage".');
                    }
                    $reduction = (float) $item['reduction'];
                    if ($type === 'percentage' && $reduction > 1) {
                        $reduction /= 100;
                    }
                    $specificPrice->price = -1; // keep base price, only apply the reduction
                    $specificPrice->reduction = $reduction;
                    $specificPrice->reduction_type = $type;
                    $specificPrice->reduction_tax = 1;
                } else {
                    throw new \InvalidArgumentException(sprintf('Provide newPrice or reductionType+reduction for product %d.', $productId));
                }

                $ok = (bool) $specificPrice->add();
                if ($ok) {
                    ++$scheduled;
                }
                $results[] = [
                    'id_product' => $productId,
                    'scheduled' => $ok,
                    'id_specific_price' => $ok ? (int) $specificPrice->id : null,
                    'from' => $from,
                    'to' => $to,
                ];
            }

            return ['content' => ['scheduled' => $scheduled, 'items' => $results]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Compute a 0-100 product performance score weighting recent sales most,
     * then catalogue completeness, then stock presence.
     */
    private function performanceScore(int $unitsSold, int $stock, bool $hasImage, bool $hasDescription): int
    {
        $salesScore = min($unitsSold, 50) / 50 * 60;
        $imageScore = $hasImage ? 15 : 0;
        $descriptionScore = $hasDescription ? 15 : 0;
        $stockScore = $stock > 0 ? 10 : 0;

        return (int) round($salesScore + $imageScore + $descriptionScore + $stockScore);
    }

    /**
     * Set one language's value of a multilang product field while preserving the
     * other languages. Loading a Product without a lang id exposes its lang
     * fields as `[idLang => value]` arrays.
     */
    private function setLangField(\Product $product, string $field, int $idLang, string $value): void
    {
        $current = $product->{$field};
        if (!is_array($current)) {
            $current = $this->multilangValue(is_string($current) ? $current : '');
        }
        $current[$idLang] = $value;
        $product->{$field} = $current;
    }
}
