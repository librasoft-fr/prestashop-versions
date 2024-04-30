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
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\Install\Tabs\ModuleTabsInstaller;
use PrestaShop\Module\PsxDesign\Install\Tabs\ModuleTabsUninstaller;
use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShopBundle\Entity\Repository\TabRepository;

class PsxDesign extends Module
{
    use PrestaShop\Module\PsxDesign\Traits\UseHooks;

    /**
     * Module ID created when registering product on marketplace and required to get information from marketplace.
     */
    public const ADDONS_MODULE_ID = 89361;
    private const PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED = 'PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED';
    private const PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED = 'PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED';

    /**
     * @var ServiceContainer
     */
    private $serviceContainer;

    public function __construct()
    {
        $this->name = 'psxdesign';
        $this->tab = 'others';
        $this->version = '1.6.7';
        $this->author = 'PrestaShop';
        $this->need_instance = 1;
        $this->module_key = '82148d7b60bbd40f98c65ac7ae3e431a';

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans(
            'PrestaShop Design',
            [],
            'Modules.Psxdesign.Admin'
        );

        $this->description =
            $this->getTranslator()->trans(
                'PrestaShop Design allows you to easily and rapidly customize your website theme and autonomously adapt the appearance of your store, brand image and preferences without needing any technical expertise.',
                [],
                'Modules.Psxdesign.Admin'
            );

        $this->ps_versions_compliancy = [
            'min' => '8',
            'max' => _PS_VERSION_,
        ];

        $this->setServiceContainer();
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

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function install(): bool
    {
        /** @var PrestaShop\PrestaShop\Adapter\Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');
        $configuration->set(self::PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED, '0');
        $configuration->set(self::PSXDESIGN_MODULE_TABS_LANG_UPDATE_REQUIRED, '0');

        /** @var TabRepository $tabRepository */
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');

        try {
            $this->getService('psxdesign.ps_accounts.installer')->install();
        } catch (Exception $e) {
            // For now, do nothing
        }

        include dirname(__FILE__) . '/sql/install.php';

        return parent::install() &&
            (new ModuleTabsInstaller($tabRepository, $this->name))->installTabs() &&
            $this->registerHook($this->getHooksNames());
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function uninstall(): bool
    {
        /** @var PrestaShop\PrestaShop\Adapter\Configuration $configuration */
        $configuration = $this->get('prestashop.adapter.legacy.configuration');

        $configuration->remove(self::PSXDESIGN_MODULE_TABS_MODIFICATION_NEEDED);

        /** @var TabRepository $tabRepository */
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');

        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall() &&
            (new ModuleTabsUninstaller($tabRepository, $this))->uninstallTabs() &&
            $this->deleteStylesheets();
    }

    /**
     * Set service container for module connection with ps_account
     */
    private function setServiceContainer(): void
    {
        $this->serviceContainer = new ServiceContainer(
            $this->name,
            $this->getLocalPath()
        );
    }

    /**
     * @param string $serviceName
     *
     * @return object|null
     */
    public function getService(string $serviceName)
    {
        return $this->serviceContainer->getService($serviceName);
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws Exception
     */
    public function getParameter(string $name): string
    {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @param bool $force_all
     *
     * @return bool
     *
     * @throws Exception
     */
    public function enable($force_all = false)
    {
        /** @var TabRepository $tabRepository */
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');

        $modifier = new ModuleTabsInstaller($tabRepository, $this->name);
        $modifier->modifyAdminThemesTab();

        return parent::enable($force_all);
    }

    /**
     * @param bool $force_all
     *
     * @return bool
     */
    public function disable($force_all = false)
    {
        /** @var TabRepository $tabRepository */
        $tabRepository = $this->get('prestashop.core.admin.tab.repository');

        $modifier = new ModuleTabsUninstaller($tabRepository, $this);
        $modifier->modifyAdminThemesTab();

        return parent::disable($force_all); // TODO: Change the autogenerated stub
    }

    /**
     * Delete custom stylesheets.
     *
     * @return bool
     */
    private function deleteStylesheets(): bool
    {
        $moduleCustomStylesheetPath = $this->getLocalPath() . PsxDesignConfig::CUSTOM_STYLESHEETS_PATH;

        $fontFilePath = $moduleCustomStylesheetPath . PsxDesignConfig::FONTS_CSS_SELECTOR_STYLESHEET_FILE_NAME;
        $cssSelectorFilePath = $moduleCustomStylesheetPath . PsxDesignConfig::COLORS_CSS_SELECTOR_STYLESHEET_FILE_NAME;
        $cssVariableFilePath = $moduleCustomStylesheetPath . PsxDesignConfig::COLORS_CSS_VARIABLE_STYLESHEET_FILE_NAME;

        $isDeleted = true;

        if (file_exists($fontFilePath)) {
            $isDeleted = unlink($fontFilePath);
        }
        if (file_exists($cssSelectorFilePath)) {
            $isDeleted = unlink($cssSelectorFilePath) && $isDeleted;
        }
        if (file_exists($cssVariableFilePath)) {
            $isDeleted = unlink($cssVariableFilePath) && $isDeleted;
        }

        return $isDeleted;
    }
}
