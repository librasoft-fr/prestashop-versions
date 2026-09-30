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
 * Read-only MCP tools to enumerate manufacturers (brands) and suppliers.
 *
 * Products can already be filtered by manufacturer/supplier elsewhere, but the
 * brands and suppliers themselves were not listable — these fill that gap.
 */
class BrandSupplierTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_manufacturers',
        description: 'List manufacturers (brands) with their product counts. Default limit 10.',
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
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active manufacturers (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getManufacturers(?int $langId = null, bool $activeOnly = true, int $limit = 10): array
    {
        try {
            $manufacturers = \Manufacturer::getManufacturers(true, $this->resolveLangId($langId), $activeOnly);
            $manufacturers = is_array($manufacturers) ? $manufacturers : [];

            return ['content' => array_slice($manufacturers, 0, $this->clampLimit($limit))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_manufacturer_by_id',
        description: 'Get a single manufacturer (brand) by id.',
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
            'id' => ['type' => 'integer', 'description' => 'Manufacturer id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: ['id']
    )]
    public function getManufacturerById(int $id, ?int $langId = null): array
    {
        try {
            $manufacturer = new \Manufacturer($id, $this->resolveLangId($langId));
            if (!\Validate::isLoadedObject($manufacturer)) {
                throw new \RuntimeException(sprintf('Manufacturer %d not found', $id));
            }

            return ['content' => [
                'id' => (int) $manufacturer->id,
                'name' => $manufacturer->name,
                'description' => $manufacturer->description,
                'active' => (bool) $manufacturer->active,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_suppliers',
        description: 'List suppliers with their product counts. Default limit 10.',
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
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active suppliers (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getSuppliers(?int $langId = null, bool $activeOnly = true, int $limit = 10): array
    {
        try {
            $suppliers = \Supplier::getSuppliers(true, $this->resolveLangId($langId), $activeOnly);
            $suppliers = is_array($suppliers) ? $suppliers : [];

            return ['content' => array_slice($suppliers, 0, $this->clampLimit($limit))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_supplier_by_id',
        description: 'Get a single supplier by id.',
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
            'id' => ['type' => 'integer', 'description' => 'Supplier id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: ['id']
    )]
    public function getSupplierById(int $id, ?int $langId = null): array
    {
        try {
            $supplier = new \Supplier($id, $this->resolveLangId($langId));
            if (!\Validate::isLoadedObject($supplier)) {
                throw new \RuntimeException(sprintf('Supplier %d not found', $id));
            }

            return ['content' => [
                'id' => (int) $supplier->id,
                'name' => $supplier->name,
                'description' => $supplier->description,
                'active' => (bool) $supplier->active,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
