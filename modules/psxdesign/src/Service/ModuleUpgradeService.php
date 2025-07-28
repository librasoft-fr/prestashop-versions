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

namespace PrestaShop\Module\PsxDesign\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Module;
use PrestaShop\Module\Mbo\Service\ModulesHelper;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ModuleUpgradeService
{
    private const MBO_MODULE_NAME = 'ps_mbo';

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $moduleName;

    public function __construct(ModuleManager $moduleManager, string $moduleName)
    {
        $this->moduleManager = $moduleManager;
        $this->moduleName = $moduleName;
    }

    /**
     * @return bool
     */
    public function isUpgradeAvailable(): bool
    {
        //check if service exist
        try {
            $module = Module::getInstanceByName($this->moduleName);

            /** @var ModulesHelper $mboModulesHelper */
            $mboModulesHelper = $module->get('mbo.modules.helper');
        } catch (ServiceNotFoundException $e) {
            return false;
        }

        //checking if mbo module is installed
        if (false === $this->moduleManager->isInstalledAndActive(self::MBO_MODULE_NAME)) {
            return false;
        }

        // if method exist and findVersionForUpdate returns module instance if upgrade is available otherwise null
        if (method_exists($mboModulesHelper, 'findForUpdates') && $mboModulesHelper->findForUpdates($this->moduleName)) {
            return true;
        }

        return false;
    }
}
