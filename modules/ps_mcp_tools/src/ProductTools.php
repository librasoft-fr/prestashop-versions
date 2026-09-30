<?php
/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Tools module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Tools module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpTools;

use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;
use PrestaShop\Module\PsMcpTools\Webservice\AbstractWebservice;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Product management tools using PrestaShop webservices
 */
class ProductTools extends AbstractWebservice
{
    /**
     * Resource name for products
     */
    private const RESOURCE = 'products';

    /**
     * MCP fields definitions for Product (main fields)
     */
    private const PRODUCT_MCP_FIELDS = [
        'id_manufacturer' => [
            'type' => 'integer',
            'description' => 'Manufacturer ID',
        ],
        'id_supplier' => [
            'type' => 'integer',
            'description' => 'Default supplier ID',
        ],
        'id_category_default' => [
            'type' => 'integer',
            'description' => 'Default category ID',
        ],
        'id_shop_default' => [
            'type' => 'integer',
            'description' => 'Default shop ID',
        ],
        'id_tax_rules_group' => [
            'type' => 'integer',
            'description' => 'Tax rules group ID',
        ],
        'id_default_image' => [
            'type' => 'integer',
            'description' => 'Default (cover) image ID for the product',
        ],
        'name' => [
            'type' => 'string',
            'description' => 'Product name (multilang)',
        ],
        'description' => [
            'type' => 'string',
            'description' => 'Full product description, supports HTML (multilang)',
        ],
        'description_short' => [
            'type' => 'string',
            'description' => 'Short product description (multilang)',
        ],
        'link_rewrite' => [
            'type' => 'string',
            'description' => 'SEO-friendly URL (multilang)',
        ],
        'meta_description' => [
            'type' => 'string',
            'description' => 'SEO meta description (multilang)',
        ],
        'meta_title' => [
            'type' => 'string',
            'description' => 'SEO meta title (multilang)',
        ],
        'meta_keywords' => [
            'type' => 'string',
            'description' => 'SEO meta keywords (multilang)',
        ],
        'available_now' => [
            'type' => 'string',
            'description' => 'Text when product is in stock (multilang)',
        ],
        'available_later' => [
            'type' => 'string',
            'description' => 'Text when product is out of stock (multilang)',
        ],
        'price' => [
            'type' => 'number',
            'description' => 'Product price (tax excluded)',
        ],
        'wholesale_price' => [
            'type' => 'number',
            'description' => 'Wholesale price',
        ],
        'unity' => [
            'type' => 'string',
            'description' => 'Unit type (kg, L, etc.)',
        ],
        'unit_price_ratio' => [
            'type' => 'number',
            'description' => 'Unit price ratio',
        ],
        'additional_shipping_cost' => [
            'type' => 'number',
            'description' => 'Additional shipping cost',
        ],
        'reference' => [
            'type' => 'string',
            'description' => 'Product reference/SKU',
        ],
        'supplier_reference' => [
            'type' => 'string',
            'description' => 'Supplier reference',
        ],
        'location' => [
            'type' => 'string',
            'description' => 'Warehouse location',
        ],
        'width' => [
            'type' => 'number',
            'description' => 'Width',
        ],
        'height' => [
            'type' => 'number',
            'description' => 'Height',
        ],
        'depth' => [
            'type' => 'number',
            'description' => 'Depth',
        ],
        'weight' => [
            'type' => 'number',
            'description' => 'Weight',
        ],
        'ean13' => [
            'type' => 'string',
            'description' => 'EAN-13 barcode',
        ],
        'isbn' => [
            'type' => 'string',
            'description' => 'ISBN code',
        ],
        'upc' => [
            'type' => 'string',
            'description' => 'UPC barcode',
        ],
        'mpn' => [
            'type' => 'string',
            'description' => 'Manufacturer Part Number',
        ],
        'ecotax' => [
            'type' => 'number',
            'description' => 'Eco tax',
        ],
        'minimal_quantity' => [
            'type' => 'integer',
            'description' => 'Minimum quantity for order',
        ],
        'low_stock_threshold' => [
            'type' => 'integer',
            'description' => 'Low stock alert threshold',
        ],
        'low_stock_alert' => [
            'type' => 'boolean',
            'description' => 'Enable low stock alert',
        ],
        'quantity' => [
            'type' => 'integer',
            'description' => 'Available quantity',
        ],
        'active' => [
            'type' => 'boolean',
            'description' => 'Product is active',
        ],
        'available_for_order' => [
            'type' => 'boolean',
            'description' => 'Available for purchase',
        ],
        'available_date' => [
            'type' => 'string',
            'description' => 'Available date (YYYY-MM-DD)',
        ],
        'show_condition' => [
            'type' => 'boolean',
            'description' => 'Show condition on product page',
        ],
        'condition' => [
            'type' => 'string',
            'description' => 'Condition (new, used, refurbished)',
        ],
        'show_price' => [
            'type' => 'boolean',
            'description' => 'Show price on product page',
        ],
        'indexed' => [
            'type' => 'boolean',
            'description' => 'Product is indexed for search',
        ],
        'visibility' => [
            'type' => 'string',
            'description' => 'Visibility (both, catalog, search, none)',
        ],
        'on_sale' => [
            'type' => 'boolean',
            'description' => 'Product is on sale',
        ],
        'online_only' => [
            'type' => 'boolean',
            'description' => 'Available online only',
        ],
        'is_virtual' => [
            'type' => 'boolean',
            'description' => 'Is virtual product (downloadable)',
        ],
        'customizable' => [
            'type' => 'integer',
            'description' => 'Number of customizable text fields',
        ],
        'uploadable_files' => [
            'type' => 'integer',
            'description' => 'Number of uploadable files',
        ],
        'text_fields' => [
            'type' => 'integer',
            'description' => 'Number of text fields',
        ],
        'redirect_type' => [
            'type' => 'string',
            'description' => 'Redirect type when disabled',
        ],
        'id_type_redirected' => [
            'type' => 'integer',
            'description' => 'ID for redirect',
        ],
        'advanced_stock_management' => [
            'type' => 'boolean',
            'description' => 'Use advanced stock management',
        ],
        'pack_stock_type' => [
            'type' => 'integer',
            'description' => 'Pack stock type (0=default, 1=products only, 2=pack only, 3=both)',
        ],
        'state' => [
            'type' => 'integer',
            'description' => 'Product state ID',
        ],
    ];

    #[PsMcpTool(
        name: 'get_products',
        title: 'Get Products',
        description: 'Retrieve a list of products from the store using PrestaShop webservices. Default limit is 10 products.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'filter' => ['type' => 'string', 'description' => 'Filter results. Format: "field=[value]" for exact match (case-insensitive). Multiple fields use comma: "active=1,id_category_default=5". Operators: "[val1|val2]" (OR list), "[min,max]" (range/interval), "[prefix]%" (begins with), "%[suffix]" (ends with), "%[value]%" (contains). Examples: "active=1", "id=[10|20|30]", "price=[100,500]", "reference=[REF]%", "active=1,on_sale=1". Default: empty'],
            'sort' => ['type' => 'string', 'description' => 'Sort results (e.g., "id_ASC", "price_DESC", "date_add_DESC") - default: empty'],
            'limit' => ['type' => 'string', 'description' => 'Limit results (e.g., "10" or "0,10") - default: 10'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getProducts(
        string $display,
        string $filter = '',
        string $sort = '',
        string $limit = '10',
        ?int $langId = null,
    ): array {
        try {
            return $this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_product_by_id',
        title: 'Get Product by ID',
        description: 'Retrieve a specific product by its ID using PrestaShop webservices',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product to retrieve'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['productId', 'display']
    )]
    public function getProductById(int $productId, string $display, ?int $langId = null): array
    {
        try {
            return $this->getResourceById(self::RESOURCE, $productId, $display, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'search_product',
        title: 'Search Products',
        description: 'Find the most relevant products based on search terms for a given language',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'searchTerms' => ['type' => 'string', 'description' => 'Semicolon separated list of terms to search for.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
            'limitResults' => ['type' => 'integer', 'description' => 'Maximum number of search results (defaults to 10).'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
        ],
        required: ['searchTerms', 'display']
    )]
    public function searchProducts(
        string $searchTerms,
        string $display,
        ?int $langId = null,
        int $limitResults = 10,
    ): array {
        try {
            // Use default language if not specified
            if ($langId === null) {
                $langId = (int) \Configuration::get('PS_LANG_DEFAULT');
            }

            // Use PrestaShop's native search to find relevant product IDs
            $searchResult = \Search::find($langId, $searchTerms, 1, $limitResults, 'position', 'desc', false, false);

            if (!is_array($searchResult) || empty($searchResult)) {
                return ['products' => []];
            }

            // Extract product IDs from search results
            $productIds = array_column($searchResult['result'], 'id_product');

            if (empty($productIds)) {
                return ['products' => []];
            }

            // Build filter string with product IDs using OR syntax
            $filter = 'id=[' . implode('|', $productIds) . ']';

            // Retrieve full product details via webservice
            $products = $this->getResourceList(self::RESOURCE, $display, $filter, '', (string) $limitResults, $langId);

            return $products;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_product',
        title: 'Create Product',
        description: 'Create a product for a given language.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'data' => [
                'type' => 'object',
                'description' => 'Create a new product with these properties',
                'properties' => self::PRODUCT_MCP_FIELDS,
                'additionalProperties' => false,
                'required' => [
                    'price',
                ],
            ],
            'langId' => ['type' => 'integer', 'description' => 'Language of this product.'],
        ],
        required: ['data', 'langId']
    )]
    public function createProduct(array $data, int $langId): array
    {
        try {
            $quantity = isset($data['quantity']) ? (int) $data['quantity'] : null;
            unset($data['quantity']);

            // Apply sensible defaults matching PrestaShop's Product class defaults
            $defaults = [
                'active' => 1,
                'visibility' => 'both',
                'available_for_order' => 1,
                'show_price' => 1,
                'condition' => 'new',
                'id_category_default' => (int) \Configuration::get('PS_HOME_CATEGORY') ?: 2,
                'id_shop_default' => (int) \Configuration::get('PS_SHOP_DEFAULT') ?: 1,
                'minimal_quantity' => 1,
                'id_tax_rules_group' => 0,
            ];
            foreach ($defaults as $field => $defaultValue) {
                if (!isset($data[$field])) {
                    $data[$field] = $defaultValue;
                }
            }

            // Generate link_rewrite from name if not provided
            if (!isset($data['link_rewrite']) && isset($data['name'])) {
                $data['link_rewrite'] = \Tools::str2url($data['name']);
            }

            $product = new \Product(null, false, $langId);
            foreach ($data as $field => $value) {
                $product->$field = $value;
            }

            $validationErrors = $product->validateController();
            if (count($validationErrors) > 0) {
                throw new \InvalidArgumentException(implode('; ', $validationErrors));
            }

            $product->save();

            // Associate product with its default category (required for FO visibility)
            $categories = [(int) $product->id_category_default];
            $product->updateCategories($categories);

            // Build search index so product is findable in FO
            \Search::indexation(false, (int) $product->id);

            if ($quantity !== null) {
                \StockAvailable::setQuantity((int) $product->id, 0, $quantity);
            }

            return $this->getResourceById(self::RESOURCE, (int) $product->id, 'full');
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_product_by_id',
        title: 'Update Product',
        description: 'Update an existing product using PrestaShop webservices',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product to update'],
            'data' => [
                'type' => 'object',
                'description' => 'Product data to update. For multilang fields, provide an object with language IDs as keys',
                'properties' => self::PRODUCT_MCP_FIELDS,
                'additionalProperties' => false,
            ],
        ],
        required: ['productId', 'data']
    )]
    public function updateProductById(int $productId, array $data): array
    {
        try {
            $quantity = isset($data['quantity']) ? (int) $data['quantity'] : null;
            unset($data['quantity']);

            $result = empty($data) ? [] : $this->updateResource(self::RESOURCE, $productId, $data);

            if ($quantity !== null && !$this->productHasCombinations($productId)) {
                \StockAvailable::setQuantity($productId, 0, $quantity);
            }

            return $result;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_by_category',
        title: 'Get Products by Category',
        description: 'Get all products in a specific category',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'categoryId' => ['type' => 'integer', 'description' => 'ID of the category'],
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: date_add_DESC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['categoryId', 'display']
    )]
    public function getProductsByCategory(
        int $categoryId,
        string $display,
        bool $active = true,
        string $sort = 'date_add_DESC',
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "id_category_default={$categoryId},active={$activeValue}";

            return $this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_by_manufacturer',
        title: 'Get Products by Manufacturer',
        description: 'Get all products from a specific manufacturer',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'manufacturerId' => ['type' => 'integer', 'description' => 'ID of the manufacturer'],
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: id_ASC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['manufacturerId', 'display']
    )]
    public function getProductsByManufacturer(
        int $manufacturerId,
        string $display,
        bool $active = true,
        string $sort = 'id_ASC',
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "id_manufacturer={$manufacturerId},active={$activeValue}";

            return $this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_by_supplier',
        title: 'Get Products by Supplier',
        description: 'Get all products from a specific supplier',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'supplierId' => ['type' => 'integer', 'description' => 'ID of the supplier'],
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: id_ASC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['supplierId', 'display']
    )]
    public function getProductsBySupplier(
        int $supplierId,
        string $display,
        bool $active = true,
        string $sort = 'id_ASC',
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "id_supplier={$supplierId},active={$activeValue}";

            return $this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_on_sale',
        title: 'Get Products on Sale',
        description: 'Get all products currently on sale',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getProductsOnSale(
        string $display,
        bool $active = true,
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "on_sale=1,active={$activeValue}";

            $products = $this->getResourceList(self::RESOURCE, $display, $filter, '', $limit, $langId);

            if (!empty($products)) {
                // Sort manually by date_add
                usort($products['products'], function ($a, $b) {
                    return strtotime($b['date_add']) <=> strtotime($a['date_add']);
                });
            }

            return $products;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_by_reference',
        title: 'Get Products by Reference',
        description: 'Search products by reference/SKU',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'reference' => ['type' => 'string', 'description' => 'Product reference to search for. For multiple references (OR search), separate them with pipe | WITHOUT spaces (e.g., "REF001|REF002|REF003"). Use % for wildcard'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['reference', 'display']
    )]
    public function getProductsByReference(
        string $reference,
        string $display,
        ?int $langId = null,
    ): array {
        try {
            $filter = "reference=[{$reference}]%";

            return $this->getResourceList(self::RESOURCE, $display, $filter, '', '100', $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_product_stock_availables',
        title: 'Get Product Stock',
        description: 'Get stock information for a specific product',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['productId', 'display']
    )]
    public function getProductStockAvailables(int $productId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_product={$productId}";
            $result = $this->getResourceList('stock_availables', $display, $filter, '', '100', $langId);

            $hasCombinations = $this->productHasCombinations($productId);
            $result['has_combinations'] = $hasCombinations;
            $result['total_quantity'] = $this->computeTotalStock($productId, $hasCombinations);

            return $result;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function productHasCombinations(int $productId): bool
    {
        $result = $this->getResourceList('combinations', '[id]', "id_product={$productId}", '', '1');

        return !empty($result['combinations']);
    }

    private function computeTotalStock(int $productId, bool $hasCombinations): int
    {
        $stocks = $this->getResourceList(
            'stock_availables',
            '[id,quantity,id_product_attribute]',
            "id_product={$productId}",
            '',
            '100'
        );
        $total = 0;
        foreach ($stocks['stock_availables'] ?? [] as $item) {
            $isComboEntry = (int) ($item['id_product_attribute'] ?? 0) > 0;
            if ($isComboEntry === $hasCombinations) {
                $total += (int) ($item['quantity'] ?? 0);
            }
        }

        return $total;
    }

    #[PsMcpTool(
        name: 'get_product_combinations',
        title: 'Get Product Combinations',
        description: 'Get all combinations (variants) for a specific product',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'products']
    )]
    #[PsMcpSchema(
        properties: [
            'productId' => ['type' => 'integer', 'description' => 'ID of the product'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,price,reference,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['productId', 'display']
    )]
    public function getProductCombinations(int $productId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_product={$productId}";

            return $this->getResourceList('combinations', $display, $filter, '', '100', $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
