<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits;

trait UseConstants
{
    public static string $PS_ASK_AI_MODULE_NAME = 'ps_ask_ai';

    /* -------------------------------------------------------------------------- */
    /*                                Service names */
    /* -------------------------------------------------------------------------- */
    public static string $PS_ASK_AI_LOGGER_SERVICE_NAME = 'ps_ask_ai.logger';
    public static string $PS_ACCOUNTS_FACADE = 'ps_ask_ai.ps_accounts_facade';
    public static string $PS_ACCOUNTS_INSTALLER = 'ps_ask_ai.ps_accounts_installer';
    public static string $PS_BILLING_FACADE = 'ps_ask_ai.ps_billings_facade';
}
