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
 * Read-only MCP tools for customer-service triage: browsing customer-service
 * threads and reading the messages in a thread.
 */
class CustomerServiceTools extends AbstractMcpTool
{
    /** Valid customer_thread statuses in PrestaShop. */
    private const THREAD_STATUSES = ['open', 'closed', 'pending1', 'pending2'];

    #[PsMcpTool(
        name: 'get_customer_threads',
        description: 'List customer-service threads (id, customer email, status, linked order, message count, dates), newest activity first. Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customer-service']
    )]
    #[PsMcpSchema(
        properties: [
            'status' => ['type' => ['string', 'null'], 'enum' => ['open', 'closed', 'pending1', 'pending2'], 'description' => 'Filter by thread status (optional)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getCustomerThreads(?string $status = null, int $limit = 20): array
    {
        try {
            $query = new \DbQuery();
            $query->select('ct.id_customer_thread, ct.id_customer, ct.id_order, ct.email, ct.status, ct.date_add, ct.date_upd')
                ->select('(SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customer_message cm WHERE cm.id_customer_thread = ct.id_customer_thread) AS messages_count')
                ->from('customer_thread', 'ct')
                ->where('ct.id_shop = ' . (int) $this->resolveShopId(null));
            if ($status !== null && in_array($status, self::THREAD_STATUSES, true)) {
                $query->where("ct.status = '" . pSQL($status) . "'");
            }
            $query->orderBy('ct.date_upd DESC')->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_customer_thread' => (int) $r['id_customer_thread'],
                    'id_customer' => (int) $r['id_customer'],
                    'id_order' => (int) $r['id_order'],
                    'email' => (string) $r['email'],
                    'status' => (string) $r['status'],
                    'messages_count' => (int) $r['messages_count'],
                    'date_add' => (string) $r['date_add'],
                    'date_upd' => (string) $r['date_upd'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_customer_messages',
        description: 'Read the messages of a customer-service thread in chronological order. Each message indicates whether it came from an employee and whether it is a private internal note.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'customer-service']
    )]
    #[PsMcpSchema(
        properties: [
            'threadId' => ['type' => 'integer', 'description' => 'Customer thread id'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 50)'],
        ],
        required: ['threadId']
    )]
    public function getCustomerMessages(int $threadId, int $limit = 50): array
    {
        try {
            $query = new \DbQuery();
            $query->select('cm.id_customer_message, cm.id_employee, cm.message, cm.private, cm.`read`, cm.date_add')
                ->from('customer_message', 'cm')
                ->where('cm.id_customer_thread = ' . (int) $threadId)
                ->orderBy('cm.date_add ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => array_map(static function (array $r): array {
                return [
                    'id_customer_message' => (int) $r['id_customer_message'],
                    'from_employee' => (int) $r['id_employee'] > 0,
                    'private_note' => (bool) $r['private'],
                    'read' => (bool) $r['read'],
                    'message' => (string) $r['message'],
                    'date_add' => (string) $r['date_add'],
                ];
            }, $this->rows($query))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
