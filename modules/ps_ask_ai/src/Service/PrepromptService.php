<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches the agent's system preprompt from a remote URL (GCS bucket) so
 * its wording can be iterated without shipping a new module release.
 *
 * Read path is a file cache with stale-while-revalidate:
 *   - fresh cache (< TTL)  → return cached, no network
 *   - stale cache (>= TTL) → return cached immediately, refresh in a
 *                            shutdown handler so the next call is fresh
 *   - no cache             → fetch synchronously, throw on failure
 *
 * The "no cache + remote unreachable" case bubbles up; callers should
 * surface it as a user-visible error rather than silently dropping the
 * preprompt (we don't want an undocumented degraded mode where the
 * assistant runs without its instructions).
 */
class PrepromptService
{
    private const CACHE_TTL_SECONDS = 600;
    private const FETCH_TIMEOUT_SECONDS = 3;
    private const MAX_BYTES = 200_000;
    private const ENV_VAR = 'PS_ASK_AI__PREPROMPT_URL';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null,
        private readonly ?SentryReporter $sentryReporter = null,
    ) {
    }

    public function get(): string
    {
        $this->ensureEnvLoaded();

        $url = getenv(self::ENV_VAR);
        if ($url === false || $url === '') {
            throw new \RuntimeException(sprintf('%s is not configured.', self::ENV_VAR));
        }

        $cachePath = $this->cachePath();
        $cached = is_file($cachePath) ? @file_get_contents($cachePath) : false;
        $age = is_file($cachePath) ? time() - (int) @filemtime($cachePath) : PHP_INT_MAX;

        if ($cached !== false && $cached !== '' && $age < self::CACHE_TTL_SECONDS) {
            return $cached;
        }

        if ($cached !== false && $cached !== '') {
            $this->scheduleBackgroundRefresh($url, $cachePath);

            return $cached;
        }

        $fetched = $this->fetch($url);
        $this->writeCache($cachePath, $fetched);

        return $fetched;
    }

    /**
     * Register a one-shot refresh that runs after PHP finishes the current
     * request. For SSE this fires once the stream completes, so the *next*
     * chat turn picks up the fresh copy — fine for a 10-minute TTL.
     */
    private function scheduleBackgroundRefresh(string $url, string $cachePath): void
    {
        register_shutdown_function(function () use ($url, $cachePath): void {
            try {
                $fetched = $this->fetch($url);
                $this->writeCache($cachePath, $fetched);
            } catch (\Throwable $e) {
                // Bump mtime so we don't re-attempt on every subsequent
                // request when the remote is down — wait a full TTL before
                // trying again. The stale content stays readable.
                @touch($cachePath);
                $this->logger?->warning(
                    'Background preprompt refresh failed: ' . $e->getMessage(),
                    ['exception' => $e]
                );
                $this->sentryReporter?->captureWarning(
                    'Background preprompt refresh failed: ' . $e->getMessage(),
                    'preprompt_refresh_failed',
                );
            }
        });
    }

    private function fetch(string $url): string
    {
        $response = $this->httpClient->request('GET', $url, [
            'timeout' => self::FETCH_TIMEOUT_SECONDS,
            'max_duration' => self::FETCH_TIMEOUT_SECONDS,
        ]);
        $status = $response->getStatusCode();
        if ($status !== 200) {
            throw new \RuntimeException(sprintf('Unexpected HTTP status %d from %s', $status, $url));
        }
        $body = $response->getContent();
        if ($body === '') {
            throw new \RuntimeException('Preprompt response body is empty.');
        }
        if (strlen($body) > self::MAX_BYTES) {
            throw new \RuntimeException(sprintf('Preprompt exceeds max size of %d bytes (got %d).', self::MAX_BYTES, strlen($body)));
        }

        return $body;
    }

    private function writeCache(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger?->warning('Cannot create preprompt cache dir: ' . $dir);

            return;
        }
        // Write to a temp file then rename so concurrent readers never see
        // a partially-written cache.
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, $content) === false) {
            $this->logger?->warning('Failed to write preprompt cache tmp file: ' . $tmp);

            return;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            $this->logger?->warning('Failed to rename preprompt cache tmp file: ' . $tmp);
        }
    }

    private function cachePath(): string
    {
        $base = defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/';

        return rtrim((string) $base, '/') . '/ps_ask_ai/preprompt.txt';
    }

    /**
     * Same pattern as NeuronAiService::ensureEnvLoaded — the module
     * bootstrap only loads Dotenv when PrestaShop instantiates the Module,
     * which doesn't happen in CLI/console contexts.
     */
    private function ensureEnvLoaded(): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $envPath = dirname(__DIR__, 2) . '/.env';
        if (is_file($envPath) && class_exists(Dotenv::class)) {
            $dotenv = new Dotenv();
            // Dotenv::usePutenv() only exists in Symfony 5.1+ (PS9). On Symfony 4.4 (PS8)
            // putenv is enabled via the constructor and defaults to true, so skip the call.
            // @phpstan-ignore function.alreadyNarrowedType, function.impossibleType (true on PS9 / false on PS8 Symfony 4.4)
            if (method_exists($dotenv, 'usePutenv')) {
                $dotenv->usePutenv(true);
            }
            $dotenv->loadEnv($envPath);
        }
    }
}
