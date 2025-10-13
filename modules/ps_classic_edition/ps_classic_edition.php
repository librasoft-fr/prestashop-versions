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

use PrestaShop\Module\PsClassicEdition\Install\Tabs\TabsInstaller;
use PrestaShop\Module\PsClassicEdition\Install\Tabs\TabsUninstaller;

define('PS_CLASSIC_EDITION_SETTINGS_WHITE_LIST', json_decode(file_get_contents(__DIR__ . '/settingsWhiteList.json'), true));
define('PS_CLASSIC_EDITION_SETTINGS_BLACK_LIST', json_decode(file_get_contents(__DIR__ . '/settingsBlackList.json'), true));
define('PS_CLASSIC_EDITION_MENU_WHITE_LIST', json_decode(file_get_contents(__DIR__ . '/menuWhiteList.json'), true));

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class ps_classic_edition extends Module
{
    use PrestaShop\Module\PsClassicEdition\Traits\UseHooks;

    private string $userflow_id;

    public function __construct()
    {
        $this->name = 'ps_classic_edition';
        $this->version = '1.0.2';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
        $this->module_key = '5530785cbb44445d52d2a98f8ff6d309';

        parent::__construct();

        $this->displayName = $this->trans('PrestaShop Classic Edition', [], 'Modules.Classicedition.Admin');
        $this->description = $this->trans('PrestaShop Classic Edition.', [], 'Modules.Classicedition.Admin');
        $this->userflow_id = 'ct_55jfryadgneorc45cjqxpbf6o4';
        $this->bootstrap = true;
    }

    /**
     * This function is required in order to make module compatible with new translation system.
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        $this->uninstallBasicEditionModule();

        $installed =
            parent::install()
            && (new TabsInstaller($this->name, $this->getTranslator()))->run()
            && $this->registerHook($this->getHooksNames())
        ;
        if (!$installed) {
            return false;
        }

        // We hide the setup guide by default on install, if we want to enable it again later
        // we'll just have to remove this line
        Configuration::updateGlobalValue('PS_SETUP_GUIDE_MODAL_IS_HIDDEN', 1);

        return true;
    }

    /**
     * @throws Exception
     */
    public function uninstall(): bool
    {
        return parent::uninstall()
            && (new TabsUninstaller($this->name))->run();
    }

    public function disable($force_all = false): bool
    {
        return parent::disable($force_all)
            && (new TabsUninstaller($this->name))->run();
    }

    /**
     * @throws Exception
     */
    public function enable($force_all = false): bool
    {
        return parent::enable($force_all)
            && (new TabsInstaller($this->name, $this->getTranslator()))->run();
    }

    protected function uninstallBasicEditionModule(): void
    {
        $oldModule = Module::getInstanceByName('ps_edition_basic');
        if ($oldModule) {
            $oldModule->uninstall();
        }
    }
}
