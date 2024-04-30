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

namespace PrestaShop\Module\PsxDesign\Account\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Exception;
use Module;
use PrestaShop\Module\PsAccounts\Provider\OAuth2\PrestaShopSession;
use PrestaShop\Module\PsAccounts\Service\PsAccountsService;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignAccountsException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Ps_accounts;

class PsAccountDataProvider
{
    /**
     * @var PsAccounts
     */
    private $psAccountsFacade;

    public function __construct(PsAccounts $psAccountsFacade)
    {
        $this->psAccountsFacade = $psAccountsFacade;
    }

    /**
     * @return string|null
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     * @throws PsxDesignException
     */
    public function getAccountShopId(): ?string
    {
        if (!$this->isAccountLinked()) {
            return null;
        }

        return $this->getAccountsService()->getShopUuid() ?? null;
    }

    /**
     * @return string|null
     */
    public function getOrRefreshAccessToken(): ?string
    {
        if (!$this->isAccountLinked()) {
            return null;
        }

        try {
            $psSession = $this->getPrestashopSessionService();
            $token = $psSession->getOrRefreshAccessToken();
        } catch (Exception $e) {
            throw new PsxDesignAccountsException('Failed to retrieve ps_accounts service', PsxDesignAccountsException::FAILED_GET_SERVICE);
        }

        return $token;
    }

    /**
     * @return bool
     */
    private function isAccountLinked(): bool
    {
        try {
            return $this->getAccountsService()->isAccountLinked();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return PsAccountsService
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     * @throws PsxDesignException
     */
    private function getAccountsService(): PsAccountsService
    {
        $psAccountService = $this->psAccountsFacade->getPsAccountsService();

        if (!($psAccountService instanceof PsAccountsService)) {
            throw new PsxDesignAccountsException('Failed to retrieve service', PsxDesignAccountsException::FAILED_GET_SERVICE);
        }

        return $psAccountService;
    }

    /**
     * @return PrestaShopSession
     *
     * @throws ModuleNotInstalledException
     * @throws ModuleVersionException
     * @throws PsxDesignException
     */
    private function getPrestashopSessionService(): PrestaShopSession
    {
        try {
            $psAccountsModule = Module::getInstanceByName('ps_accounts');

            if (!($psAccountsModule instanceof Ps_accounts)) {
                throw new PsxDesignAccountsException('Failed to retrieve ps account service', PsxDesignAccountsException::FAILED_GET_PS_ACCOUNTS_SERVICE);
            }

            $psSessionService = $psAccountsModule->getService(PrestaShopSession::class);
        } catch (Exception $e) {
            throw new PsxDesignAccountsException('Failed to retrieve ps_account session service', PsxDesignAccountsException::FAILED_GET_PS_ACCOUNTS_SESSION_SERVICE);
        }

        return $psSessionService;
    }
}
