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
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Install a back-office module on demand from inside the Ask AI drawer.
 *
 * The actual install (DB tables, hooks, tabs, default config) is delegated to
 * PrestaShop's core ModuleManager — the same service the BO "Catalog" page
 * drives. We only need to put the module zip on disk first; that download is
 * what differs between merchants.
 *
 * Download strategy (see {@see download()}):
 *  1. DEFAULT — MBO. When ps_mbo is installed + enabled we reuse its
 *     ActionsManager::downloadModule(), fetched from the core admin Symfony
 *     container (NOT MBO's isolated ServiceContainer, which lacks the PSR-18
 *     http client). MBO attaches the merchant's PrestaShop Account bearer
 *     token, so this downloads FREE modules AND PAID modules the linked
 *     account has purchased. MBO is referenced by service id only (no compile
 *     -time symbol) so this module does not hard-depend on ps_mbo.
 *  2. FALLBACK — direct public Addons download. When MBO is absent we hit the
 *     public Addons endpoint with no auth: FREE modules only.
 *
 * Either way the resulting local zip is handed to ModuleManager::install(),
 * whose ZipSourceHandler accepts a local zip path (mime-based, not extension).
 *
 * The technical name + Addons product id both come from the client (resolved
 * from the recommendation card), so any module can be targeted — but the
 * download only succeeds for modules the linked account is entitled to, which
 * Addons enforces server-side.
 */
class ModuleInstallerService
{
    private const ADDONS_DOWNLOAD_URL = 'https://api-addons.prestashop.com/';
    private const DOWNLOAD_TIMEOUT_SECONDS = 60;
    private const MAX_ZIP_BYTES = 60_000_000;

    /**
     * Post-purchase entitlement is granted ASYNCHRONOUSLY: after the Chargebee
     * payment, billing fans a `payment_succeeded` event through Svix to Addons,
     * which only then flips the order valid (ps_orders.valid=1 + invoice_date).
     * Until that lands, Addons' Zipanator denies the download ({"success":false}).
     * The native ps_mbo flow waits for an `OrderValidated` SSE (up to 20s) before
     * downloading; we retry the authenticated download a few times so a
     * just-purchased module's entitlement has time to propagate.
     */
    private const ENTITLEMENT_RETRY_ATTEMPTS = 4;
    private const ENTITLEMENT_RETRY_DELAY_SECONDS = 3;

    /**
     * MBO's ActionsManager service id in the core admin container. Referenced
     * as a string (not a class symbol) so this module carries no compile-time
     * dependency on ps_mbo.
     */
    private const MBO_ACTIONS_MANAGER_SERVICE = 'PrestaShop\\Module\\Mbo\\Module\\ActionsManager';

    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?PsAccounts $accountsFacade = null,
    ) {
    }

    public function isInstalled(string $moduleName): bool
    {
        return $this->moduleManager->isInstalled($moduleName);
    }

    public function isOnDisk(string $moduleName): bool
    {
        return is_dir($this->moduleDir() . $moduleName);
    }

    /**
     * Put the module zip on disk when the files are not already there. Returns
     * the local zip path to hand to {@see install()} as the install source, or
     * null when the files are already present (install from disk).
     *
     * MBO is the default path (free + purchased-paid via the merchant's account
     * token); the direct public Addons download is the fallback when ps_mbo is
     * not available (free modules only).
     */
    public function download(string $moduleName, ?int $productId = null): ?string
    {
        if ($this->isOnDisk($moduleName)) {
            return null;
        }

        // The product id is supplied by the caller (from the Addons search) so
        // any purchased module downloads. Entitlement is still enforced
        // Addons-side on download.
        if ($productId === null) {
            throw new \RuntimeException(sprintf('Module "%s" is not downloadable (no product id).', $moduleName));
        }
        $moduleId = $productId;

        if ($this->isMboAvailable()) {
            try {
                return $this->downloadViaMbo($moduleId, $moduleName);
            } catch (\Throwable $mboError) {
                // MBO's download discards the Addons error body (keeps only
                // "Bad Request"). Retry the SAME authenticated download
                // ourselves so we can read the real reason and — for a freshly
                // purchased module whose entitlement is now live — succeed
                // without ps_mbo's static circuit breaker getting in the way.
                $token = $this->resolveAccountsUserToken();
                if ($token !== '') {
                    return $this->downloadViaAddonsAuthenticated($moduleId, $moduleName, $token, $mboError);
                }
                throw $mboError;
            }
        }

        return $this->downloadZip($moduleId, $moduleName);
    }

    /**
     * Run the module's install. When $source is a zip path, ModuleManager
     * unpacks it into the modules dir first, then runs onInstall. The temporary
     * zip is removed afterwards. Returns true on success.
     */
    public function install(string $moduleName, ?string $source = null): bool
    {
        if ($this->isInstalled($moduleName)) {
            return true;
        }

        try {
            return $this->moduleManager->install($moduleName, $source);
        } finally {
            if ($source !== null && is_file($source)) {
                @unlink($source);
            }
        }
    }

    public function isConfigurable(string $moduleName): bool
    {
        $module = \Module::getInstanceByName($moduleName);

        return $module !== false && method_exists($module, 'getContent');
    }

    /**
     * Absolute BO link to the freshly installed module's configuration page, or
     * null when the module exposes no getContent().
     */
    public function getConfigureUrl(string $moduleName): ?string
    {
        if (!$this->isConfigurable($moduleName)) {
            return null;
        }

        return \Context::getContext()->link->getAdminLink(
            'AdminModules',
            true,
            [],
            ['configure' => $moduleName]
        );
    }

    /**
     * True when ps_mbo is installed and enabled, so its authenticated download
     * path can be reused.
     */
    private function isMboAvailable(): bool
    {
        return \Module::isInstalled('ps_mbo') && \Module::isEnabled('ps_mbo');
    }

    /**
     * Download the module zip through MBO. MBO attaches the merchant's
     * PrestaShop Account bearer token (via ps_accounts), so this serves FREE
     * modules and PAID modules the linked account has purchased. Returns the
     * local temp zip path produced by MBO.
     *
     * Fetched from the core admin Symfony container — MBO's own isolated
     * ServiceContainer does not wire the PSR-18 http client ActionsManager's
     * dependencies need.
     *
     * @throws \RuntimeException when MBO is unreachable or the download fails
     *                           (e.g. a paid module the account has not bought)
     */
    private function downloadViaMbo(int $moduleId, string $moduleName): string
    {
        $container = SymfonyContainer::getInstance();
        if ($container === null || !$container->has(self::MBO_ACTIONS_MANAGER_SERVICE)) {
            throw new \RuntimeException('MBO ActionsManager is not available in the container.');
        }

        $actionsManager = $container->get(self::MBO_ACTIONS_MANAGER_SERVICE);
        if (!is_object($actionsManager) || !method_exists($actionsManager, 'downloadModule')) {
            throw new \RuntimeException('MBO ActionsManager service has an unexpected type.');
        }

        try {
            // Returns a local temp zip path; install() unlinks it afterwards.
            return (string) $actionsManager->downloadModule($moduleId);
        } catch (\Throwable $e) {
            // The real Addons rejection (e.g. the 400 body) is discarded by
            // ps_mbo's ApiClient — it keeps only the reason phrase ("Bad
            // Request"). The chain below still names each layer. In-process
            // retry is pointless: AddonsDataProvider trips a *static*
            // $is_addons_up=false on the first failure, so every later call in
            // the same request short-circuits with "Previous call failed and
            // disabled client". The real diagnosis comes from our own
            // authenticated Addons call (see downloadViaAddonsAuthenticated).
            $chain = [];
            for ($cur = $e; $cur !== null; $cur = $cur->getPrevious()) {
                $chain[] = get_class($cur) . ': ' . $cur->getMessage();
            }
            $this->logger?->warning('MBO module download failed', [
                'module' => $moduleName,
                'id_module' => $moduleId,
                'error' => $e->getMessage(),
                'chain' => $chain,
            ]);
            throw new \RuntimeException(sprintf('MBO could not download module "%s" (free, or paid and purchased by the linked account?).', $moduleName), 0, $e);
        }
    }

    /**
     * Download a purchased module from Addons' MODERN v2 endpoint
     * `GET /v2/shop/products/{idProduct}` (middleware `sso_auth`), authenticated
     * with the merchant's PrestaShop Account token as a Bearer header.
     *
     * Why not ps_mbo's path: ps_mbo downloads through the LEGACY
     * `?method=module` endpoint, whose login flow rejects our bearer on preprod
     * (`0003 Invalid credentials`). The v2 API accepts the very same token, so
     * we download through v2 too. On success Addons streams the zip binary as
     * the body; we persist it and return the local temp path. On failure it
     * returns `{"success":false}` (400); we log the body and rethrow the
     * original MBO error.
     *
     * Base URL: the shop's own `ADDONS_API_URL` (preprod there), falling back
     * to the public production endpoint.
     */
    private function downloadViaAddonsAuthenticated(int $moduleId, string $moduleName, string $token, \Throwable $mboError): string
    {
        $base = $this->addonsAuthBase();
        $url = $base . 'v2/shop/products/' . $moduleId . '?' . http_build_query([
            'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '9.0.0',
            'shop_url' => $this->shopUrl(),
        ]);

        // Entitlement lands asynchronously after payment (billing → Svix →
        // Addons), so a fresh purchase can be denied on the first attempt and
        // pass moments later. Retry the refusal a bounded number of times; a
        // transport error or a genuinely unentitled module still fails, just
        // after exhausting the window (stand-in for the native OrderValidated
        // SSE wait). See self::ENTITLEMENT_RETRY_ATTEMPTS.
        for ($attempt = 1; $attempt <= self::ENTITLEMENT_RETRY_ATTEMPTS; ++$attempt) {
            try {
                $response = $this->httpClient->request('GET', $url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'X-PrestaShop-MBO' => $this->mboVersion(),
                    ],
                    'timeout' => self::DOWNLOAD_TIMEOUT_SECONDS,
                    'max_duration' => self::DOWNLOAD_TIMEOUT_SECONDS,
                ]);
                $status = $response->getStatusCode();
                $content = $response->getContent(false);
            } catch (\Throwable $e) {
                $this->logger?->warning('Authenticated Addons download transport error', [
                    'module' => $moduleName,
                    'id_module' => $moduleId,
                    'error' => $e->getMessage(),
                ]);
                throw $mboError;
            }

            // On success Addons streams the zip binary (starts with the "PK"
            // magic bytes). Anything else (JSON {"success":false}, an error
            // page) is a failure — log it verbatim so the reason is unambiguous.
            if ($status >= 200 && $status < 300 && substr($content, 0, 2) === 'PK') {
                if (strlen($content) > self::MAX_ZIP_BYTES) {
                    throw new \RuntimeException(sprintf('Downloaded module exceeds %d bytes.', self::MAX_ZIP_BYTES));
                }
                $dir = $this->tmpDir();
                if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                    throw new \RuntimeException('Cannot create temp dir for module download: ' . $dir);
                }
                $zipPath = $dir . $moduleName . '_' . bin2hex(random_bytes(4)) . '.zip';
                if (@file_put_contents($zipPath, $content) === false) {
                    throw new \RuntimeException('Cannot write downloaded module zip to ' . $zipPath);
                }

                return $zipPath;
            }

            $this->logger?->warning('Authenticated Addons download refused', [
                'module' => $moduleName,
                'id_module' => $moduleId,
                'status' => $status,
                'base' => $base,
                'body' => substr($content, 0, 600),
                'attempt' => $attempt,
                'max_attempts' => self::ENTITLEMENT_RETRY_ATTEMPTS,
            ]);

            if ($attempt < self::ENTITLEMENT_RETRY_ATTEMPTS) {
                sleep(self::ENTITLEMENT_RETRY_DELAY_SECONDS);
            }
        }

        throw $mboError;
    }

    /**
     * Addons API base for the authenticated download: prefer the same endpoint
     * ps_mbo targets (`ADDONS_API_URL`, e.g. the preprod host) so entitlement
     * matches where the order was placed; fall back to public production.
     */
    private function addonsAuthBase(): string
    {
        $fromEnv = getenv('ADDONS_API_URL');
        $base = is_string($fromEnv) && $fromEnv !== '' ? $fromEnv : self::ADDONS_DOWNLOAD_URL;

        return rtrim($base, '/') . '/';
    }

    /**
     * ps_mbo's version string for the X-PrestaShop-MBO header, read off the
     * installed module instance. Falls back to a neutral value when ps_mbo's
     * VERSION constant is not loaded.
     */
    private function mboVersion(): string
    {
        if (defined('\\ps_mbo::VERSION')) {
            return (string) constant('\\ps_mbo::VERSION');
        }
        $mbo = \Module::getInstanceByName('ps_mbo');

        return ($mbo !== false && !empty($mbo->version)) ? (string) $mbo->version : '0';
    }

    /**
     * The shop's public base URL (scheme + domain), passed to Addons as the
     * `shop_url` the download is bound to.
     */
    private function shopUrl(): string
    {
        $domain = \Tools::getShopDomainSsl(true);

        return is_string($domain) && $domain !== '' ? $domain : '';
    }

    /**
     * Resolve the bearer for the authenticated Addons download.
     *
     * Addons' download resolves a CUSTOMER (a person) from the bearer via
     * CustomerToken::getByLocalIdOrEmail($uid, $email) — so it needs a USER
     * token, not the shop token (a shop identity carries no customer email and
     * can never match). We send the user token (right identity) and log its
     * issuer; if it is empty we fall back to the shop token only so the
     * failure is still visible in the logs.
     */
    private function resolveAccountsUserToken(): string
    {
        if ($this->accountsFacade === null) {
            return '';
        }

        try {
            $service = $this->accountsFacade->getPsAccountsService();
        } catch (\Throwable $e) {
            $this->logger?->warning('Could not resolve ps_accounts service for download', ['error' => $e->getMessage()]);

            return '';
        }

        foreach (['getUserToken', 'getShopToken'] as $method) {
            if (!method_exists($service, $method)) {
                continue;
            }
            try {
                $token = (string) $service->{$method}();
            } catch (\Throwable $e) {
                $this->logger?->warning('ps_accounts token method threw', ['method' => $method, 'error' => $e->getMessage()]);
                continue;
            }
            if ($token !== '') {
                $this->logger?->info('Using ps_accounts token for Addons download', [
                    'method' => $method,
                    'issuer' => $this->jwtIssuer($token),
                ]);

                return $token;
            }
        }

        return '';
    }

    /**
     * Best-effort decode of a JWT's `iss` claim, for logging which identity we
     * present to Addons. Returns '' when the token is opaque/unparseable.
     */
    private function jwtIssuer(string $jwt): string
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return '';
        }
        $payload = base64_decode(strtr($parts[1], '-_', '+/'), false);
        if ($payload === false) {
            return '';
        }
        $data = json_decode($payload, true);

        return is_array($data) && isset($data['iss']) ? (string) $data['iss'] : '';
    }

    /**
     * Download the module zip from the public Addons endpoint to a temp file.
     * Free modules are served without authentication. Used as a fallback when
     * MBO is not installed.
     */
    private function downloadZip(int $moduleId, string $moduleName): string
    {
        $context = \Context::getContext();
        $isoLang = $context->language !== null ? strtolower((string) $context->language->iso_code) : 'en';
        $isoCode = 'us';
        if ($context->country !== null && $context->country->iso_code) {
            $isoCode = strtolower((string) $context->country->iso_code);
        }

        $url = self::ADDONS_DOWNLOAD_URL . '?' . http_build_query([
            'method' => 'module',
            'id_module' => $moduleId,
            'format' => 'json',
            'version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '9.0.0',
            'iso_lang' => preg_match('/^[a-z]{2,3}$/', $isoLang) ? $isoLang : 'en',
            'iso_code' => preg_match('/^[a-z]{2,3}$/', $isoCode) ? $isoCode : 'us',
        ]);

        $response = $this->httpClient->request('GET', $url, [
            'timeout' => self::DOWNLOAD_TIMEOUT_SECONDS,
            'max_duration' => self::DOWNLOAD_TIMEOUT_SECONDS,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf('Addons returned HTTP %d while downloading "%s".', $response->getStatusCode(), $moduleName));
        }

        $content = $response->getContent();
        if (strlen($content) > self::MAX_ZIP_BYTES) {
            throw new \RuntimeException(sprintf('Downloaded module exceeds %d bytes.', self::MAX_ZIP_BYTES));
        }
        // Addons returns the zip binary directly; a JSON/HTML body here means an
        // error (e.g. paid module needing auth, or unknown id). Zip magic: "PK".
        if (substr($content, 0, 2) !== 'PK') {
            $this->logger?->warning('Addons download did not return a zip', [
                'module' => $moduleName,
                'id_module' => $moduleId,
                'body_preview' => substr($content, 0, 200),
            ]);
            throw new \RuntimeException(sprintf('Addons did not return a module zip for "%s" (maybe not free?).', $moduleName));
        }

        $dir = $this->tmpDir();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Cannot create temp dir for module download: ' . $dir);
        }
        $zipPath = $dir . $moduleName . '_' . bin2hex(random_bytes(4)) . '.zip';
        if (@file_put_contents($zipPath, $content) === false) {
            throw new \RuntimeException('Cannot write downloaded module zip to ' . $zipPath);
        }

        return $zipPath;
    }

    private function moduleDir(): string
    {
        $base = defined('_PS_MODULE_DIR_') ? _PS_MODULE_DIR_ : (defined('_PS_ROOT_DIR_') ? _PS_ROOT_DIR_ . '/modules/' : sys_get_temp_dir() . '/');

        return rtrim((string) $base, '/') . '/';
    }

    private function tmpDir(): string
    {
        $base = defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/';

        return rtrim((string) $base, '/') . '/ps_ask_ai/';
    }
}
