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
 * Read-only MCP tools for browsing the category tree and the products it holds.
 */
class CategoryTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_categories',
        description: 'List shop categories (id, parent, name, active, position). Filter by parent to walk the tree. Default limit 10.',
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
            'parentId' => ['type' => ['integer', 'null'], 'description' => 'Only return direct children of this category id'],
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active categories (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: []
    )]
    public function getCategories(?int $langId = null, ?int $parentId = null, bool $activeOnly = true, int $limit = 10): array
    {
        try {
            $query = $this->baseCategoryQuery($this->resolveLangId($langId));
            if ($activeOnly) {
                $query->where('c.active = 1');
            }
            if ($parentId !== null && $parentId > 0) {
                $query->where('c.id_parent = ' . (int) $parentId);
            }
            $query->orderBy('c.nleft ASC')->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_category_by_id',
        description: 'Get a single category by id, including name, description, parent and active state.',
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
            'id' => ['type' => 'integer', 'description' => 'Category id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: ['id']
    )]
    public function getCategoryById(int $id, ?int $langId = null): array
    {
        try {
            $category = new \Category($id, $this->resolveLangId($langId));
            if (!\Validate::isLoadedObject($category)) {
                throw new \RuntimeException(sprintf('Category %d not found', $id));
            }

            return ['content' => [
                'id' => (int) $category->id,
                'id_parent' => (int) $category->id_parent,
                'name' => $category->name,
                'description' => $category->description,
                'link_rewrite' => $category->link_rewrite,
                'active' => (bool) $category->active,
                'is_root_category' => (bool) $category->is_root_category,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'search_categories',
        description: 'Find categories whose name contains the given text. Default limit 10.',
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
            'search' => ['type' => 'string', 'description' => 'Text to look for in the category name'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: ['search']
    )]
    public function searchCategories(string $search, ?int $langId = null, int $limit = 10): array
    {
        try {
            $query = $this->baseCategoryQuery($this->resolveLangId($langId));
            $query->where('cl.name LIKE \'%' . pSQL($search) . '%\'')
                ->orderBy('cl.name ASC')
                ->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_products_in_category',
        description: 'List products assigned to a category (id, name, reference, price, active). Default limit 10.',
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
            'categoryId' => ['type' => 'integer', 'description' => 'Category id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active products (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 10)'],
        ],
        required: ['categoryId']
    )]
    public function getProductsInCategory(int $categoryId, ?int $langId = null, bool $activeOnly = true, int $limit = 10): array
    {
        try {
            $idLang = $this->resolveLangId($langId);
            $query = new \DbQuery();
            $query->select('p.id_product, p.reference, p.price, p.active, pl.name')
                ->from('category_product', 'cp')
                ->innerJoin('product', 'p', 'p.id_product = cp.id_product')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = p.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ' . $this->resolveShopId(null))
                ->where('cp.id_category = ' . (int) $categoryId);
            if ($activeOnly) {
                $query->where('p.active = 1');
            }
            $query->orderBy('cp.position ASC')->limit($this->clampLimit($limit));

            return ['content' => $this->rows($query)];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Shared category list query (category joined to its lang row).
     */
    private function baseCategoryQuery(int $idLang): \DbQuery
    {
        $query = new \DbQuery();
        $query->select('c.id_category, c.id_parent, c.active, c.is_root_category, cl.name, cl.link_rewrite')
            ->from('category', 'c')
            ->leftJoin('category_lang', 'cl', 'cl.id_category = c.id_category AND cl.id_lang = ' . (int) $idLang . ' AND cl.id_shop = ' . $this->resolveShopId(null));

        return $query;
    }
}
