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
 * MCP tools to read and manage CMS pages (e.g. shipping/returns policies).
 *
 * Create/update tools are not read-only and pass through operator approval.
 */
class CmsTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_cms_pages',
        description: 'List CMS content pages (id, title, link, active). Default limit 20.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'content']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
            'activeOnly' => ['type' => 'boolean', 'description' => 'Only active pages (default true)'],
            'limit' => ['type' => 'integer', 'description' => 'Max rows, 1-100 (default 20)'],
        ],
        required: []
    )]
    public function getCmsPages(?int $langId = null, bool $activeOnly = true, int $limit = 20): array
    {
        try {
            $pages = \CMS::getCMSPages($this->resolveLangId($langId), null, $activeOnly, $this->resolveShopId(null));
            $pages = is_array($pages) ? $pages : [];

            return ['content' => array_slice($pages, 0, $this->clampLimit($limit))];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'get_cms_page_by_id',
        description: 'Get a single CMS page by id, including its HTML content.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'content']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'description' => 'CMS page id'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: ['id']
    )]
    public function getCmsPageById(int $id, ?int $langId = null): array
    {
        try {
            $cms = new \CMS($id, $this->resolveLangId($langId));
            if (!\Validate::isLoadedObject($cms)) {
                throw new \RuntimeException(sprintf('CMS page %d not found', $id));
            }

            return ['content' => [
                'id' => (int) $cms->id,
                'meta_title' => $cms->meta_title,
                'meta_description' => $cms->meta_description,
                'link_rewrite' => $cms->link_rewrite,
                'content' => $cms->content,
                'active' => (bool) $cms->active,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'create_cms_page',
        description: 'Create a CMS content page. The link is derived from the title when not supplied.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: false,
            openWorldHint: false
        ),
        meta: ['category' => 'content']
    )]
    #[PsMcpSchema(
        properties: [
            'title' => ['type' => 'string', 'description' => 'Page title (meta title)'],
            'content' => ['type' => 'string', 'description' => 'HTML body of the page'],
            'metaDescription' => ['type' => ['string', 'null'], 'description' => 'Meta description (optional)'],
            'linkRewrite' => ['type' => ['string', 'null'], 'description' => 'URL slug (defaults to a slug of the title)'],
            'categoryId' => ['type' => 'integer', 'description' => 'CMS category id (default 1, the root)'],
            'active' => ['type' => 'boolean', 'description' => 'Whether the page is published (default true)'],
        ],
        required: ['title', 'content']
    )]
    public function createCmsPage(
        string $title,
        string $content,
        ?string $metaDescription = null,
        ?string $linkRewrite = null,
        int $categoryId = 1,
        bool $active = true,
    ): array {
        try {
            $cms = new \CMS();
            $cms->id_cms_category = max(1, $categoryId);
            $cms->meta_title = $this->multilangValue($title);
            $cms->meta_description = $this->multilangValue((string) ($metaDescription ?? ''));
            $cms->content = $this->multilangValue($content);
            $cms->link_rewrite = $this->multilangValue(\Tools::str2url($linkRewrite ?: $title) ?: 'page');
            $cms->active = $active ? 1 : 0;

            if (!$cms->add()) {
                throw new \RuntimeException('Failed to create the CMS page.');
            }

            return ['content' => ['id' => (int) $cms->id]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'update_cms_page_by_id',
        description: 'Update fields of an existing CMS page. Only the provided fields change.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: false,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'content']
    )]
    #[PsMcpSchema(
        properties: [
            'id' => ['type' => 'integer', 'description' => 'CMS page id'],
            'title' => ['type' => ['string', 'null'], 'description' => 'New title'],
            'content' => ['type' => ['string', 'null'], 'description' => 'New HTML body'],
            'metaDescription' => ['type' => ['string', 'null'], 'description' => 'New meta description'],
            'active' => ['type' => ['boolean', 'null'], 'description' => 'Publish/unpublish the page'],
        ],
        required: ['id']
    )]
    public function updateCmsPageById(
        int $id,
        ?string $title = null,
        ?string $content = null,
        ?string $metaDescription = null,
        ?bool $active = null,
    ): array {
        try {
            $cms = new \CMS($id);
            if (!\Validate::isLoadedObject($cms)) {
                throw new \RuntimeException(sprintf('CMS page %d not found', $id));
            }

            if ($title !== null) {
                $cms->meta_title = $this->multilangValue($title);
            }
            if ($content !== null) {
                $cms->content = $this->multilangValue($content);
            }
            if ($metaDescription !== null) {
                $cms->meta_description = $this->multilangValue($metaDescription);
            }
            if ($active !== null) {
                $cms->active = $active ? 1 : 0;
            }

            if (!$cms->update()) {
                throw new \RuntimeException('Failed to update the CMS page.');
            }

            return ['content' => ['id' => (int) $cms->id, 'updated' => true]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
