<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service;

use NeuronAI\Workflow\Interrupt\WorkflowInterrupt;
use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use PrestaShop\Module\PsAskAi\Exception\ApiKeyValidationException;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Severity;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Central error reporting to the ps_ask_ai Sentry project.
 *
 * Modeled on ps_enterprise_module's SentryReporter, with one deliberate
 * difference: we run on a DEDICATED Hub/client instead of \Sentry\init().
 * ps_enterprise_module and pscompliancecheck both initialise the global PHP
 * hub — whichever module calls \Sentry\init() last wins, so sharing the
 * global hub would send our events to another module's DSN (or theirs to
 * ours). A private Hub keeps the DSNs strictly separated.
 *
 * Behaviour:
 *  - lazy init on first capture; TOTAL no-op when PS_ASK_AI__SENTRY_DSN is
 *    absent (self-hosted merchants) — no network calls, no SDK bootstrap;
 *  - environment from PS_ASK_AI__SENTRY_ENV ('preprod' default | 'prod'),
 *    same convention as PS_ASK_AI__SEGMENT_ENV;
 *  - release = ps_ask_ai@<module version>;
 *  - expected/user errors are never sent (see shouldReport());
 *  - events are scrubbed (see scrubEvent()): no query strings (they carry
 *    `prompt` and `api_key`), no cookies, no PII. Allowed identifiers are
 *    shop_id (ps_accounts uuid), numeric employee_id and thread_id.
 *
 * The class is intentionally stateless at instance level (all state is
 * static): ToolErrorHandler holds an instance and is serialize()d to disk by
 * the workflow-interrupt persistence, so instances must survive a PHP
 * serialize/unserialize round-trip.
 */
class SentryReporter
{
    private static bool $initialized = false;

    private static ?Hub $hub = null;

    public function captureException(\Throwable $exception, string $eventName, array $metadata = [], array $tags = []): void
    {
        if (!self::shouldReport($exception) || !$this->initialize()) {
            return;
        }

        $metadata = array_merge(['error_message' => $exception->getMessage()], $metadata);

        $this->capture(function (Hub $hub) use ($exception): void {
            $hub->captureException($exception);
        }, $eventName, $metadata, $tags);
    }

    /**
     * Report a swallowed error (something we only log as warning today) as a
     * warning-level Sentry message.
     */
    public function captureWarning(string $message, string $eventName, array $metadata = [], array $tags = []): void
    {
        if (!$this->initialize()) {
            return;
        }

        $this->capture(function (Hub $hub) use ($message): void {
            $hub->captureMessage($message, Severity::warning());
        }, $eventName, $metadata, $tags);
    }

    /**
     * Filter out expected errors that are user/provider state, not bugs:
     *  - WorkflowInterrupt is the nominal tool-approval control flow;
     *  - ApiKeyValidationException carries a machine-readable type — only the
     *    'unknown' bucket is a potential bug on our side;
     *  - provider errors the UI already classifies and handles (invalid key,
     *    quota, overload 429/503/529, context length exceeded) are matched on
     *    message, mirroring the frontend's ErrorType patterns.
     */
    public static function shouldReport(\Throwable $exception): bool
    {
        if ($exception instanceof WorkflowInterrupt) {
            return false;
        }

        if ($exception instanceof ApiKeyValidationException) {
            return $exception->errorType === ApiKeyValidationException::ERROR_UNKNOWN;
        }

        $message = strtolower($exception->getMessage());

        $expectedPatterns = [
            // invalid API key (user error, surfaced as invalidApiKey in the UI)
            'invalid x-api-key', 'invalid api key', 'incorrect api key',
            'authentication_error', 'api key not valid', 'invalid_api_key',
            // quota / billing (providerQuota)
            'insufficient_quota', 'resource_exhausted', 'quota exceeded',
            'out of credits', 'billing', 'usage limits', '429',
            // temporary provider overload (transient)
            'unavailable', 'overloaded', 'high demand', '503', '529',
            // context window (maxTokens)
            'context_length_exceeded', 'context window', 'input is too long',
            'too many tokens', 'prompt is too long', 'maximum context length',
        ];
        foreach ($expectedPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * before_send scrubber. Drops everything that could leak a prompt, an API
     * key or PII:
     *  - the request query string (stream URLs carry `prompt` and `api_key`)
     *    both in `url` and `query_string`;
     *  - cookies and headers (session ids, employee identity).
     *
     * Kept public & static so it is unit-testable without a DSN.
     */
    public static function scrubEvent(Event $event, ?EventHint $hint = null): Event
    {
        $request = $event->getRequest();
        if ($request !== []) {
            if (isset($request['url']) && is_string($request['url'])) {
                $request['url'] = strtok($request['url'], '?');
            }
            unset($request['query_string'], $request['cookies'], $request['headers'], $request['data'], $request['env']);
            $event->setRequest($request);
        }

        return $event;
    }

    private function capture(callable $callback, string $eventName, array $metadata, array $tags): void
    {
        $hub = self::$hub;
        if ($hub === null) {
            return;
        }

        $tags = array_merge([
            'module' => 'ps_ask_ai',
            'event' => $eventName,
        ], $tags);

        $shopId = $this->resolveShopUuid();
        if ($shopId !== null) {
            $tags['shop_id'] = $shopId;
        }

        $metadata = self::filterNulls(array_merge($this->defaultMetadata(), $metadata));

        $hub->withScope(function (Scope $scope) use ($callback, $hub, $tags, $metadata, $eventName): void {
            foreach ($tags as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $scope->setTag((string) $key, (string) $value);
            }

            if ($metadata !== []) {
                $scope->setContext('metadata', $metadata);
            }

            $scope->setFingerprint([$eventName]);

            $callback($hub);
        });
    }

    private function initialize(): bool
    {
        if (self::$initialized) {
            return self::$hub !== null;
        }
        self::$initialized = true;

        $dsn = $this->getEnvValue(PsAskAiConfig::SENTRY_DSN_ENV_VAR);
        if ($dsn === null) {
            return false;
        }

        $environment = $this->getEnvValue(PsAskAiConfig::SENTRY_ENV_ENV_VAR) ?? PsAskAiConfig::SENTRY_DEFAULT_ENV;

        try {
            $client = ClientBuilder::create([
                'dsn' => $dsn,
                'environment' => $environment,
                'release' => 'ps_ask_ai@' . PsAskAiConfig::MODULE_VERSION,
                'send_default_pii' => false,
                'attach_stacktrace' => true,
                'before_send' => [self::class, 'scrubEvent'],
            ])->getClient();

            self::$hub = new Hub($client);
        } catch (\Throwable $e) {
            // A malformed DSN must never break the module.
            self::$hub = null;

            return false;
        }

        return true;
    }

    /**
     * @return array<string, string|int>
     */
    private function defaultMetadata(): array
    {
        $metadata = [];

        try {
            $employee = \Context::getContext()->employee ?? null;
            if ($employee !== null && !empty($employee->id)) {
                $metadata['employee_id'] = (int) $employee->id;
            }
        } catch (\Throwable $e) {
            // Context unavailable (CLI) — fine, identifiers are best-effort.
        }

        return $metadata;
    }

    /**
     * ps_accounts shop uuid — the only shop identifier we send. Best-effort:
     * ps_accounts may be unlinked or the container unavailable (CLI/install).
     */
    private function resolveShopUuid(): ?string
    {
        try {
            $container = SymfonyContainer::getInstance();
            if ($container === null || !$container->has('ps_ask_ai.ps_accounts_facade')) {
                return null;
            }
            /** @var \PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts $facade */
            $facade = $container->get('ps_ask_ai.ps_accounts_facade');
            $uuid = $facade->getPsAccountsService()->getShopUuid();

            return is_string($uuid) && $uuid !== '' ? $uuid : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getEnvValue(string $key): ?string
    {
        $this->ensureEnvLoaded();

        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);
        if ($value === false) {
            return null;
        }
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private static function filterNulls(array $data): array
    {
        return array_filter($data, static fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * Same pattern as NeuronAiService/SegmentService::ensureEnvLoaded — the
     * module bootstrap only loads Dotenv when PrestaShop instantiates the
     * Module class, which doesn't happen in CLI/console contexts.
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
