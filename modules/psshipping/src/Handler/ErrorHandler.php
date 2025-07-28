<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Handler;

use Module;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class ErrorHandler
{
    /**
     * @var ModuleFilteredRavenClient
     */
    protected $client;

    public function __construct()
    {
        /** @var Psshipping $module */
        $module = Module::getInstanceByName('psshipping');

        $this->client = new ModuleFilteredRavenClient(
            $module->getSentryDsn(),
            [
                'level' => 'warning',
                'tags' => [
                    'php_version' => phpversion(),
                    'psshipping_version' => $module->version,
                    'prestashop_version' => _PS_VERSION_,
                    'psshipping_is_enabled' => \Module::isEnabled('psshipping'),
                    'psshipping_is_installed' => \Module::isInstalled('psshipping'),
                    'env' => $module->getSentryEnv(),
                ],
                'release' => "v{$module->version}",
            ]
        );

        // We use realpath to get errors even if module is behind a symbolic link
        $this->client->setAppPath(realpath(_PS_MODULE_DIR_ . $module->name . '/'));
        // - Do no not add the shop root folder, it will exclude everything even if specified in the app path.
        // - Excluding vendor/ avoids errors comming from one of your libraries library when called by another module.
        $this->client->setExcludedAppPaths([
            realpath(_PS_MODULE_DIR_ . $module->name . '/vendor/'),
        ]);
        // $this->client->setExcludedDomains(['127.0.0.1', 'localhost', '.local']);

        if (version_compare(phpversion(), '7.4.0', '>=') && version_compare(_PS_VERSION_, '8.0', '<')) {
            return;
        }

        $this->client->install();
    }

    /**
     * @param \Exception $error
     * @param int $code
     * @param bool|null $throw
     * @param array<string>|null $data
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle($error, $code = null, $throw = true, $data = null)
    {
        $this->client->captureException($error, $data);
        if ($code && true === $throw) {
            http_response_code($code);
            throw $error;
        }
    }

    /**
     * @return void
     */
    private function __clone()
    {
    }
}
