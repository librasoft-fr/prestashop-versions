<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsxDesign\Exception\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use Exception;
use Module;
use PrestaShop\Module\PsxDesign\Account\Provider\PsAccountDataProvider;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use Raven_Client;
use Throwable;

/**
 * Handle Error.
 */
class SentryExceptionErrorHandler implements ErrorHandlerInterface
{
    /** Used to filter informational exceptions. Info severity codes between 1-100 */
    private const INFO_SEVERITY = 100;

    /**
     * @var Raven_Client|null
     */
    protected $client;

    public function __construct(
        PsAccountDataProvider $accountService,
        ModuleManager $moduleManager,
        string $sentryDsn,
        string $sentryEnv,
        string $moduleName,
        string $moduleVersion
    ) {
        $psAccounts = Module::getInstanceByName('ps_accounts');

        try {
            $this->client = new Raven_Client(
                $sentryDsn,
                [
                    'level' => 'warning',
                    'tags' => [
                        'shop_id' => $accountService->getAccountShopId(),
                        'psxdesign_version' => $moduleVersion,
                        'ps_accounts_version' => $psAccounts ? $psAccounts->version : false,
                        'php_version' => phpversion(),
                        'prestashop_version' => _PS_VERSION_,
                        'psxdesign_is_enabled' => $moduleManager->isEnabled($moduleName),
                        'psxdesign_is_installed' => $moduleManager->isInstalled($moduleName),
                        'environment' => $sentryEnv,
                    ],
                ]
            );
            /** @var string $configurationPsShopEmail */
            $configurationPsShopEmail = Configuration::get('PS_SHOP_EMAIL');
            $this->client->set_user_data($accountService->getAccountShopId(), $configurationPsShopEmail);
        } catch (\Exception $e) {
            //We do not want stop the if sentry will fail to send
        }
    }

    /**
     * @param Throwable|Exception $error the Throwable/Exception object
     * @param mixed $code
     * @param bool|null $throw
     * @param array|null $data
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle($error, $code = null, ?bool $throw = false, ?array $data = null): void
    {
        /* Prevent to send informational exceptions */
        if ($this->isInfoSeverity($code)) {
            return;
        }

        if (!$this->client) {
            return;
        }
        $this->client->captureException($error, $data);
        if (is_int($code) && true === $throw) {
            http_response_code($code);
            throw $error;
        }
    }

    /**
     * Used to filter informational exceptions as we do not want to send them
     *
     * @param int $code
     *
     * @return bool
     */
    private function isInfoSeverity(int $code): bool
    {
        return self::INFO_SEVERITY >= $code;
    }
}
