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

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Controller;

use PrestaShop\Module\PsEditionBasic\Service\ModuleService;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class AdminPsEditionBasicController extends FrameworkBundleAdminController
{
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

    protected function layoutTitle(): string
    {
        return $this->trans('Home', 'Modules.Editionbasic.Admin');
    }

    public function filter_settings_tabs_recursive(array $tabs, array $whitelist): array
    {
        $filtered = [];

        foreach ($tabs as $tab) {
            if (in_array($tab['class_name'], $whitelist)) {
                $filtered[] = $tab;
                continue;
            }

            $filtered = array_merge($filtered, $this->filter_settings_tabs_recursive($tab['sub_tabs'], $whitelist));
        }

        return array_values($this->reorganize_tabs($filtered, $whitelist));
    }

    public function filter_modules_tabs_recursive(array $tabs, array $whitelist): array
    {
        $filtered = [];

        foreach ($tabs as $key => $tab) {
            if (isset($tab['sub_tabs']) && is_array($tab['sub_tabs'])) {
                foreach ($tab['sub_tabs'] as $subTab) {
                    array_push($filtered, $subTab);
                }
            }
        }

        foreach ($filtered as $key => $tab) {
            if (in_array($tab['class_name'], $whitelist) || $tab['active'] == 0 || $tab['class_name'] === 'AdminPsEditionBasicSettingsController') {
                unset($filtered[$key]);
            } else {
                if (isset($tab['sub_tabs']) && is_array($tab['sub_tabs'])) {
                    $this->filter_modules_tabs_recursive($tab['sub_tabs'], $whitelist);
                }
            }
        }

        return array_values($filtered);
    }

    public function reorganize_tabs(array $tabs, array $whitelist): array
    {
        $reorganized = [];

        foreach ($whitelist as $tabClassName) {
            foreach ($tabs as $item) {
                if ($item['class_name'] === $tabClassName) {
                    $reorganized[] = $item;
                    break;
                }
            }
        }

        return $reorganized;
    }

    private function buildAdminUrl(string $routeName): string
    {
        $router = $this->get('router');
        $scheme = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'https://') === 0 ? 'https://' : 'http://';

        return $scheme . $_SERVER['HTTP_HOST'] . $router->generate($routeName);
    }

    public function indexAction(): Response
    {
        $modulePsEditionBasic = $this->get('ps_edition_basic.module');

        $jsLink = $modulePsEditionBasic->getParameter('ps_edition_basic.edition_basic_homepage_js');

        $psAccountService = $this->get('PrestaShop\Module\PsAccounts\Service\PsAccountsService');
        $userTokenRepository = $this->get('PrestaShop\Module\PsAccounts\Repository\UserTokenRepository');

        $reflection = new \ReflectionClass($this->getContext()->controller);
        $method = $reflection->getMethod('getTabs');
        $method->setAccessible(true);
        $tabs = $method->invoke($this->getContext()->controller);

        $employeeAccount = $psAccountService->getEmployeeAccount();
        $psAccountID = ($employeeAccount ? $employeeAccount->getUid() : $psAccountService->getUserUuid());

        /* ----------------------- Allow auto install account ---------------------- */
        $accountsFacade = null;
        $accountsService = null;
        try {
            $accountsInstaller = $this->get('ps_edition_basic.ps_accounts.installer');
            $accountsInstaller->install();
            $accountsFacade = $this->get('ps_edition_basic.ps_accounts.facade');
            \Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($modulePsEditionBasic->name),
            ]);
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\Exception $e) {
            // Todo logs ?
        }

        /**
         * @var string|string[]
         */
        $shopCountry = $this->getContext()->country->iso_code;
        if (is_array($shopCountry)) { // Country might be an array
            $shopCountry = $shopCountry[array_key_first($shopCountry)] ?? '';
        }
        $shopCountry = strtolower($shopCountry);

        /** @var ModuleService $moduleService */
        $moduleService = $this->get('PrestaShop\Module\PsEditionBasic\Service\ModuleService');

        $callBackModuleUrl = $this->buildAdminUrl('ps_edition_basic_call_back');
        $setupGuideApiUrl = $this->buildAdminUrl('ps_edition_basic_setup_guide_api_index');
        $setupGuideApiUrlEdit = $this->buildAdminUrl('ps_edition_basic_setup_guide_api_edit');
        $setupGuideApiUrlModalHidden = $this->buildAdminUrl('ps_edition_basic_setup_guide_api_modal_hidden');
        $cacheClearApiUrl = $this->buildAdminUrl('ps_edition_basic_clean_mbo_cache');
        $getSubscriptionApiUrl = $this->buildAdminUrl('ps_edition_basic_get_subscription');
        $psAcademyApiUrl = $this->buildAdminUrl('ps_edition_basic_ps_academy');

        return $this->render('@Modules/ps_edition_basic/views/templates/admin/homepage.html.twig', [
            'layoutTitle' => $this->layoutTitle(),
            'urlAccountsCdn' => $accountsService ? $accountsService->getAccountsCdn() : '',
            'enableSidebar' => true,
            'jsLink' => $jsLink,
            'jsContext' => json_encode([
                'CALL_BACK_MODULE_URL' => $callBackModuleUrl,
                'SETUP_GUIDE_API_URL' => $setupGuideApiUrl,
                'SETUP_GUIDE_API_URL_EDIT' => $setupGuideApiUrlEdit,
                'SETUP_GUIDE_API_URL_MODAL_HIDDEN' => $setupGuideApiUrlModalHidden,
                'CACHE_CLEAR_API_URL' => $cacheClearApiUrl,
                'GET_SUBSCRIPTION_API_URL' => $getSubscriptionApiUrl,
                'PS_EDITION_BASIC_PS_ACADEMY_API_URL' => $psAcademyApiUrl,
                'moduleName' => $modulePsEditionBasic->displayName,
                'moduleSlug' => $modulePsEditionBasic->name,
                'moduleVersion' => $modulePsEditionBasic->version,
                'moduleIsUpdatable' => $moduleService->getModuleIsUpdatable(),
                'moduleUpdateLink' => $moduleService->getUpdateLink(),
                'userToken' => strval($userTokenRepository->getOrRefreshToken()) ?: '',
                'psAccountShopID' => $psAccountService->getShopUuid() ?: '',
                'psAccountID' => $psAccountID ?: '',
                'shopName' => (string) $this->configuration->get('PS_SHOP_NAME', ''),
                'isShopEnabled' => (bool) $this->configuration->get('PS_SHOP_ENABLE', false),
                'psSubscriptionID' => (string) $this->configuration->get('PS_SHOP_SUBSCRIPTION_ID', ''),
                'callBack' => [
                    'isCalledBack' => (bool) $this->configuration->get('PS_IS_CALLED_BACK', false),
                    'apiResponse' => (array) json_decode($this->configuration->get('PS_CALLBACK_API_RESPONSE', '[]'), true),
                ],
                'tabs' => $this->filter_settings_tabs_recursive($tabs, PS_EDITION_BASIC_SETTINGS_WHITE_LIST),
                'locale' => $this->getContext()->language->iso_code,
                'shopCountry' => $shopCountry,
                'modulesTabs' => $this->filter_modules_tabs_recursive($tabs, array_merge(PS_EDITION_BASIC_SETTINGS_WHITE_LIST, PS_EDITION_BASIC_MENU_WHITE_LIST)),
            ]),
        ]);
    }
}
