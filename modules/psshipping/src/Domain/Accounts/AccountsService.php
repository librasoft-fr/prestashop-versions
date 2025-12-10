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

namespace PrestaShop\Module\Psshipping\Domain\Accounts;

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager as LegacyModuleManager;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer as PsAccountsInstaller;
use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class AccountsService
{
    /**
     * @param Psshipping $module
     *
     * @return array<string, string|boolean|null>
     *
     * @throws AccountsIsNotInstalledException
     */
    public function getAccountsContext(Psshipping $module, bool $onInstall = false): array
    {
        /** @var ModuleManagerBuilder $moduleManagerBuilder */
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();

        /** @var ModuleManager|LegacyModuleManager $moduleManager */
        $moduleManager = $moduleManagerBuilder->build();

        $psAccountsIsInstalled = $moduleManager->isInstalled('ps_accounts');

        if (!$psAccountsIsInstalled) {
            throw new AccountsIsNotInstalledException('PrestaShop accounts (ps_accounts) module is not installed, please install it.');
        }

        // we use this condition because we can't get the facade on install
        if ($onInstall === true) {
            /** @var PsAccounts $accounts */
            $accounts = new PsAccounts(new PsAccountsInstaller('5'));
        } else {
            /** @var PsAccounts $accounts */
            $accounts = $module->get('ps_accounts.facade');
        }

        /** @var AbstractAccountsType $accountPresenter */
        $accountPresenter = $accounts->getPsAccountsPresenter();

        return $accountPresenter->present($module->name);
    }

    public function getPsAccountToken(Psshipping $module): string
    {
        /** @var PsAccounts $accounts */
        $accounts = $module->get('ps_accounts.facade');

        /** @var AbstractAccountsType $accountService */
        $accountService = $accounts->getPsAccountsService();

        return $accountService->getOrRefreshToken();
    }

    public function getPsAccountCdnUrl(): string
    {
        /** @var ModuleManagerBuilder $moduleManagerBuilder */
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();

        /** @var ModuleManager|LegacyModuleManager $moduleManager */
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isInstalled('ps_accounts')) {
            $accountsModule = \Module::getInstanceByName('ps_accounts');
            if ($accountsModule && version_compare($accountsModule->version, '7', '>=')) {
                /* @phpstan-ignore-next-line */
                $accountsCdn = $accountsModule->getParameter('ps_accounts.accounts_cdn_url');
            }
        }

        return $accountsCdn ?? 'https://unpkg.com/prestashop_accounts_vue_components@5';
    }
}
