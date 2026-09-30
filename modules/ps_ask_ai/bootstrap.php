<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */
if (class_exists('\Symfony\Component\Dotenv\Dotenv')) {
    (new Symfony\Component\Dotenv\Dotenv(true))->loadEnv(__DIR__ . '/.env');
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}
