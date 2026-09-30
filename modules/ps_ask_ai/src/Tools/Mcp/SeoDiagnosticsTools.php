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
 * Read-only MCP tools that rate the catalogue's SEO coverage and surface a shop
 * health snapshot.
 *
 * diagnose_shop_performance reports data integrity, recent error-log volume and
 * key configuration flags. It does NOT measure page speed / load times:
 * PrestaShop stores no page-timing metrics and the MCP runtime can't profile the
 * front office, so that dimension is intentionally omitted.
 */
class SeoDiagnosticsTools extends AbstractMcpTool
{
    #[PsMcpTool(
        name: 'get_catalog_seo_health',
        description: 'Rate the catalogue against SEO best practices: share of products with a meta title, meta description, link rewrite (friendly URL), an image and a description, with counts, percentages and a sample of offending product ids.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'seo']
    )]
    #[PsMcpSchema(
        properties: [
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: []
    )]
    public function getCatalogSeoHealth(?int $langId = null): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);

            $total = (int) (($this->rows((new \DbQuery())
                ->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop))[0] ?? [])['nb'] ?? 0);

            $withMetaTitle = $this->countLangField($idShop, $idLang, "pl.meta_title <> '' AND pl.meta_title IS NOT NULL");
            $withMetaDescription = $this->countLangField($idShop, $idLang, "pl.meta_description <> '' AND pl.meta_description IS NOT NULL");
            $withLinkRewrite = $this->countLangField($idShop, $idLang, "pl.link_rewrite <> '' AND pl.link_rewrite IS NOT NULL");
            $withDescription = $this->countLangField($idShop, $idLang, "pl.description <> '' AND pl.description IS NOT NULL");

            $withImage = (int) (($this->rows((new \DbQuery())
                ->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where('ps.id_product IN (SELECT i.id_product FROM ' . _DB_PREFIX_ . 'image i)'))[0] ?? [])['nb'] ?? 0);

            // A handful of ids missing a meta description, to act on first.
            $sample = array_map(static fn (array $r): int => (int) $r['id_product'], $this->rows((new \DbQuery())
                ->select('ps.id_product')
                ->from('product_shop', 'ps')
                ->leftJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_lang = ' . (int) $idLang . ' AND pl.id_shop = ps.id_shop')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where("pl.meta_description IS NULL OR pl.meta_description = ''")
                ->limit(10)));

            return ['content' => [
                'total_products' => $total,
                'with_meta_title' => $this->coverage($withMetaTitle, $total),
                'with_meta_description' => $this->coverage($withMetaDescription, $total),
                'with_link_rewrite' => $this->coverage($withLinkRewrite, $total),
                'with_image' => $this->coverage($withImage, $total),
                'with_description' => $this->coverage($withDescription, $total),
                'sample_missing_meta_description' => $sample,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    #[PsMcpTool(
        name: 'diagnose_shop_performance',
        description: 'Shop health snapshot: catalogue data integrity (disabled, out-of-stock, missing image/description), recent error-log volume, and key configuration flags (shop online, friendly URLs, SSL). Does not measure page speed/load times — PrestaShop stores no such metrics.',
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'seo']
    )]
    #[PsMcpSchema(
        properties: [
            'errorDays' => ['type' => 'integer', 'description' => 'Look-back window in days for the error log (default 7)'],
            'langId' => ['type' => ['integer', 'null'], 'description' => 'Language id (defaults to the current back-office language)'],
        ],
        required: []
    )]
    public function diagnoseShopPerformance(int $errorDays = 7, ?int $langId = null): array
    {
        try {
            $idShop = $this->resolveShopId(null);
            $idLang = $this->resolveLangId($langId);
            $cutoff = date('Y-m-d 00:00:00', (int) strtotime('-' . max(1, $errorDays) . ' days'));

            $counts = $this->rows((new \DbQuery())
                ->select('COUNT(*) AS total')
                ->select('SUM(ps.active = 0) AS disabled')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop))[0] ?? [];

            $outOfStock = (int) (($this->rows((new \DbQuery())
                ->select('COUNT(DISTINCT sa.id_product) AS nb')
                ->from('stock_available', 'sa')
                ->where('sa.quantity <= 0')
                ->where('(sa.id_shop = ' . (int) $idShop . ' OR sa.id_shop = 0)'))[0] ?? [])['nb'] ?? 0);

            $noImage = (int) (($this->rows((new \DbQuery())
                ->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where('ps.id_product NOT IN (SELECT i.id_product FROM ' . _DB_PREFIX_ . 'image i)'))[0] ?? [])['nb'] ?? 0);

            $noDescription = $this->rows((new \DbQuery())
                ->select('COUNT(*) AS nb')
                ->from('product_shop', 'ps')
                ->innerJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_shop = ps.id_shop AND pl.id_lang = ' . (int) $idLang)
                ->where('ps.id_shop = ' . (int) $idShop)
                ->where("pl.description = '' OR pl.description IS NULL"))[0] ?? [];

            // Recent error log volume (severity >= 2 are warnings/errors).
            $recentErrors = (int) (($this->rows((new \DbQuery())
                ->select('COUNT(*) AS nb')
                ->from('log')
                ->where('severity >= 2')
                ->where("date_add >= '" . pSQL($cutoff) . "'"))[0] ?? [])['nb'] ?? 0);

            return ['content' => [
                'catalog' => [
                    'total_products' => (int) ($counts['total'] ?? 0),
                    'disabled_products' => (int) ($counts['disabled'] ?? 0),
                    'out_of_stock_products' => $outOfStock,
                    'products_without_image' => $noImage,
                    'products_without_description' => (int) ($noDescription['nb'] ?? 0),
                ],
                'errors' => [
                    'since' => $cutoff,
                    'recent_errors' => $recentErrors,
                ],
                'configuration' => [
                    'shop_online' => (bool) \Configuration::get('PS_SHOP_ENABLE'),
                    'friendly_urls' => (bool) \Configuration::get('PS_REWRITING_SETTINGS'),
                    'ssl_enabled' => (bool) \Configuration::get('PS_SSL_ENABLED'),
                    'ssl_enabled_everywhere' => (bool) \Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
                ],
                'note' => 'Page speed/load times are not measured; PrestaShop stores no page-timing metrics.',
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Count products whose default-language translation row satisfies $condition.
     */
    private function countLangField(int $idShop, int $idLang, string $condition): int
    {
        $query = new \DbQuery();
        $query->select('COUNT(*) AS nb')
            ->from('product_shop', 'ps')
            ->innerJoin('product_lang', 'pl', 'pl.id_product = ps.id_product AND pl.id_shop = ps.id_shop AND pl.id_lang = ' . (int) $idLang)
            ->where('ps.id_shop = ' . (int) $idShop)
            ->where($condition);

        return (int) (($this->rows($query)[0] ?? [])['nb'] ?? 0);
    }

    /**
     * Build a {count, percent} coverage figure relative to the catalogue total.
     *
     * @return array{count: int, percent: float}
     */
    private function coverage(int $count, int $total): array
    {
        return [
            'count' => $count,
            'percent' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
        ];
    }
}
