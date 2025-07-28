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

use PrestaShop\Module\PsEditionBasic\Actions\Uninstall;
use PrestaShop\Module\PsEditionBasic\Install\Tabs\TabsInstaller;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Psr\Container\ContainerInterface;

define('PS_EDITION_BASIC_SETTINGS_WHITE_LIST', json_decode(file_get_contents(__DIR__ . '/settingsWhiteList.json'), true));
define('PS_EDITION_BASIC_SETTINGS_BLACK_LIST', json_decode(file_get_contents(__DIR__ . '/settingsBlackList.json'), true));
define('PS_EDITION_BASIC_MENU_WHITE_LIST', json_decode(file_get_contents(__DIR__ . '/menuWhiteList.json'), true));

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    (new \Symfony\Component\Dotenv\Dotenv(true))->loadEnv(__DIR__ . '/.env');
}

class ps_edition_basic extends Module
{
    use PrestaShop\Module\PsEditionBasic\Traits\UseHooks;
    use PrestaShop\Module\PsEditionBasic\Traits\HaveConfigurationPage;

    private const PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED = 'PS_EDITION_BASIC_MODULE_TABS_LANG_UPDATE_REQUIRED';

    private string $userflow_id;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public int $addons_id = 91027;

    public function __construct()
    {
        $this->name = 'ps_edition_basic';
        $this->version = '1.0.20';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->module_key = '5530785cbb44445d52d2a98f8ff6d309';

        parent::__construct();

        $this->displayName = $this->trans('PrestaShop Edition Basic', [], 'Modules.Editionbasic.Admin');
        $this->description = $this->trans('PrestaShop Edition Basic.', [], 'Modules.Editionbasic.Admin');
        $this->userflow_id = 'ct_55jfryadgneorc45cjqxpbf6o4';
        $this->bootstrap = true;
    }

    /**
     * @param string $serviceName
     *
     * @return object
     */
    public function get($serviceName)
    {
        if (null === $this->container) {
            $this->container = SymfonyContainer::getInstance();
        }

        return $this->container->get($serviceName);
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
        $filePath = $this->getModulePath('smb_edition');
        $moduleIsPresentOnDisk = file_exists($filePath);

        // Activate new menu on edition shop
        if ($moduleIsPresentOnDisk) {
            Configuration::updateValue('SMB_IS_NEW_MENU_ENABLED', true);
        } else {
            // Deactivate on basic shop
            Configuration::updateValue('SMB_IS_NEW_MENU_ENABLED', false);
        }

        return parent::install()
            && (new TabsInstaller($this->name))->installTabs()
            && $this->registerHook($this->getHooksNames());
    }

    public function postInstall(): bool
    {
        $cacheClearer = null;
        try {
            $cacheClearer = $this->getContainer()->get('prestashop.adapter.cache.clearer.symfony_cache_clearer');
            $cacheClearer->clear();
        } catch (Exception $e) {
            // TODO ?
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function uninstall(): bool
    {
        Configuration::deleteByName('SMB_IS_NEW_MENU_ENABLED');

        return parent::uninstall()
            && (new Uninstall($this->name))->run();
    }

    /**
     * @throws Exception
     */
    public function enable($force_all = false): bool
    {
        (new TabsInstaller($this->name))->installTabs();

        return parent::enable($force_all);
    }

    public function disable($force_all = false): bool
    {
        return parent::disable($force_all);
    }

    private function addAdminThemeMedia(): void
    {
        $this->context->controller->addCSS($this->getParameter('ps_edition_basic.edition_basic_admin_css'));
        Media::addJsDef(['ps_edition_basic_favicon' => $this->getPathUri() . 'views/favicon.png']);
        $this->context->controller->addJS($this->getPathUri() . 'views/js/favicon.js');
        // Hide minified setup guide if not in edition shop
    }

    public function getParameter(string $key)// @phpstan-ignore-line
    {
        return $this->getContainer()->hasParameter($key) ? $this->getContainer()->getParameter($key) : null;
    }

    private function getModulePath(string $name): string
    {
        return _PS_MODULE_DIR_ . $name . '/' . $name . '.php';
    }
}
