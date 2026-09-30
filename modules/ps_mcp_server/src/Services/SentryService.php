<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SentryService
{
    private bool $isInitialized = false;

    private const MODULE_NAMESPACE = 'PrestaShop\\Module\\PsMcpServer\\';

    public function init(?string $env = null, ?string $version = null, ?string $dsn = null): void
    {
        $allowedEnvs = ['production', 'development', 'preproduction', 'e2e'];

        if ($this->isInitialized || !in_array($env, $allowedEnvs) || empty($dsn)) {
            return;
        }

        try {
            \PsMcpServerDeps\Sentry\init([
                'dsn' => $dsn,
                'environment' => $env,
                'release' => 'ps_mcp_server@' . $version,
                'send_default_pii' => false,
                'error_types' => E_ALL,
                'before_send' => function (\PsMcpServerDeps\Sentry\Event $event, ?\PsMcpServerDeps\Sentry\EventHint $hint): ?\PsMcpServerDeps\Sentry\Event {
                    if ($hint === null || $hint->exception === null) {
                        return null;
                    }

                    if ($hint->exception instanceof \ErrorException) {
                        return self::isModuleFile($hint->exception->getFile()) ? $event : null;
                    }

                    return self::isFromModule($hint->exception) ? $event : null;
                },
            ]);

            \PsMcpServerDeps\Sentry\configureScope(function (\PsMcpServerDeps\Sentry\State\Scope $scope): void {
                $scope->setTag('prestashop_version', _PS_VERSION_);
                $scope->setTag('php_version', PHP_VERSION);
            });

            $this->isInitialized = true;
        } catch (\Exception $e) {
            error_log('Sentry initialization failed: ' . $e->getMessage());
        }
    }

    public static function capture(\Throwable $e, array $context = []): void
    {
        \PsMcpServerDeps\Sentry\configureScope(function (\PsMcpServerDeps\Sentry\State\Scope $scope) use ($context) {
            foreach ($context as $key => $value) {
                $scope->setExtra($key, $value);
            }
        });

        \PsMcpServerDeps\Sentry\captureException($e);
    }

    public static function isFromModule(\Throwable $e): bool
    {
        if (self::isInModuleNamespace(get_class($e)) || self::isModuleFile($e->getFile())) {
            return true;
        }

        $trace = $e->getTrace();
        $framesToCheck = min(5, count($trace));

        for ($i = 0; $i < $framesToCheck; ++$i) {
            $frame = $trace[$i];
            if (
                (isset($frame['class']) && self::isInModuleNamespace($frame['class']))
                || (isset($frame['file']) && self::isModuleFile($frame['file']))
            ) {
                return true;
            }
        }

        return false;
    }

    private static function isInModuleNamespace(string $class): bool
    {
        return str_starts_with((string) $class, self::MODULE_NAMESPACE);
    }

    private static function isModuleFile(?string $file): bool
    {
        if (empty($file)) {
            return false;
        }

        $modulePath = realpath(_PS_MODULE_DIR_ . 'ps_mcp_server');
        $realFile = realpath($file);

        if ($modulePath !== false && $realFile !== false) {
            $vendorPath = $modulePath . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;

            return str_starts_with($realFile, $modulePath . DIRECTORY_SEPARATOR)
                && !str_starts_with($realFile, $vendorPath);
        }

        return (bool) preg_match('#[/\\\\]ps_mcp_server[/\\\\](?!vendor[/\\\\])#', $file);
    }
}
