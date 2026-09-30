<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits;

use Psr\Log\LoggerInterface;

trait UseLogger
{
    use UseGetService;
    use UseConstants;

    private ?LoggerInterface $logger = null;

    public function log(string $whatToLog): void
    {
        try {
            if (null === $this->logger) {
                $this->logger = $this->getService(self::$PS_ASK_AI_LOGGER_SERVICE_NAME);
                if ($this->logger) { // @phpstan-ignore-line
                    $this->logger->info($whatToLog);
                } else {
                    // During install, the monolog logger is not yet available
                    // Instead, let's "manually" log
                    $log_dir = join(DIRECTORY_SEPARATOR, [_PS_ROOT_DIR_, 'var', 'modules', self::$PS_ASK_AI_MODULE_NAME, 'logs']);
                    if (!is_dir($log_dir)) {
                        mkdir($log_dir, 0775, true);
                    }
                    file_put_contents(join(DIRECTORY_SEPARATOR, [$log_dir, self::$PS_ASK_AI_MODULE_NAME . '-' . date('Y-m-d') . '_manual.log']), $whatToLog . PHP_EOL, FILE_APPEND);
                }
            } else {
                $this->logger->info($whatToLog);
            }
        } catch (\Throwable $e) {
            \PrestaShopLogger::addLog('ps_ask_ai tried to log ' . $whatToLog . ' but got an error : ' . $e->getMessage());
        }
    }
}
