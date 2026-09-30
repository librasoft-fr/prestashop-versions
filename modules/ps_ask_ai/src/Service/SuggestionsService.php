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
 * Fetches the prompt suggestions catalog from a remote URL (GCS bucket) as a
 * `,`-separated CSV with columns `category, title_<lang>, prompt_<lang>` per
 * supported language. Caller requests a language (e.g. "fr"); if no
 * `title_<lang>`/`prompt_<lang>` columns exist the row falls back to `_en`.
 *
 * Same stale-while-revalidate cache strategy as PrepromptService:
 *   - fresh cache (< TTL)  → return cached, no network
 *   - stale cache (>= TTL) → return cached immediately, refresh on shutdown
 *   - no cache             → fetch synchronously, throw on failure
 */
class SuggestionsService
{
    private const CACHE_TTL_SECONDS = 600;
    private const FETCH_TIMEOUT_SECONDS = 3;
    private const MAX_BYTES = 1_000_000;
    private const ENV_URL = 'PS_ASK_AI__SUGGESTIONS_URL';
    private const FALLBACK_LANG = 'en';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @return array<int, array{id: int, category: string, title: string, prompt: string}>
     */
    public function get(string $lang): array
    {
        $this->ensureEnvLoaded();

        $url = getenv(self::ENV_URL);
        if ($url === false || $url === '') {
            throw new \RuntimeException(sprintf('%s is not configured.', self::ENV_URL));
        }

        $cachePath = $this->cachePath();
        $cached = is_file($cachePath) ? @file_get_contents($cachePath) : false;
        $age = is_file($cachePath) ? time() - (int) @filemtime($cachePath) : PHP_INT_MAX;

        if ($cached !== false && $cached !== '' && $age < self::CACHE_TTL_SECONDS) {
            return $this->parse($cached, $lang);
        }

        if ($cached !== false && $cached !== '') {
            $this->scheduleBackgroundRefresh($url, $cachePath);

            return $this->parse($cached, $lang);
        }

        $fetched = $this->fetch($url);
        $this->writeCache($cachePath, $fetched);

        return $this->parse($fetched, $lang);
    }

    /**
     * Pick `title_<lang>`/`prompt_<lang>` if both columns exist, else fall
     * back to `_en`. Throws when even the fallback columns are missing.
     *
     * @return array<int, array{id: int, category: string, title: string, prompt: string}>
     */
    private function parse(string $csv, string $lang): array
    {
        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            return [];
        }
        fwrite($handle, $csv);
        rewind($handle);

        $header = fgetcsv($handle, 0, ',');
        if ($header === false) {
            fclose($handle);

            return [];
        }
        $idx = array_flip(array_map('strval', $header));
        $catCol = $idx['category'] ?? null;
        $titleCol = $idx['title_' . $lang] ?? $idx['title_' . self::FALLBACK_LANG] ?? null;
        $promptCol = $idx['prompt_' . $lang] ?? $idx['prompt_' . self::FALLBACK_LANG] ?? null;
        if ($catCol === null || $titleCol === null || $promptCol === null) {
            fclose($handle);
            throw new \RuntimeException('Suggestions CSV missing required columns.');
        }

        $rows = [];
        $id = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = [
                'id' => $id++,
                'category' => (string) ($row[$catCol] ?? ''),
                'title' => (string) ($row[$titleCol] ?? ''),
                'prompt' => (string) ($row[$promptCol] ?? ''),
            ];
        }
        fclose($handle);

        return $rows;
    }

    private function scheduleBackgroundRefresh(string $url, string $cachePath): void
    {
        register_shutdown_function(function () use ($url, $cachePath): void {
            try {
                $fetched = $this->fetch($url);
                $this->writeCache($cachePath, $fetched);
            } catch (\Throwable $e) {
                @touch($cachePath);
                $this->logger?->warning(
                    'Background suggestions refresh failed: ' . $e->getMessage(),
                    ['exception' => $e]
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
            throw new \RuntimeException('Suggestions response body is empty.');
        }
        if (strlen($body) > self::MAX_BYTES) {
            throw new \RuntimeException(sprintf('Suggestions exceeds max size of %d bytes (got %d).', self::MAX_BYTES, strlen($body)));
        }

        return $body;
    }

    private function writeCache(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger?->warning('Cannot create suggestions cache dir: ' . $dir);

            return;
        }
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, $content) === false) {
            $this->logger?->warning('Failed to write suggestions cache tmp file: ' . $tmp);

            return;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            $this->logger?->warning('Failed to rename suggestions cache tmp file: ' . $tmp);
        }
    }

    private function cachePath(): string
    {
        $base = defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/';

        return rtrim((string) $base, '/') . '/ps_ask_ai/suggestions.csv';
    }

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
