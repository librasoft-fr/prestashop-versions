<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Config;

/**
 * Names of every PrestaShop Configuration key the module reads/writes.
 * Kept in a PSR-4 class so services/controllers can consume them without
 * depending on the legacy ps_ask_ai module class.
 */
final class PsAskAiConfig
{
    public const CONFIG_MODEL_KEY = 'PS_ASK_AI_MODEL_KEY';
    public const CONFIG_ACTIVE_PROVIDER = 'PS_ASK_AI_ACTIVE_PROVIDER';
    public const CONFIG_API_KEY = 'PS_ASK_AI_API_KEY';
    public const CONFIG_API_KEY_ERROR = 'PS_ASK_AI_API_KEY_ERROR';
    public const CONFIG_PROVIDER_QUOTA_ERROR = 'PS_ASK_AI_PROVIDER_QUOTA_ERROR';

    /**
     * Set to '1' the first time Ask AI turns PrestaShop's webservice on from
     * its own UI (config page or drawer modal). Lets the store-access modal
     * tell "Enable" (never enabled by us) from "Re-enable" (we enabled it
     * before, it is now off) without needing an audit trail on the global
     * PS_WEBSERVICE setting — which is shared and not attributable per user.
     */
    public const CONFIG_WEBSERVICE_ENABLED_ONCE = 'PS_ASK_AI_WS_ENABLED_ONCE';

    /**
     * Provider ids accepted by the configure form. Only one of these can be
     * active at a time; the chosen one drives both the API key in storage and
     * the models offered through the UI.
     *
     * @var list<string>
     */
    public const SUPPORTED_PROVIDERS = [
        'gemini',
        'anthropic',
        'openai',
        'mistral',
    ];

    /**
     * Per-provider env var holding the API key when none is stored in the BO.
     *
     * @var array<string, string>
     */
    public const PROVIDER_API_KEY_ENV = [
        'gemini' => 'PS_ASK_AI__GEMINI_API_KEY',
        'anthropic' => 'PS_ASK_AI__ANTHROPIC_API_KEY',
        'openai' => 'PS_ASK_AI__OPENAI_API_KEY',
        'mistral' => 'PS_ASK_AI__MISTRAL_API_KEY',
    ];

    /**
     * Module version reported on Segment tracking events and used as the
     * Sentry release. Keep in sync with ps_ask_ai::$version and config.xml.
     */
    public const MODULE_VERSION = '2.0.0';

    /**
     * Module identifier reported on every Segment event.
     */
    public const SEGMENT_MODULE_NAME = 'askai';

    /**
     * Segment HTTP Tracking API endpoint (server-side, fire-and-forget).
     */
    public const SEGMENT_TRACK_URL = 'https://api.segment.io/v1/track';

    /**
     * PHP back-end write keys, keyed by environment. The matching JS front-end
     * write keys are intentionally not stored: every config-page event is
     * emitted server-side, so only these back-end keys are used.
     *
     * @var array<string, string>
     */
    public const SEGMENT_WRITE_KEYS = [
        'preprod' => '23s6sguWjmgWaus5QmJEoKJhQeXayhwG',
        'prod' => 'HmnlDZuyjUqrn4z1lonuPqZXTSgZJyVc',
    ];

    /**
     * Default Segment environment when SEGMENT_ENV_ENV_VAR is unset/unknown.
     * Defaults to preprod so production events only fire when explicitly opted in.
     */
    public const SEGMENT_DEFAULT_ENV = 'preprod';

    /**
     * Env var selecting the Segment environment: 'preprod' (default) or 'prod'.
     */
    public const SEGMENT_ENV_ENV_VAR = 'PS_ASK_AI__SEGMENT_ENV';

    /**
     * Env var holding the backend Sentry DSN. When unset (self-hosted
     * merchants), SentryReporter is a total no-op — no SDK init, no network.
     */
    public const SENTRY_DSN_ENV_VAR = 'PS_ASK_AI__SENTRY_DSN';

    /**
     * Env var selecting the Sentry environment: 'preprod' (default) or
     * 'prod'. Same convention as PS_ASK_AI__SEGMENT_ENV.
     */
    public const SENTRY_ENV_ENV_VAR = 'PS_ASK_AI__SENTRY_ENV';

    /**
     * Default Sentry environment when SENTRY_ENV_ENV_VAR is unset.
     */
    public const SENTRY_DEFAULT_ENV = 'preprod';

    /**
     * Canonical Segment event names emitted from the configuration page.
     */
    public const SEGMENT_EVENT_CONFIG_PAGE_VIEWED = 'AskAI Config Page Viewed';
    public const SEGMENT_EVENT_PROVIDER_SELECTED = 'AskAI Provider Selected';
    public const SEGMENT_EVENT_API_KEY_SUBMITTED = 'AskAI API Key Submitted';
    public const SEGMENT_EVENT_CONFIG_SAVE_FAILED = 'AskAI Config Save Failed';
    public const SEGMENT_EVENT_CONFIG_SAVED = 'AskAI Config Saved';
}
