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
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
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
    public function get($serviceName) // @phpstan-ignore-line
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

    public function filter_settings_tabs_recursive(array $tabs, array $whitelist, array $blacklist = []): array
    {
        $filtered = [];

        foreach ($tabs as $tab) {
            if (in_array($tab['class_name'], $whitelist)) {
                if ($tab['class_name'] === 'AdminAdvancedParameters') {
                    $tmp = [];
                    for ($i = 0; $i < count($tab['sub_tabs']); ++$i) {
                        if (!in_array($tab['sub_tabs'][$i]['wording'], $blacklist)) {
                            array_push($tmp, $tab['sub_tabs'][$i]);
                        }
                    }
                    $tab['sub_tabs'] = $tmp;
                }
                $filtered[] = $tab;
                continue;
            }

            $filtered = array_merge($filtered, $this->filter_settings_tabs_recursive($tab['sub_tabs'], $whitelist, $blacklist));
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
        $scheme = $this->getConfiguration()->get('PS_SSL_ENABLED') ? 'https://' : 'http://';

        return $scheme . $_SERVER['HTTP_HOST'] . $router->generate($routeName);
    }

    public function indexAction(): Response
    {
        if (intval($this->getContext()->employee->id_profile) !== 1) {
            \Tools::redirectAdmin($this->getContext()->link->getAdminLink('AdminDashboard'));
        }

        $modulePsEditionBasic = $this->get('ps_edition_basic.module');

        /* ----------------------- Allow auto install account ---------------------- */
        /**
         * @var PsAccounts|null
         */
        $accountsFacade = null;
        $accountsService = null;
        $employeeAccount = null;
        $psAccountID = null;
        $userTokenRepository = null;
        try {
            $accountsInstaller = $this->get('ps_edition_basic.ps_accounts.installer');
            $accountsInstaller->install();
            $accountsFacade = $this->get('ps_edition_basic.ps_accounts.facade');
            \Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($modulePsEditionBasic->name),
            ]);
            $accountsService = $accountsFacade->getPsAccountsService();
            $employeeAccount = $accountsService->getEmployeeAccount();
            $psAccountID = ($employeeAccount ? $employeeAccount->getUid() : $accountsService->getUserUuid());
            $userTokenRepository = $this->get('PrestaShop\Module\PsAccounts\Repository\UserTokenRepository');
        } catch (\Exception $e) {
            // Todo logs ?
        }

        $jsLink = $modulePsEditionBasic->getParameter('ps_edition_basic.edition_basic_homepage_js');

        $reflection = new \ReflectionClass($this->getContext()->controller);
        $method = $reflection->getMethod('getTabs');
        $method->setAccessible(true);
        $tabs = $method->invoke($this->getContext()->controller);

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
        try {
            $smbEditionManageSubscriptionApiUrl = $this->buildAdminUrl('smb_edition_manage_subscription');
        } catch (\Exception $e) {
            $smbEditionManageSubscriptionApiUrl = '';
        }

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
                'SMB_EDITION_MANAGE_SUBSCRIPTION_API_URL' => $smbEditionManageSubscriptionApiUrl,
                'moduleName' => $modulePsEditionBasic->displayName,
                'moduleSlug' => $modulePsEditionBasic->name,
                'moduleVersion' => $modulePsEditionBasic->version,
                'moduleIsUpdatable' => $moduleService->getModuleIsUpdatable(),
                'moduleUpdateLink' => $moduleService->getUpdateLink(),
                'userToken' => $userTokenRepository ? strval($userTokenRepository->getOrRefreshToken()) : '',
                'psAccountShopID' => $accountsService && is_string($accountsService->getShopUuid()) ? $accountsService->getShopUuid() : '',
                'psAccountID' => $psAccountID ?: '',
                'shopName' => (string) $this->getConfiguration()->get('PS_SHOP_NAME', ''),
                'isShopEnabled' => (bool) $this->getConfiguration()->get('PS_SHOP_ENABLE', false),
                'psSubscriptionID' => (string) $this->getConfiguration()->get('PS_SHOP_SUBSCRIPTION_ID', ''),
                'callBack' => [
                    'isCalledBack' => (bool) $this->getConfiguration()->get('PS_IS_CALLED_BACK', false),
                ],
                'tabs' => $this->filter_settings_tabs_recursive($tabs, PS_EDITION_BASIC_SETTINGS_WHITE_LIST, PS_EDITION_BASIC_SETTINGS_BLACK_LIST),
                'locale' => $this->getContext()->language->iso_code,
                'shopCountry' => $shopCountry,
                'modulesTabs' => $this->filter_modules_tabs_recursive($tabs, array_merge(PS_EDITION_BASIC_SETTINGS_WHITE_LIST, PS_EDITION_BASIC_MENU_WHITE_LIST)),
            ]),
        ]);
    }
}
