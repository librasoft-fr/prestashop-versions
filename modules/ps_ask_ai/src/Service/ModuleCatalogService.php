<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Marketplace catalog lookups for the drawer's module recommendation cards.
 *
 * Hits the SAME catalog endpoint ps_mbo's Marketplace page uses — the MBO API
 * `GET /api/modules/{id}` (ModuleV2 shape) — which proxies Addons. The route is
 * public-with-optional-bearer: we pass the MBO shop JWT so the shop is
 * resolved, plus the ps_accounts user token so the reply can carry the
 * `purchased` flag (paid module already owned → direct download instead of a
 * marketplace redirect).
 *
 * Two-token rule inherited from the MBO API: the Authorization header must be
 * the ps_mbo-signed SHOP JWT (it carries shop_url/ps_version — a bare
 * accounts_token has neither and crashes the API), while the accounts_token
 * query param is the MERCHANT identity Addons resolves entitlement under.
 */
class ModuleCatalogService
{
    private const DEFAULT_MBO_API_BASE = 'https://mbo-api.preproduction.prestashop.com/api';
    private const REQUEST_TIMEOUT_SECONDS = 30;

    /**
     * MBO's AdminAuthenticationProvider service id in the core admin container.
     * Referenced as a string (not a class symbol) so this module carries no
     * compile-time dependency on ps_mbo.
     */
    private const MBO_AUTH_PROVIDER_SERVICE = 'PrestaShop\\Module\\Mbo\\Api\\Security\\AdminAuthenticationProvider';

    public function __construct(
        private readonly PsAccounts $accountsFacade,
        private readonly HttpClientInterface $httpClient,
        private readonly \Context $context,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Fetch a module's marketplace details by Addons product id. Everything is
     * best-effort: missing fields degrade to empty/zero rather than failing
     * the card.
     *
     * @return array{id: string, name: string, technicalName: string, editor: string,
     *               price: float, currency: string, rating: float, nbRates: int,
     *               description: string, pico: string, productLink: string,
     *               productType: string, purchased: bool, downloadable: bool,
     *               subscriptionPrice: float, subscriptionPeriodUnit: string,
     *               freeTrialDays: int}
     *
     * @throws \RuntimeException when the MBO API call fails or returns no module
     */
    public function fetchModule(string $productId): array
    {
        $query = [
            'currency' => $this->resolveCurrencyIso(),
            'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '9.0.0',
            'shop_url' => $this->shopUrl(),
            'iso_lang' => $this->resolveLangIso(),
            'iso_code' => $this->resolveCountryIso(),
        ];

        $accountsToken = $this->resolveAccountsUserToken();
        if ($accountsToken !== '') {
            $query['accounts_token'] = $accountsToken;
        }

        $headers = ['Accept' => 'application/json'];
        $mboJwt = $this->resolveMboJwt();
        if ($mboJwt !== '') {
            $headers['Authorization'] = 'Bearer ' . $mboJwt;
        }

        $response = $this->httpClient->request('GET', $this->apiBase() . '/modules/' . rawurlencode($productId), [
            'headers' => $headers,
            'query' => $query,
            'timeout' => self::REQUEST_TIMEOUT_SECONDS,
            'max_duration' => self::REQUEST_TIMEOUT_SECONDS,
        ]);

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $this->logger?->warning('MBO module lookup failed', [
                'product_id' => $productId,
                'status' => $status,
                'body_preview' => substr($response->getContent(false), 0, 300),
            ]);
            throw new \RuntimeException(sprintf('Module lookup returned HTTP %d.', $status));
        }

        $data = $response->toArray(false);
        $module = $this->normalizeModule($data, $productId);
        if ($module['name'] === '' && $module['technicalName'] === '') {
            throw new \RuntimeException('No module found for this product id.');
        }

        return $module;
    }

    /**
     * Flatten the nested MBO ModuleV2 JSON into the shape the drawer card
     * consumes. Reads several likely key spellings so it survives the catalog
     * (v2, nested) and legacy (flat) response variants the API can return.
     *
     * @param array<string, mixed> $data
     *
     * @return array{id: string, name: string, technicalName: string, editor: string,
     *               price: float, currency: string, rating: float, nbRates: int,
     *               description: string, pico: string, productLink: string,
     *               productType: string, purchased: bool, downloadable: bool,
     *               subscriptionPrice: float, subscriptionPeriodUnit: string,
     *               freeTrialDays: int}
     */
    private function normalizeModule(array $data, string $productId): array
    {
        $product = is_array($data['product'] ?? null) ? $data['product'] : [];
        $productLang = is_array($data['product_lang'] ?? null) ? $data['product_lang'] : [];
        $owner = is_array($data['owner']['manufacturer'] ?? null) ? $data['owner']['manufacturer'] : [];
        $ratings = is_array($data['ratings'] ?? null) ? $data['ratings'] : [];
        $prices = is_array($data['prices'] ?? null) ? $data['prices'] : [];
        $gallery = is_array($data['gallery'] ?? null) ? $data['gallery'] : [];
        $tech = is_array($data['technical_info'] ?? null) ? $data['technical_info'] : [];
        // Subscription (Recurring Billing Module) block: a downloadable module can
        // still carry a paid service subscription (e.g. 9.99 €/month + free trial).
        $rbm = is_array($data['rbm'] ?? null) ? $data['rbm'] : [];
        $minPrice = is_array($rbm['min_price'] ?? null) ? $rbm['min_price'] : [];

        $str = static fn ($v): string => is_scalar($v) ? (string) $v : '';
        $first = static function (array ...$candidates) use ($str): string {
            foreach ($candidates as $c) {
                $v = $str($c[0] ?? null);
                if ($v !== '') {
                    return $v;
                }
            }

            return '';
        };
        // A `prices.*` entry is an OBJECT ({actual_price, actual_euro_price,
        // euro_price, original_price}), not a scalar — casting the array to float
        // silently yields 1.0, so pull the amount out explicitly. `actual_price`
        // is in the requested currency; euro variants are the fallback.
        $priceOf = static function ($block): float {
            if (is_numeric($block)) {
                return (float) $block;
            }
            if (!is_array($block)) {
                return 0.0;
            }
            foreach (['actual_price', 'actual_euro_price', 'euro_price', 'original_price'] as $key) {
                if (isset($block[$key]) && is_numeric($block[$key])) {
                    return (float) $block[$key];
                }
            }

            return 0.0;
        };

        return [
            'id' => $first([$product['id_product'] ?? null], [$data['id'] ?? null]) ?: $productId,
            'name' => $first([$productLang['product_name'] ?? null], [$data['name'] ?? null], [$data['displayName'] ?? null]),
            'technicalName' => $first([$tech['technical_name'] ?? null], [$data['name'] ?? null]),
            'editor' => $first([$owner['name'] ?? null], [$data['author'] ?? null]),
            'price' => $priceOf($prices['product'] ?? null) ?: $priceOf($prices['total'] ?? null),
            'currency' => $first([$prices['currency'] ?? null]) ?: $this->resolveCurrencyIso(),
            'rating' => (float) ($ratings['rate'] ?? 0),
            'nbRates' => (int) ($ratings['nb_rates'] ?? 0),
            'description' => $first([$productLang['short_description'] ?? null], [$data['description'] ?? null]),
            'pico' => $first([$gallery['cover'] ?? null], [$data['img'] ?? null], [$data['cover'] ?? null]),
            'productLink' => $first([$productLang['product_link'] ?? null], [$data['url'] ?? null]),
            'productType' => $first([$product['product_type'] ?? null], [$data['product_type'] ?? null]),
            'purchased' => (bool) ($data['purchased'] ?? false),
            'downloadable' => (bool) ($product['downloadable'] ?? false),
            'subscriptionPrice' => (isset($minPrice['price']) && is_numeric($minPrice['price'])) ? (float) $minPrice['price'] : 0.0,
            'subscriptionPeriodUnit' => $first([$minPrice['period_unit'] ?? null]),
            'freeTrialDays' => (int) ($rbm['free_trial'] ?? 0),
        ];
    }

    private function apiBase(): string
    {
        $fromEnv = $this->env('PS_ASK_AI__MBO_API_BASE');
        $base = $fromEnv !== '' ? $fromEnv : self::DEFAULT_MBO_API_BASE;

        return rtrim($base, '/');
    }

    /**
     * Read a PS_ASK_AI__* env var. bootstrap.php's `new Dotenv(true)` does NOT
     * enable putenv on PS9 (Symfony 5.1+, where the first constructor arg is
     * $envKey, not $usePutenv) — it only populates $_SERVER/$_ENV. So getenv()
     * alone returns false here; fall back to the server/env superglobals that
     * loadEnv actually fills.
     */
    private function env(string $key): string
    {
        $val = getenv($key);
        if (is_string($val) && $val !== '') {
            return $val;
        }
        foreach ([$_SERVER, $_ENV] as $bag) {
            if (isset($bag[$key]) && is_string($bag[$key]) && $bag[$key] !== '') {
                return $bag[$key];
            }
        }

        return '';
    }

    private function resolveAccountsUserToken(): string
    {
        try {
            $service = $this->accountsFacade->getPsAccountsService();

            return (string) $service->getUserToken();
        } catch (\Throwable $e) {
            $this->logger?->warning('Could not resolve ps_accounts token for module lookup', ['error' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * Resolve the MBO shop JWT (the token ps_mbo itself puts in the
     * Authorization header when calling its API). Pulled from the core admin
     * container, same as {@see ModuleInstallerService::downloadViaMbo()}.
     * Empty string when ps_mbo is absent or the service misbehaves.
     */
    private function resolveMboJwt(): string
    {
        try {
            $container = SymfonyContainer::getInstance();
            if ($container === null || !$container->has(self::MBO_AUTH_PROVIDER_SERVICE)) {
                return '';
            }

            $provider = $container->get(self::MBO_AUTH_PROVIDER_SERVICE);
            if (!is_object($provider) || !method_exists($provider, 'getMboJWT')) {
                return '';
            }

            return (string) $provider->getMboJWT();
        } catch (\Throwable $e) {
            $this->logger?->warning('Could not resolve MBO shop JWT', ['error' => $e->getMessage()]);

            return '';
        }
    }

    private function resolveCurrencyIso(): string
    {
        $iso = $this->context->currency->iso_code ?? null;

        return is_string($iso) && $iso !== '' ? $iso : 'EUR';
    }

    private function resolveLangIso(): string
    {
        $iso = $this->context->language->iso_code ?? null;

        return is_string($iso) && $iso !== '' ? $iso : 'en';
    }

    private function resolveCountryIso(): string
    {
        $iso = $this->context->country->iso_code ?? null;

        return is_string($iso) && $iso !== '' ? strtolower($iso) : 'us';
    }

    private function shopUrl(): string
    {
        $domain = \Tools::getShopDomainSsl(true);

        return is_string($domain) && $domain !== '' ? $domain : '';
    }
}
