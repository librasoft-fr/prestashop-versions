<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

namespace PrestaShop\Module\PsAskAi\Factory;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class PsAskAiLogger
{
    use \PrestaShop\Module\PsAskAi\Traits\UseConstants;

    public const MAX_FILES = 15;

    /**
     * Create logger.
     *
     * @return Logger
     */
    public static function create()
    {
        $log_dir = join(DIRECTORY_SEPARATOR, [_PS_ROOT_DIR_, 'var', 'modules', self::$PS_ASK_AI_MODULE_NAME, 'logs']);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        $rotatingFileHandler = new RotatingFileHandler(
            join(DIRECTORY_SEPARATOR, [$log_dir, self::$PS_ASK_AI_MODULE_NAME . '.log']),
            static::MAX_FILES,
        );
        $logger = new Logger(self::$PS_ASK_AI_MODULE_NAME);
        $logger->pushHandler($rotatingFileHandler);

        return $logger;
    }
}
