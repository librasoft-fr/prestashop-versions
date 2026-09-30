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
 * Fetches per-model LLM parameters (temperature, top_p, top_k, max_tokens,
 * thinking controls) from a remote URL (GCS bucket) so they can be tuned
 * without shipping a new module release.
 *
 * Read path mirrors {@see PrepromptService}: stale-while-revalidate file
 * cache, throws when no cache exists AND the remote is unreachable. Field-
 * level type errors are silently dropped (caller gets an empty array for
 * that model — same as today's behaviour where nothing was set).
 *
 * Expected JSON shape:
 * {
 *   "version": "2026-05-26.1",
 *   "models": {
 *     "<model_key>": {
 *       "temperature": 0.7,            // float
 *       "top_p": 0.95,                 // float
 *       "top_k": 40,                   // int
 *       "max_tokens": 16384,           // int
 *       "thinking_budget_tokens": 5000,        // int   (Anthropic legacy)
 *       "thinking_effort": "medium",            // string (Anthropic Opus 4.7)
 *       "thinking_include_thoughts": true,      // bool  (Gemini)
 *       "reasoning_summary": "auto"             // string (OpenAI Responses)
 *     }
 *   }
 * }
 *
 * The service is intentionally provider-agnostic: it just returns the
 * sanitized dict. NeuronAiService translates it to each provider's native
 * shape and decides which fields are legal for which model.
 */
class LlmParamsService
{
    private const CACHE_TTL_SECONDS = 600;
    private const FETCH_TIMEOUT_SECONDS = 3;
    private const MAX_BYTES = 50_000;
    private const ENV_VAR = 'PS_ASK_AI__LLM_PARAMS_URL';

    /** @var array<string, mixed>|null */
    private ?array $parsed = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @return array<string, float|int|string|bool>
     */
    public function getParamsForModel(string $modelKey): array
    {
        // Params are optional tuning overrides — a missing/unreachable remote
        // (or unset env var) must degrade to provider defaults, never abort
        // the chat turn.
        try {
            $data = $this->load();
        } catch (\Throwable $e) {
            $this->logger?->warning(
                'LLM params unavailable, using provider defaults: ' . $e->getMessage(),
                ['exception' => $e]
            );

            return [];
        }
        $entry = $data['models'][$modelKey] ?? null;
        if (!is_array($entry)) {
            return [];
        }

        return $this->sanitize($entry);
    }

    /**
     * Curated whitelist of chat-capable model keys (the `models` object keys).
     * Empty when the remote is unreachable and no cache exists — callers treat
     * that as "no models available", never a crash.
     *
     * @return list<string>
     */
    public function getModelKeys(): array
    {
        try {
            $data = $this->load();
        } catch (\Throwable $e) {
            $this->logger?->warning(
                'LLM params unavailable, no model keys: ' . $e->getMessage(),
                ['exception' => $e]
            );

            return [];
        }
        $models = $data['models'] ?? null;

        return is_array($models) ? array_values(array_filter(array_keys($models), 'is_string')) : [];
    }

    /**
     * Model keys flagged as the default pre-selection (`"default": true`).
     * Insertion order preserved. Empty on fetch failure. Metadata, not a
     * provider param — kept out of {@see sanitize()}, like `label`.
     *
     * @return list<string>
     */
    public function getDefaultFlaggedKeys(): array
    {
        try {
            $data = $this->load();
        } catch (\Throwable $e) {
            return [];
        }
        $models = $data['models'] ?? null;
        if (!is_array($models)) {
            return [];
        }

        $out = [];
        foreach ($models as $key => $entry) {
            if (is_string($key) && is_array($entry) && ($entry['default'] ?? null) === true) {
                $out[] = $key;
            }
        }

        return $out;
    }

    /**
     * Human-readable label for a model key, as authored in the bucket JSON.
     * Returns null when the key is unknown or has no non-empty `label`.
     * Kept out of {@see sanitize()} so it never leaks into provider params.
     */
    public function getLabelForModel(string $modelKey): ?string
    {
        try {
            $data = $this->load();
        } catch (\Throwable $e) {
            return null;
        }
        $label = $data['models'][$modelKey]['label'] ?? null;

        return is_string($label) && $label !== '' ? $label : null;
    }

    public function getVersion(): ?string
    {
        try {
            $data = $this->load();
        } catch (\Throwable $e) {
            $this->logger?->warning(
                'LLM params unavailable, no version: ' . $e->getMessage(),
                ['exception' => $e]
            );

            return null;
        }
        $version = $data['version'] ?? null;

        return is_string($version) ? $version : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(): array
    {
        if ($this->parsed !== null) {
            return $this->parsed;
        }

        $body = $this->readBody();
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new \RuntimeException('LLM params remote response is not a JSON object.');
        }

        return $this->parsed = $data;
    }

    private function readBody(): string
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
                    'Background LLM params refresh failed: ' . $e->getMessage(),
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
            throw new \RuntimeException('LLM params response body is empty.');
        }
        if (strlen($body) > self::MAX_BYTES) {
            throw new \RuntimeException(sprintf('LLM params exceeds max size of %d bytes (got %d).', self::MAX_BYTES, strlen($body)));
        }

        return $body;
    }

    private function writeCache(string $path, string $content): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger?->warning('Cannot create llm params cache dir: ' . $dir);

            return;
        }
        // Write to a temp file then rename so concurrent readers never see
        // a partially-written cache.
        $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($tmp, $content) === false) {
            $this->logger?->warning('Failed to write llm params cache tmp file: ' . $tmp);

            return;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            $this->logger?->warning('Failed to rename llm params cache tmp file: ' . $tmp);
        }
    }

    private function cachePath(): string
    {
        $base = defined('_PS_CACHE_DIR_') ? _PS_CACHE_DIR_ : sys_get_temp_dir() . '/';

        return rtrim((string) $base, '/') . '/ps_ask_ai/llm_params.json';
    }

    /**
     * Filter per-model entry to known fields with the right scalar type.
     * Unknown keys and bad types are dropped silently — better degraded
     * behaviour (use provider default for that field) than a crashing turn.
     *
     * @param array<mixed> $entry
     *
     * @return array<string, float|int|string|bool>
     */
    private function sanitize(array $entry): array
    {
        $out = [];

        foreach (['temperature', 'top_p'] as $k) {
            if (isset($entry[$k]) && is_numeric($entry[$k])) {
                $out[$k] = (float) $entry[$k];
            }
        }
        foreach (['top_k', 'max_tokens', 'thinking_budget_tokens'] as $k) {
            if (isset($entry[$k]) && is_numeric($entry[$k])) {
                $out[$k] = (int) $entry[$k];
            }
        }
        foreach (['thinking_effort', 'reasoning_summary'] as $k) {
            if (isset($entry[$k]) && is_string($entry[$k]) && $entry[$k] !== '') {
                $out[$k] = $entry[$k];
            }
        }
        if (isset($entry['thinking_include_thoughts']) && is_bool($entry['thinking_include_thoughts'])) {
            $out['thinking_include_thoughts'] = $entry['thinking_include_thoughts'];
        }

        return $out;
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
