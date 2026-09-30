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
 * Customer management tools using PrestaShop webservices
 */
class CustomerTools extends AbstractWebservice
{
    /**
     * Resource name for customers
     */
    private const RESOURCE = 'customers';

    /**
     * Sensitive fields that must never be returned in responses
     */
    private const SENSITIVE_FIELDS = [
        'passwd',
        'reset_password_token',
        'reset_password_validity',
        'secure_key',
        'last_passwd_gen',
    ];

    /**
     * MCP fields definitions for Customer
     */
    private const CUSTOMER_MCP_FIELDS = [
        'firstname' => [
            'type' => 'string',
            'description' => 'Customer first name',
        ],
        'lastname' => [
            'type' => 'string',
            'description' => 'Customer last name',
        ],
        'email' => [
            'type' => 'string',
            'description' => 'Customer email address (must be unique)',
        ],
        'passwd' => [
            'type' => 'string',
            'description' => 'Customer password (plain text, will be hashed by PrestaShop)',
        ],
        'id_default_group' => [
            'type' => 'integer',
            'description' => 'Default customer group ID (default: 3 for regular customers)',
        ],
        'id_gender' => [
            'type' => 'integer',
            'description' => 'Gender ID (1=Mr, 2=Mrs, 3=Other)',
        ],
        'birthday' => [
            'type' => 'string',
            'description' => 'Date of birth (format: YYYY-MM-DD)',
        ],
        'newsletter' => [
            'type' => 'boolean',
            'description' => 'Whether customer is subscribed to newsletter',
        ],
        'optin' => [
            'type' => 'boolean',
            'description' => 'Whether customer has opted in for partner offers',
        ],
        'active' => [
            'type' => 'boolean',
            'description' => 'Whether the customer account is active',
        ],
        'note' => [
            'type' => 'string',
            'description' => 'Private note about the customer',
        ],
        'id_shop' => [
            'type' => 'integer',
            'description' => 'Shop ID the customer belongs to',
        ],
        'company' => [
            'type' => 'string',
            'description' => 'Customer company name (for B2B)',
        ],
        'siret' => [
            'type' => 'string',
            'description' => 'Company SIRET number (for B2B)',
        ],
        'website' => [
            'type' => 'string',
            'description' => 'Customer website',
        ],
    ];

    #[PsMcpTool(
        name: 'get_customers',
        title: 'Get Customers',
        description: 'Retrieve a list of customers from the store using PrestaShop webservices. Default limit is 10 customers.',
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
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'filter' => ['type' => 'string', 'description' => 'Filter results. Format: "field=[value]" for exact match (case-insensitive). Multiple fields use comma: "active=1,newsletter=1". Operators: "[val1|val2]" (OR list), "[min,max]" (range/interval), "[prefix]%" (begins with), "%[suffix]" (ends with), "%[value]%" (contains). Examples: "active=1", "id=[5|10]", "email=[test]%", "id_default_group=3", "active=1,newsletter=1". Default: empty'],
            'sort' => ['type' => 'string', 'description' => 'Sort results (e.g., "id_ASC", "email_DESC", "date_add_DESC") - default: empty'],
            'limit' => ['type' => 'string', 'description' => 'Limit results (e.g., "10" or "0,10") - default: 10'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getCustomers(
        string $display,
        string $filter = '',
        string $sort = '',
        string $limit = '10',
        ?int $langId = null,
    ): array {
        try {
            return $this->sanitizeCustomerResponse($this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_customer_by_id',
        title: 'Get Customer by ID',
        description: 'Retrieve a specific customer by their ID using PrestaShop webservices',
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
            'customerId' => ['type' => 'integer', 'description' => 'ID of the customer to retrieve'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['customerId', 'display']
    )]
    public function getCustomerById(int $customerId, string $display, ?int $langId = null): array
    {
        try {
            return $this->sanitizeCustomerResponse($this->getResourceById(self::RESOURCE, $customerId, $display, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_customer',
        title: 'Create Customer',
        description: 'Create a new customer using PrestaShop webservices',
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
            'data' => [
                'type' => 'object',
                'description' => 'Customer data. Email must be unique.',
                'properties' => self::CUSTOMER_MCP_FIELDS,
                'additionalProperties' => false,
                'required' => [
                    'firstname',
                    'lastname',
                    'email',
                    'passwd',
                ],
            ],
        ],
        required: ['data']
    )]
    public function createCustomer(array $data): array
    {
        try {
            // Set defaults
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            if (!isset($data['id_default_group'])) {
                $data['id_default_group'] = 3; // Default customer group
            }
            if (!isset($data['newsletter'])) {
                $data['newsletter'] = 0;
            }
            if (!isset($data['optin'])) {
                $data['optin'] = 0;
            }
            if (!isset($data['id_shop'])) {
                $data['id_shop'] = (int) \Configuration::get('PS_SHOP_DEFAULT') ?: 1;
            }

            return $this->sanitizeCustomerResponse($this->createResource(self::RESOURCE, $data));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_customer_by_id',
        title: 'Update Customer',
        description: 'Update an existing customer using PrestaShop webservices',
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
            'customerId' => ['type' => 'integer', 'description' => 'ID of the customer to update'],
            'data' => [
                'type' => 'object',
                'description' => 'Customer data to update',
                'properties' => self::CUSTOMER_MCP_FIELDS,
                'additionalProperties' => false,
            ],
        ],
        required: ['customerId', 'data']
    )]
    public function updateCustomerById(int $customerId, array $data): array
    {
        try {
            return $this->sanitizeCustomerResponse($this->updateResource(self::RESOURCE, $customerId, $data));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'search_customer_by_email',
        title: 'Search Customer by Email',
        description: 'Search for customers by email address. Supports exact match or partial match (LIKE operator).',
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
            'email' => ['type' => 'string', 'description' => 'Email to search for. For multiple emails (OR search), separate them with pipe | WITHOUT spaces (e.g., "email1@test.com|email2@test.com"). Use % for wildcard (e.g., "test%" for emails starting with test)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['email', 'display']
    )]
    public function searchCustomerByEmail(string $email, string $display, ?int $langId = null): array
    {
        try {
            $filter = "email=[{$email}]";

            return $this->sanitizeCustomerResponse($this->getResourceList(self::RESOURCE, $display, $filter, '', '100', $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_newsletter_subscribers',
        title: 'Get Newsletter Subscribers',
        description: 'Get all customers subscribed to newsletter',
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
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: email_ASC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 100'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['display']
    )]
    public function getNewsletterSubscribers(
        string $display,
        bool $active = true,
        string $sort = 'email_ASC',
        string $limit = '100',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "newsletter=1,active={$activeValue}";

            return $this->sanitizeCustomerResponse($this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_customers_by_group',
        title: 'Get Customers by Group',
        description: 'Get customers belonging to a specific customer group',
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
            'groupId' => ['type' => 'integer', 'description' => 'ID of the customer group (3=Customer, 4=Guest, etc.)'],
            'active' => ['type' => 'boolean', 'description' => 'Filter by active status (default: true)'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: id_ASC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['groupId', 'display']
    )]
    public function getCustomersByGroup(
        int $groupId,
        string $display,
        bool $active = true,
        string $sort = 'id_ASC',
        string $limit = '50',
        ?int $langId = null,
    ): array {
        try {
            $activeValue = $active ? '1' : '0';
            $filter = "id_default_group={$groupId},active={$activeValue}";

            return $this->sanitizeCustomerResponse($this->getResourceList(self::RESOURCE, $display, $filter, $sort, $limit, $langId));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_recently_registered_customers',
        title: 'Get Recently Registered Customers',
        description: 'Get customers registered within a specific date range',
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
            'startDate' => ['type' => 'string', 'description' => 'Start date (format: YYYY-MM-DD HH:MM:SS or YYYY-MM-DD)'],
            'endDate' => ['type' => 'string', 'description' => 'End date (format: YYYY-MM-DD HH:MM:SS or YYYY-MM-DD) - default: now'],
            'display' => ['type' => 'string', 'description' => 'IMPORTANT: Specify only the fields you need to minimize token usage. Use "[field1,field2,field3]" format (e.g., "[id,email,firstname,lastname,active]") to retrieve specific fields. Use "full" only when you need all fields. This significantly reduces response size and token consumption.'],
            'sort' => ['type' => 'string', 'description' => 'Sort results - default: date_add_DESC'],
            'limit' => ['type' => 'string', 'description' => 'Limit results - default: 50'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language ID to filter multilang fields (optional)'],
        ],
        required: ['startDate', 'display']
    )]
    public function getRecentlyRegisteredCustomers(
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

            return $this->sanitizeCustomerResponse($this->parseWebserviceResponse($result));
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Remove sensitive fields from customer response data recursively
     */
    private function sanitizeCustomerResponse(array $response): array
    {
        foreach ($response as $key => $value) {
            if (is_string($key) && in_array($key, self::SENSITIVE_FIELDS, true)) {
                unset($response[$key]);
            } elseif (is_array($value)) {
                $response[$key] = $this->sanitizeCustomerResponse($value);
            }
        }

        return $response;
    }
}
