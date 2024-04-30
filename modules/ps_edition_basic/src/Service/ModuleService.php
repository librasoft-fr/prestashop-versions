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

namespace PrestaShop\Module\PsEditionBasic\Service;

use Context;
use PrestaShop\Module\Mbo\Service\ModulesHelper;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Module\ModuleManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ModuleService
{
    private const MBO_MODULE_NAME = 'ps_mbo';
    private const ADDONS_URL = 'https://api-addons.prestashop.com';

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var int
     */
    private $moduleId;

    public function __construct(
        ModuleManager $moduleManager,
        string $moduleName,
        int $moduleId
    ) {
        $this->moduleManager = $moduleManager;
        $this->moduleName = $moduleName;
        $this->moduleId = $moduleId;
    }

    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * returns the update link of the module if it is not enabled. If enabled, returns an empty string
     *
     * @return string
     */
    public function getUpdateLink(): string
    {
        $router = $this->get('router');

        $context = Context::getContext();

        return substr(\Tools::getShopDomainSsl(true) . __PS_BASE_URI__, 0, -1) .
            $router->generate('admin_module_manage_action', [
                'action' => 'upgrade',
                'module_name' => $this->moduleName,
                'source' => self::ADDONS_URL . '?method=module&format=json&channel=stable&iso_code=' . $context->language->iso_code . '&iso_lang=' . $context->language->language_code . '&version=' . _PS_VERSION_ . '&id_module=' . $this->moduleId . '&shop_url=' . preg_replace('/\/$/', '', $context->shop->getBaseUrl()),
            ]);
    }

    public function getModuleIsUpdatable(): bool
    {
        // check if service exist
        try {
            $module = \Module::getInstanceByName($this->moduleName);

            /** @var ModulesHelper $mboModulesHelper */
            $mboModulesHelper = $module->get('mbo.modules.helper'); // @phpstan-ignore-line
        } catch (ServiceNotFoundException $e) {
            return false;
        }

        // checking if mbo module is installed
        if (false === $this->moduleManager->isInstalledAndActive(self::MBO_MODULE_NAME)) {
            return false;
        }

        // if method exist and findVersionForUpdate returns module instance if upgrade is available otherwise null
        if (method_exists($mboModulesHelper, 'findForUpdates') && $mboModulesHelper->findForUpdates($this->moduleName)) { // @phpstan-ignore-line
            return true;
        }

        return false;
    }
}
