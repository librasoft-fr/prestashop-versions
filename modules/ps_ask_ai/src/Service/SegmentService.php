<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of PrestaShop AskAI.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Emits AskAI configuration-page analytics to Segment's HTTP Tracking API,
 * server-side. Every event carries a shared set of common properties and
 * deliberately contains NO merchant PII (no name, email, or shop customer
 * data) — only the connected employee id, store version, and module metadata.
 *
 * Calls are fire-and-forget: a short timeout bounds the latency added to the
 * config flow, and any failure is swallowed (logged at debug) so tracking can
 * never break or visibly slow down saving a configuration.
 *
 * The environment (which write key is used) is selected by the
 * PS_ASK_AI__SEGMENT_ENV env var and defaults to preprod, so production events
 * only fire when explicitly opted in.
 */
class SegmentService
{
    private const REQUEST_TIMEOUT_SECONDS = 2;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly \Context $context,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Send one tracking event. The given $properties are merged on top of the
     * common properties shared by every event.
     *
     * @param array<string, mixed> $properties
     */
    public function track(string $event, array $properties = []): void
    {
        $writeKey = $this->resolveWriteKey();
        if ($writeKey === '') {
            return;
        }

        $payload = [
            'event' => $event,
            'anonymousId' => $this->anonymousId(),
            'properties' => array_merge($this->buildCommonProperties(), $properties),
            'timestamp' => date('c'),
        ];

        try {
            $response = $this->httpClient->request('POST', PsAskAiConfig::SEGMENT_TRACK_URL, [
                'timeout' => self::REQUEST_TIMEOUT_SECONDS,
                'max_duration' => self::REQUEST_TIMEOUT_SECONDS,
                'auth_basic' => [$writeKey, ''],
                'json' => $payload,
            ]);
            // Symfony HttpClient is lazy; touch the status to actually flush the
            // request. Bounded by the timeout above. The value itself is ignored.
            $response->getStatusCode();
        } catch (\Throwable $e) {
            $this->logger?->debug('Segment track failed: ' . $e->getMessage(), ['event' => $event]);
        }
    }

    /**
     * Properties present on every event. No PII.
     *
     * @return array<string, mixed>
     */
    private function buildCommonProperties(): array
    {
        return [
            'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '',
            'module' => PsAskAiConfig::SEGMENT_MODULE_NAME,
            'module_version' => PsAskAiConfig::MODULE_VERSION,
            'idemployee' => $this->employeeId(),
            'timestamp' => date('c'),
        ];
    }

    /**
     * Stable, non-PII identifier required by Segment to attribute events.
     * Built from internal numeric ids only — never from name/email.
     */
    private function anonymousId(): string
    {
        $shopId = isset($this->context->shop) ? (int) $this->context->shop->id : 0;

        return sprintf('ps-employee-%d-%d', $shopId, $this->employeeId());
    }

    private function employeeId(): int
    {
        return isset($this->context->employee) ? (int) $this->context->employee->id : 0;
    }

    /**
     * Resolve the back-end write key for the active environment. Unknown or
     * unset env value falls back to the default (preprod).
     */
    private function resolveWriteKey(): string
    {
        $this->ensureEnvLoaded();

        $env = (string) getenv(PsAskAiConfig::SEGMENT_ENV_ENV_VAR);
        if (!isset(PsAskAiConfig::SEGMENT_WRITE_KEYS[$env])) {
            $env = PsAskAiConfig::SEGMENT_DEFAULT_ENV;
        }

        return PsAskAiConfig::SEGMENT_WRITE_KEYS[$env];
    }

    /**
     * Same pattern as NeuronAiService/PrepromptService::ensureEnvLoaded — the
     * module bootstrap only loads Dotenv when PrestaShop instantiates the
     * Module, which doesn't happen in CLI/console contexts.
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
            // @phpstan-ignore function.alreadyNarrowedType, function.impossibleType (true on PS9 / false on PS8 Symfony 4.4)
            if (method_exists($dotenv, 'usePutenv')) {
                $dotenv->usePutenv(true);
            }
            $dotenv->loadEnv($envPath);
        }
    }
}
