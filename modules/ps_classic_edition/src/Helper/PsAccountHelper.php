<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PsClassicEdition\Helper;

use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts as PsAccountsFacade;
use PrestaShop\PsAccountsInstaller\Installer\Installer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Service in charge of fetching PrestaShop Accounts data by mixing the data from multiple services.
 */
class PsAccountHelper
{
    public function __construct(
        #[Autowire(service: 'ps_classic_edition.ps_accounts.installer')]
        private readonly Installer $accountsInstaller,
        #[Autowire(service: 'ps_classic_edition.ps_accounts.facade')]
        private readonly PsAccountsFacade $psAccountsFacade,
        #[Autowire(service: 'ps_classic_edition.module')]
        private readonly \ps_classic_edition $modulePsClassicEdition,
    ) {
    }

    public function loadAccountSettings(): array
    {
        $psAccountID = '';
        $psShopID = '';
        $accountUserToken = '';
        $urlAccountsCdn = '';
        $psAccountService = null;

        try {
            // Install account module automatically
            $this->accountsInstaller->install();
            $psAccountService = $this->psAccountsFacade->getPsAccountsService();
        } catch (\Throwable) {
        }

        if ($psAccountService) {
            try {
                $employeeAccount = $psAccountService->getEmployeeAccount();
                $psAccountID = ($employeeAccount ? $employeeAccount->getUid() : $psAccountService->getUserUuid());
                $psShopID = $psAccountService->getShopUuid();
                $urlAccountsCdn = $psAccountService->getAccountsCdn();
                // New method starting from PS Accounts 7.1.1
                if (method_exists($psAccountService, 'getShopToken')) {
                    $accountUserToken = strval($psAccountService->getShopToken());
                } elseif (method_exists($psAccountService, 'getOrRefreshToken')) {
                    $accountUserToken = strval($psAccountService->getOrRefreshToken());
                }
            } catch (\Throwable) {
            }
        }
        \Media::addJsDef([
            'contextPsAccounts' => $this->psAccountsFacade->getPsAccountsPresenter()->present($this->modulePsClassicEdition->name),
        ]);

        return [
            'psAccountID' => $psAccountID ?: '',
            'psShopID' => $psShopID ?: '',
            'accountUserToken' => $accountUserToken ?: '',
            'urlAccountsCdn' => $urlAccountsCdn ?: '',
        ];
    }
}
