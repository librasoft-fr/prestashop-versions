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

/**
 * MCP tool to search the PrestaShop Marketplace (Addons) for modules.
 *
 * Read-only: it queries the public Addons search API and returns matching
 * modules so the assistant can recommend something to install — it never
 * installs anything itself (the install flow lives elsewhere).
 *
 * The Addons endpoint takes numeric `id_lang` / `id_country` / `id_currency`
 * that are Addons-specific ids, NOT PrestaShop DB ids. This tool accepts
 * BO-friendly ISO codes and maps them to those ids via the constant tables
 * below, defaulting from the current shop context when a code is omitted.
 *
 * Results are post-filtered before reaching the model: modules already
 * installed on the shop are dropped (no point recommending them), modules
 * whose declared compatibility range excludes the running PrestaShop version
 * are dropped, and so are modules not available in the shop's country. The
 * tool requires ps_mbo to be installed+enabled because the
 * follow-up install flow depends on it — recommending something that can't
 * be installed would be a dead end.
 */
class MarketplaceTools extends AbstractMcpTool
{
    /** Base URL of the public Addons search API. */
    private const ADDONS_SEARCH_URL = 'https://addons.prestashop.com/request3/api/search/search';

    /**
     * Addons language ids keyed by lowercase ISO code.
     *
     * @var array<string, int>
     */
    private const LANG_IDS = [
        'en' => 1,
        'fr' => 2,
        'es' => 3,
        'de' => 5,
        'it' => 6,
        'nl' => 7,
        'pl' => 8,
        'pt' => 10,
        'ru' => 11,
    ];

    /**
     * Addons currency ids keyed by uppercase ISO code.
     *
     * @var array<string, int>
     */
    private const CURRENCY_IDS = [
        'EUR' => 1,
        'USD' => 2,
        'GBP' => 3,
    ];

    /**
     * Addons country ids keyed by uppercase ISO code.
     *
     * Two notes on the source list: it carries two "Ireland" entries (26 and
     * 245) — we keep IE => 26; and "Guyana" (104) is mapped to GF (French
     * Guiana), the ISO code PrestaShop core ships for that territory.
     *
     * @var array<string, int>
     */
    private const COUNTRY_IDS = [
        'DE' => 1,
        'AT' => 2,
        'BE' => 3,
        'CN' => 5,
        'ES' => 6,
        'FI' => 7,
        'FR' => 8,
        'GR' => 9,
        'IT' => 10,
        'LU' => 12,
        'NL' => 13,
        'PL' => 14,
        'PT' => 15,
        'CZ' => 16,
        'GB' => 17,
        'SE' => 18,
        'CH' => 19,
        'DK' => 20,
        'US' => 21,
        'NO' => 23,
        'IE' => 26,
        'RO' => 36,
        'SK' => 37,
        'HR' => 74,
        'CY' => 76,
        'EE' => 86,
        'GI' => 97,
        'GP' => 98,
        'GF' => 104,
        'LV' => 125,
        'LI' => 130,
        'LT' => 131,
        'MT' => 139,
        'MQ' => 141,
        'YT' => 144,
        'HU' => 143,
        'RE' => 176,
        'MF' => 182,
        'SI' => 193,
        'BG' => 236,
    ];

    /** Fallback Addons ids when an ISO code can't be resolved. */
    private const DEFAULT_LANG_ID = 1;     // en
    private const DEFAULT_CURRENCY_ID = 1; // EUR
    private const DEFAULT_COUNTRY_ID = 8;  // FR

    #[PsMcpTool(
        name: 'search_marketplace_modules',
        description: "Search the PrestaShop Marketplace (Addons) for modules by keyword (e.g. 'chronopost', 'abandoned cart', 'newsletter'). Returns matching modules with name, author, price, rating and store URL, already filtered to modules compatible with this shop's PrestaShop version, available in the shop's country and not yet installed. Read-only — it does not install anything.",
        annotations: new PsMcpToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        ),
        meta: ['category' => 'marketplace']
    )]
    #[PsMcpSchema(
        properties: [
            'searchQuery' => ['type' => 'string', 'description' => 'Keyword(s) to search for (e.g. "chronopost", "abandoned cart")'],
            'langIso' => ['type' => ['string', 'null'], 'description' => 'Two-letter language ISO code for result text (e.g. "en", "fr"). Defaults to the current back-office language.'],
            'countryIso' => ['type' => ['string', 'null'], 'description' => 'Two-letter country ISO code (e.g. "FR", "DE"). Defaults to the shop default country.'],
            'currencyIso' => ['type' => ['string', 'null'], 'description' => 'Three-letter currency ISO code (EUR, USD or GBP). Defaults to the shop default currency.'],
            'page' => ['type' => 'integer', 'description' => 'Results page (1-based, default 1)'],
            'limit' => ['type' => 'integer', 'description' => 'Results per page, 1-100 (default 10)'],
        ],
        required: ['searchQuery']
    )]
    public function searchMarketplaceModules(
        string $searchQuery,
        ?string $langIso = null,
        ?string $countryIso = null,
        ?string $currencyIso = null,
        int $page = 1,
        int $limit = 10,
    ): array {
        try {
            if (!$this->isMboEnabled()) {
                throw new \RuntimeException('Module recommendation is unavailable: the PrestaShop Marketplace module (ps_mbo) must be installed and enabled on this shop. Tell the merchant to install or enable ps_mbo from the Module Manager before searching for modules.');
            }

            $searchQuery = trim($searchQuery);
            if ($searchQuery === '') {
                throw new \InvalidArgumentException('searchQuery must not be empty.');
            }

            // Resolved once to an uppercased ISO: fed to the Addons id map for
            // the request and reused to post-filter results by country.
            $shopCountryIso = $this->resolveCountryIso($countryIso);

            $params = [
                'id_lang' => $this->resolveAddonsLangId($langIso),
                'id_country' => $this->resolveAddonsCountryId($shopCountryIso),
                'id_currency' => $this->resolveAddonsCurrencyId($currencyIso),
                'type' => 'search',
                'page' => max(1, $page),
                'nb_results_per_page' => $this->clampLimit($limit),
                'search_query' => $searchQuery,
            ];

            $url = self::ADDONS_SEARCH_URL . '?' . http_build_query($params);

            $body = \Tools::file_get_contents($url);
            if (!is_string($body) || trim($body) === '') {
                throw new \RuntimeException('No response from the Addons search API.');
            }

            $decoded = json_decode($body, true);
            if (!is_array($decoded) || ($decoded['success'] ?? false) !== true || !isset($decoded['data']['search'])) {
                throw new \RuntimeException('Unexpected response from the Addons search API.');
            }

            $search = $decoded['data']['search'];
            $products = is_array($search['products'] ?? null) ? $search['products'] : [];
            $pagination = is_array($search['pagination'] ?? null) ? $search['pagination'] : [];

            [$products, $dropped] = $this->filterProducts($products, $shopCountryIso);

            $modules = array_values(array_map([$this, 'shapeProduct'], $products));

            return ['content' => [
                'query' => $searchQuery,
                'total_results' => (int) ($pagination['nb_results'] ?? count($modules)),
                'page' => (int) ($pagination['page'] ?? $params['page']),
                'total_pages' => (int) ($pagination['nb_pages'] ?? 1),
                'count' => count($modules),
                'filtered_out_installed' => $dropped['installed'],
                'filtered_out_incompatible' => $dropped['incompatible'],
                'filtered_out_unavailable_country' => $dropped['country'],
                'modules' => $modules,
            ]];
        } catch (\Throwable $e) {
            throw new PsMcpToolCallException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Drop products the shop can't use: already-installed modules, modules
     * whose compatibility range excludes the running PrestaShop version, and
     * modules not available in the shop's country. Returns the kept products
     * plus per-reason drop counts so the model can tell the merchant when
     * results were pruned.
     *
     * @param array<int, mixed> $products
     *
     * @return array{0: array<int, array<string, mixed>>, 1: array{installed: int, incompatible: int, country: int}}
     */
    private function filterProducts(array $products, string $shopCountryIso = ''): array
    {
        $kept = [];
        $dropped = ['installed' => 0, 'incompatible' => 0, 'country' => 0];

        foreach ($products as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $technical = is_array($entry['technical_info'] ?? null) ? $entry['technical_info'] : [];

            $min = isset($technical['compatibility_min']) ? (string) $technical['compatibility_min'] : null;
            $max = isset($technical['compatibility_max']) ? (string) $technical['compatibility_max'] : null;
            if (!self::isCompatibleWithPsVersion($min, $max, _PS_VERSION_)) {
                ++$dropped['incompatible'];
                continue;
            }

            $technicalName = isset($technical['technical_name']) ? (string) $technical['technical_name'] : '';
            if ($technicalName !== '' && $this->isModuleInstalled($technicalName)) {
                ++$dropped['installed'];
                continue;
            }

            $marketing = is_array($entry['marketing_info'] ?? null) ? $entry['marketing_info'] : [];
            $availableCountries = is_array($marketing['available_countries_iso_code'] ?? null) ? $marketing['available_countries_iso_code'] : [];
            if (!self::isAvailableInCountry($availableCountries, $shopCountryIso)) {
                ++$dropped['country'];
                continue;
            }

            $kept[] = $entry;
        }

        return [$kept, $dropped];
    }

    /**
     * Country availability filter. Permissive when either side lacks data: an
     * empty/absent country list on the Addons entry means "no restriction
     * declared", and an unresolved shop country means we can't filter — in both
     * cases the module is kept rather than hide a possibly-valid result. ISO
     * codes are compared case-insensitively.
     *
     * Addons also uses the sentinel value `all` (instead of an empty list) for
     * modules sold worldwide — most catalog entries carry it, so treating it
     * as an ordinary ISO code would drop nearly every result.
     *
     * @param array<int, mixed> $availableCountries
     */
    public static function isAvailableInCountry(array $availableCountries, string $shopCountryIso): bool
    {
        if ($shopCountryIso === '' || $availableCountries === []) {
            return true;
        }

        foreach ($availableCountries as $code) {
            if (!is_string($code)) {
                continue;
            }
            $code = strtoupper(trim($code));
            if ($code === 'ALL' || $code === $shopCountryIso) {
                return true;
            }
        }

        return false;
    }

    /**
     * A missing bound is treated as permissive: Addons entries frequently omit
     * one end of the range, and dropping them would hide valid modules.
     */
    public static function isCompatibleWithPsVersion(?string $min, ?string $max, string $psVersion): bool
    {
        if ($min !== null && trim($min) !== '' && version_compare($psVersion, trim($min), '<')) {
            return false;
        }
        if ($max !== null && trim($max) !== '' && version_compare($psVersion, trim($max), '>')) {
            return false;
        }

        return true;
    }

    /** Overridable seam for unit tests (\Module is unavailable off-shop). */
    protected function isModuleInstalled(string $technicalName): bool
    {
        return \Module::isInstalled($technicalName);
    }

    /** Overridable seam for unit tests. */
    protected function isMboEnabled(): bool
    {
        return \Module::isInstalled('ps_mbo') && \Module::isEnabled('ps_mbo');
    }

    /**
     * Reduce one raw Addons product entry to a compact, assistant-friendly shape.
     * Defensive throughout: the Addons payload is large and evolves, so every
     * field is read through null-coalescing and only emitted when present.
     *
     * The live search payload exposes Verified+ only as a search facet, never
     * as a per-product field — so no `verifiedplus` key here; `badges` (e.g.
     * `ps-official-partner`) is the per-product trust signal available today.
     *
     * @param array<string, mixed> $entry
     *
     * @return array<string, mixed>
     */
    private function shapeProduct($entry): array
    {
        if (!is_array($entry)) {
            return [];
        }

        $product = is_array($entry['product'] ?? null) ? $entry['product'] : [];
        $gallery = is_array($entry['gallery'] ?? null) ? $entry['gallery'] : [];
        $lang = is_array($entry['product_lang'] ?? null) ? $entry['product_lang'] : [];
        $technical = is_array($entry['technical_info'] ?? null) ? $entry['technical_info'] : [];
        $ratings = is_array($entry['ratings'] ?? null) ? $entry['ratings'] : [];
        $prices = is_array($entry['prices'] ?? null) ? $entry['prices'] : [];
        $rbm = is_array($entry['rbm'] ?? null) ? $entry['rbm'] : [];
        $manufacturer = is_array($entry['owner']['manufacturer'] ?? null) ? $entry['owner']['manufacturer'] : [];
        $marketing = is_array($entry['marketing_info'] ?? null) ? $entry['marketing_info'] : [];

        $productPrice = is_array($prices['total'] ?? null) ? $prices['total'] : [];

        $shaped = [
            'id' => isset($product['id_product']) ? (int) $product['id_product'] : null,
            'name' => isset($lang['product_name']) ? (string) $lang['product_name'] : null,
            'technical_name' => isset($technical['technical_name']) ? (string) $technical['technical_name'] : null,
            'author' => isset($manufacturer['name']) ? (string) $manufacturer['name'] : null,
            'icon' => isset($product['pico']) ? (string) $product['pico'] : null,
            'cover' => isset($gallery['cover']) ? (string) $gallery['cover'] : null,
            'category' => isset($lang['category_name']) ? (string) $lang['category_name'] : null,
            'short_description' => isset($lang['short_description']) ? (string) $lang['short_description'] : null,
            'url' => isset($lang['product_link']) ? (string) $lang['product_link'] : null,
            'buy_price' => isset($productPrice['actual_price']) ? (float) $productPrice['actual_price'] : null,
            'plan_price' => isset($rbm['min_price']) ? $rbm['min_price'] : null,
            'currency' => isset($prices['currency']) ? (string) $prices['currency'] : null,
            'promoted' => isset($entry['promoted']) ? (bool) $entry['promoted'] : null,
            'is_latest_ps_version_compatible' => isset($product['is_latest_psVersion_compatible']) ? (bool) $product['is_latest_psVersion_compatible'] : null,
            'rating' => isset($ratings['rate']) ? (float) $ratings['rate'] : null,
            'nb_ratings' => isset($ratings['nb_rates']) ? (int) $ratings['nb_rates'] : null,
            'nb_downloads' => isset($technical['nb_downloads']) ? (int) $technical['nb_downloads'] : null,
            'latest_version' => isset($technical['last_release']['product_version']) ? (string) $technical['last_release']['product_version'] : null,
            'latest_version_date' => isset($technical['last_release']['release_date']) ? (string) $technical['last_release']['release_date'] : null,
            'available_countries' => isset($marketing['available_countries_iso_code']) ? $marketing['available_countries_iso_code'] : null,
            'badges' => isset($marketing['badges']) ? $marketing['badges'] : null,
            'developed_by' => isset($marketing['developed_by']) ? $marketing['developed_by'] : null,
            'compatibility_min' => isset($technical['compatibility_min']) ? (string) $technical['compatibility_min'] : null,
            'compatibility_max' => isset($technical['compatibility_max']) ? (string) $technical['compatibility_max'] : null,
        ];

        // Drop nulls so the model only sees fields that were actually present.
        return array_filter($shaped, static fn ($value): bool => $value !== null);
    }

    /**
     * Map a language ISO code to an Addons id, defaulting to the current
     * back-office language, then to English.
     */
    private function resolveAddonsLangId(?string $iso): int
    {
        $iso = $iso !== null ? strtolower(trim($iso)) : '';
        if ($iso === '') {
            $contextIso = \Language::getIsoById($this->resolveLangId(null));
            $iso = is_string($contextIso) ? strtolower($contextIso) : '';
        }

        return self::LANG_IDS[$iso] ?? self::DEFAULT_LANG_ID;
    }

    /**
     * Map a currency ISO code to an Addons id, defaulting to the shop default
     * currency, then to EUR.
     */
    private function resolveAddonsCurrencyId(?string $iso): int
    {
        $iso = $iso !== null ? strtoupper(trim($iso)) : '';
        if ($iso === '') {
            $iso = strtoupper($this->defaultCurrencyIso());
        }

        return self::CURRENCY_IDS[$iso] ?? self::DEFAULT_CURRENCY_ID;
    }

    /**
     * Map a country ISO code to an Addons id, defaulting to the shop default
     * country, then to France.
     */
    private function resolveAddonsCountryId(?string $iso): int
    {
        return self::COUNTRY_IDS[$this->resolveCountryIso($iso)] ?? self::DEFAULT_COUNTRY_ID;
    }

    /**
     * Resolve a country ISO code (uppercased) used both for the Addons
     * `id_country` param and the country availability filter, defaulting to the
     * shop default country. Returns '' when it can't be resolved — the filter
     * then stays permissive.
     */
    private function resolveCountryIso(?string $iso): string
    {
        $iso = $iso !== null ? strtoupper(trim($iso)) : '';
        if ($iso === '') {
            $country = new \Country((int) \Configuration::get('PS_COUNTRY_DEFAULT'));
            $iso = is_string($country->iso_code) ? strtoupper($country->iso_code) : '';
        }

        return $iso;
    }
}
