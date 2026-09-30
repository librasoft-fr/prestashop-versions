<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits;

use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException;
use PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

trait UseInitAccountsService
{
    use UseConstants;
    use UseGetService;
    use UseLogger;

    private mixed $psAccountsService;

    private function initAccountsService(bool $addContextToFront = false): bool
    {
        try {
            try {
                /** @var PsAccounts */
                $accountsFacade = $this->getService(self::$PS_ACCOUNTS_FACADE);
                $this->psAccountsService = $accountsFacade->getPsAccountsService();
            } catch (ModuleNotInstalledException|ModuleVersionException $e) {
                /** @var Installer */
                $accountsInstaller = $this->getService(self::$PS_ACCOUNTS_INSTALLER);
                $accountsInstaller->install();
                /** @var PsAccounts */
                $accountsFacade = $this->getService(self::$PS_ACCOUNTS_FACADE);
                $this->psAccountsService = $accountsFacade->getPsAccountsService();
            }
        } catch (\Throwable $e) {
            /* @phpstan-ignore function.alreadyNarrowedType */
            if (method_exists($this, 'getContext')) {
                $this->getContext()->controller->errors[] = $e->getMessage();
            }
            $this->log('Could not initialize psAccounts service, it may not be installed');
            $this->psAccountsService = null;

            return false;
        }

        if ($addContextToFront) {
            \Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present(self::$PS_ASK_AI_MODULE_NAME),
            ]);
        }

        return true;
    }
}
