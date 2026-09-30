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
 * Order management tools using PrestaShop webservices
 */
class OrderTools extends AbstractWebservice
{
    /**
     * Resource name for orders
     */
    private const RESOURCE = 'orders';

    /**
     * Sensitive fields that must never be returned in responses
     */
    private const SENSITIVE_FIELDS = [
        'secure_key',
    ];

    /**
     * MCP fields definitions for Order
     */
    private const ORDER_MCP_FIELDS = [
        'id_customer' => [
            'type' => 'integer',
            'description' => 'Customer ID who placed the order',
        ],
        'id_cart' => [
            'type' => 'integer',
            'description' => 'Cart ID that was converted to this order',
        ],
        'id_currency' => [
            'type' => 'integer',
            'description' => 'Currency ID used for the order',
        ],
        'id_lang' => [
            'type' => 'integer',
            'description' => 'Language ID for the order',
        ],
        'id_address_delivery' => [
            'type' => 'integer',
            'description' => 'Delivery address ID',
        ],
        'id_address_invoice' => [
            'type' => 'integer',
            'description' => 'Invoice address ID',
        ],
        'id_carrier' => [
            'type' => 'integer',
            'description' => 'Carrier ID for shipping',
        ],
        'current_state' => [
            'type' => 'integer',
            'description' => 'Current order state ID',
        ],
        'payment' => [
            'type' => 'string',
            'description' => 'Payment method name',
        ],
        'module' => [
            'type' => 'string',
            'description' => 'Payment module name',
        ],
        'total_paid' => [
            'type' => 'number',
            'description' => 'Total amount paid',
        ],
        'total_paid_tax_incl' => [
            'type' => 'number',
            'description' => 'Total paid including tax',
        ],
        'total_paid_tax_excl' => [
            'type' => 'number',
            'description' => 'Total paid excluding tax',
        ],
        'total_paid_real' => [
            'type' => 'number',
            'description' => 'Total actually paid (may differ from total_paid)',
        ],
        'total_products' => [
            'type' => 'number',
            'description' => 'Total products price excluding tax',
        ],
        'total_products_wt' => [
            'type' => 'number',
            'description' => 'Total products price including tax',
        ],
        'total_shipping' => [
            'type' => 'number',
            'description' => 'Total shipping cost',
        ],
        'total_shipping_tax_incl' => [
            'type' => 'number',
            'description' => 'Total shipping including tax',
        ],
        'total_shipping_tax_excl' => [
            'type' => 'number',
            'description' => 'Total shipping excluding tax',
        ],
        'total_discounts' => [
            'type' => 'number',
            'description' => 'Total discount amount (deprecated, use tax_incl/excl)',
        ],
        'total_discounts_tax_incl' => [
            'type' => 'number',
            'description' => 'Total discount including tax',
        ],
        'total_discounts_tax_excl' => [
            'type' => 'number',
            'description' => 'Total discount excluding tax',
        ],
        'total_wrapping' => [
            'type' => 'number',
            'description' => 'Gift wrapping cost',
        ],
        'total_wrapping_tax_incl' => [
            'type' => 'number',
            'description' => 'Gift wrapping cost including tax',
        ],
        'total_wrapping_tax_excl' => [
            'type' => 'number',
            'description' => 'Gift wrapping cost excluding tax',
        ],
        'conversion_rate' => [
            'type' => 'number',
            'description' => 'Currency conversion rate at order time',
        ],
        'reference' => [
            'type' => 'string',
            'description' => 'Order reference (unique)',
        ],
        'recyclable' => [
            'type' => 'boolean',
            'description' => 'Whether customer wants recyclable packaging',
        ],
        'gift' => [
            'type' => 'boolean',
            'description' => 'Whether order is a gift',
        ],
        'gift_message' => [
            'type' => 'string',
            'description' => 'Gift message',
        ],
        'invoice_number' => [
            'type' => 'integer',
            'description' => 'Invoice number',
        ],
        'invoice_date' => [
            'type' => 'string',
            'description' => 'Invoice date (YYYY-MM-DD HH:MM:SS)',
        ],
        'delivery_number' => [
            'type' => 'integer',
            'description' => 'Delivery slip number',
        ],
        'delivery_date' => [
            'type' => 'string',
            'description' => 'Delivery date (YYYY-MM-DD HH:MM:SS)',
        ],
        'valid' => [
            'type' => 'boolean',
            'description' => 'Whether order is validated',
        ],
        'round_mode' => [
            'type' => 'integer',
            'description' => 'Rounding mode (0=round_up, 1=round_down, 2=round_half_up)',
        ],
        'round_type' => [
            'type' => 'integer',
            'description' => 'Rounding type (0=per_item, 1=per_line, 2=per_total)',
        ],
    ];

    #[PsMcpTool(
        name: 'get_orders',
        title: 'Get Orders',
        description: 'Retrieve a list of orders from the store using PrestaShop webservices. Default limit is 10 orders.',
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
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'filter' => ['type' => 'string', 'description' => 'Filter results. Format: "field=[value]" for exact match (case-insensitive). Multiple fields use comma: "current_state=2,id_customer=5". Operators: "[val1|val2]" (OR list), "[min,max]" (range/interval), "[prefix]%" (begins with), "%[suffix]" (ends with), "%[value]%" (contains). Examples: "current_state=2", "id=[10|20]", "total_paid=[100,500]", "reference=[REF]%", "current_state=2,id_customer=5". Default: empty'],
            'sort' => ['type' => 'string', 'description' => 'Sort results (e.g., "id_DESC", "total_paid_DESC") - default: empty'],
            'limit' => ['type' => 'string', 'description' => 'Limit results (e.g., "10" or "0,10") - default: 10'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getOrders(
        string $display,
        string $filter = '',
        string $sort = '',
        string $limit = '10',
        ?int $langId = null,
    ): array {
        try {
            return $this->sanitizeOrderResponse($this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_by_id',
        title: 'Get Order by ID',
        description: 'Retrieve a specific order by its ID using PrestaShop webservices',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order to retrieve'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['orderId', 'display']
    )]
    public function getOrderById(int $orderId, string $display, ?int $langId = null): array
    {
        try {
            return $this->sanitizeOrderResponse($this->getResourceById(self::RESOURCE, $orderId, $display, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_order_by_id',
        title: 'Update Order',
        description: 'Update an existing order using PrestaShop webservices. Note: Some fields like totals are usually calculated automatically.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'orders']
    )]
    #[PsMcpSchema(
        properties: [
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order to update'],
            'data' => [
                'type' => 'object',
                'description' => 'Order data to update',
                'properties' => self::ORDER_MCP_FIELDS,
                'additionalProperties' => false,
            ],
        ],
        required: ['orderId', 'data']
    )]
    public function updateOrderById(int $orderId, array $data): array
    {
        try {
            return $this->sanitizeOrderResponse($this->updateResource(self::RESOURCE, $orderId, $data));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_orders_by_customer',
        title: 'Get Orders by Customer',
        description: 'Get all orders placed by a specific customer',
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
            'customerId' => ['type' => 'integer', 'description' => 'ID of the customer'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['customerId', 'display']
    )]
    public function getOrdersByCustomer(
        int $customerId,
        string $display,
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $filter = "id_customer={$customerId}";

            $orders = $this->getResourceList(self::RESOURCE, $display, $filter, '', $limit, $langId);

            if (!empty($orders)) {
                // Sort manually by date_add
                usort($orders['orders'], function ($a, $b) {
                    return strtotime($b['date_add']) <=> strtotime($a['date_add']);
                });
            }

            return $this->sanitizeOrderResponse($orders);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_orders_by_state',
        title: 'Get Orders by State',
        description: 'Get all orders in a specific state (e.g., pending, processing, shipped, delivered)',
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
            'stateId' => ['type' => 'integer', 'description' => 'ID of the order state (2=Payment accepted, 3=Preparation in progress, 4=Shipped, 5=Delivered, etc.)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['stateId', 'display']
    )]
    public function getOrdersByState(
        int $stateId,
        string $display,
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $filter = "current_state={$stateId}";

            $orders = $this->getResourceList(self::RESOURCE, $display, $filter, '', $limit, $langId);

            if (!empty($orders)) {
                // Sort manually by date_add
                usort($orders['orders'], function ($a, $b) {
                    return strtotime($b['date_add']) <=> strtotime($a['date_add']);
                });
            }

            return $this->sanitizeOrderResponse($orders);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_details',
        title: 'Get Order Details',
        description: 'Get order details (order lines/products) for a specific order',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,product_name,product_quantity,unit_price_tax_incl]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['orderId', 'display']
    )]
    public function getOrderDetails(int $orderId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_order={$orderId}";

            return $this->getResourceList('order_details', $display, $filter, '', '100', $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_history',
        title: 'Get Order History',
        description: 'Get order history (state changes) for a specific order',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,id_order_state,date_add]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['orderId', 'display']
    )]
    public function getOrderHistory(int $orderId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_order={$orderId}";

            $orderHistories = $this->getResourceList('order_histories', $display, $filter, '', '100', $langId);

            if (!empty($orderHistories)) {
                // Sort manually by date_add
                usort($orderHistories['order_histories'], function ($a, $b) {
                    return strtotime($b['date_add']) <=> strtotime($a['date_add']);
                });
            }

            return $orderHistories;
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_order_state',
        title: 'Update Order State',
        description: 'Update order state by creating a new order history entry. This changes the order status (e.g., to shipped, delivered).',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order'],
            'newStateId' => ['type' => 'integer', 'description' => 'New order state ID (2=Payment accepted, 3=Preparation in progress, 4=Shipped, 5=Delivered, etc.)'],
        ],
        required: ['orderId', 'newStateId']
    )]
    public function updateOrderState(int $orderId, int $newStateId): array
    {
        try {
            $data = [
                'id_order' => $orderId,
                'id_order_state' => $newStateId,
            ];

            return $this->createResource('order_histories', $data);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_carriers',
        title: 'Get Order Carriers',
        description: 'Get order carriers (shipping information with tracking) for a specific order',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,tracking_number,id_carrier,weight]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['orderId', 'display']
    )]
    public function getOrderCarriers(int $orderId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_order={$orderId}";

            return $this->getResourceList('order_carriers', $display, $filter, '', '100', $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_invoices',
        title: 'Get Order Invoices',
        description: 'Get order invoices for a specific order',
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
            'orderId' => ['type' => 'integer', 'description' => 'ID of the order'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,number,total_paid_tax_incl,date_add]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['orderId', 'display']
    )]
    public function getOrderInvoices(int $orderId, string $display, ?int $langId = null): array
    {
        try {
            $filter = "id_order={$orderId}";

            return $this->getResourceList('order_invoices', $display, $filter, '', '100', $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_recent_orders',
        title: 'Get Recent Orders',
        description: 'Get orders created within a specific date range',
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
            'startDate' => ['type' => 'string', 'description' => 'Start date (format: YYYY-MM-DD HH:MM:SS or YYYY-MM-DD)'],
            'endDate' => ['type' => 'string', 'description' => 'End date (format: YYYY-MM-DD HH:MM:SS or YYYY-MM-DD) - default: now'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: date_add_DESC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['startDate', 'display']
    )]
    public function getRecentOrders(
        string $startDate,
        string $display,
        string $endDate = '',
        string $sort = 'date_add_DESC',
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            // Ensure date format includes time
            if (!preg_match('/\d{2}:\d{2}:\d{2}/', $startDate)) {
                $startDate .= ' 00:00:00';
            }

            if ($endDate === '') {
                $endDate = date('Y-m-d H:i:s');
            } elseif (!preg_match('/\d{2}:\d{2}:\d{2}/', $endDate)) {
                $endDate .= ' 23:59:59';
            }

            // Note: date filtering requires date=1 parameter and uses brackets for range
            $params = [
                'display' => $display,
                'date' => '1',
                'sort' => $sort !== '' ? $sort : null,
                'limit' => $limit !== '' ? $limit : null,
            ];

            // Add date filter directly to params (not through parseFilterParams)
            $params['filter[date_add]'] = "[{$startDate},{$endDate}]";

            if ($langId !== null) {
                $params['language'] = $langId;
            }

            // Remove null values
            $params = array_filter($params, function ($value) {
                return $value !== null;
            });

            $result = $this->executeWebserviceRequest('GET', self::RESOURCE, $params);

            return $this->sanitizeOrderResponse($this->parseWebserviceResponse($result));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_orders_by_payment_method',
        title: 'Get Orders by Payment Method',
        description: 'Get orders filtered by payment method',
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
            'paymentMethod' => ['type' => 'string', 'description' => 'Payment method name (e.g., "PayPal", "Bank wire", "Credit card"). For multiple methods (OR search), separate them with pipe | WITHOUT spaces (e.g., "PayPal|Bank wire|Credit card")'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,reference,total_paid,current_state]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['paymentMethod', 'display']
    )]
    public function getOrdersByPaymentMethod(
        string $paymentMethod,
        string $display,
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $filter = "payment=[{$paymentMethod}]%";

            $orders = $this->getResourceList(self::RESOURCE, $display, $filter, '', $limit, $langId);

            if (!empty($orders)) {
                // Sort manually by date_add
                usort($orders['orders'], function ($a, $b) {
                    return strtotime($b['date_add']) <=> strtotime($a['date_add']);
                });
            }

            return $this->sanitizeOrderResponse($orders);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_order_states',
        title: 'Get Order States',
        description: 'Get all available order states (statuses) in the system',
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
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,name,color]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 100'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getOrderStates(string $display, string $limit = '100', ?int $langId = null): array
    {
        try {
            return $this->getResourceList('order_states', $display, '', 'id_ASC', $limit, $langId);
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Remove sensitive fields from order response data recursively
     */
    private function sanitizeOrderResponse(array $response): array
    {
        foreach ($response as $key => $value) {
            if (is_string($key) && in_array($key, self::SENSITIVE_FIELDS, true)) {
                unset($response[$key]);
            } elseif (is_array($value)) {
                $response[$key] = $this->sanitizeOrderResponse($value);
            }
        }

        return $response;
    }
}
