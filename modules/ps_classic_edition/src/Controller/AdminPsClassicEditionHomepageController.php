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

namespace PrestaShop\Module\PsClassicEdition\Controller;

use PrestaShop\Module\PsClassicEdition\Helper\PsAccountHelper;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminPsClassicEditionHomepageController extends PrestaShopAdminController
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            'psAccountService' => '?PrestaShop\Module\PsAccounts\Service\PsAccountsService',
        ];
    }

    public function indexAction(
        Request $request,
        PsAccountHelper $psAccountHelper,
        #[Autowire(service: 'ps_classic_edition.module')]
        \ps_classic_edition $modulePsClassicEdition,
        LegacyContext $legacyContext,
    ): Response {
        if (!$this->getEmployeeContext()->isSuperAdmin()) {
            return $this->redirect($legacyContext->getContext()->link->getAdminLink('AdminDashboard'));
        }

        /**
         * @var string|string[]
         */
        $shopCountry = $this->getCountryContext()->getIsoCode();
        if (is_array($shopCountry)) { // Country might be an array
            $shopCountry = $shopCountry[array_key_first($shopCountry)] ?? '';
        }
        $shopCountry = strtolower($shopCountry);

        $setupGuideApiUrl = $this->generateUrl('ps_classic_edition_setup_guide_api_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $setupGuideApiUrlEdit = $this->generateUrl('ps_classic_edition_setup_guide_api_edit', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $setupGuideApiUrlModalHidden = $this->generateUrl('ps_classic_edition_setup_guide_api_modal_hidden', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $psAcademyApiUrl = $this->generateUrl('ps_classic_edition_ps_academy', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $psAccountsSettings = $psAccountHelper->loadAccountSettings();

        return $this->render('@Modules/ps_classic_edition/views/templates/admin/homepage.html.twig', [
            'layoutTitle' => $this->trans('Care Center', [], 'Modules.Classicedition.Admin'),
            'urlAccountsCdn' => $psAccountsSettings['urlAccountsCdn'],
            'enableSidebar' => true,
            'jsContext' => json_encode([
                'SETUP_GUIDE_API_URL' => $setupGuideApiUrl,
                'SETUP_GUIDE_API_URL_EDIT' => $setupGuideApiUrlEdit,
                'SETUP_GUIDE_API_URL_MODAL_HIDDEN' => $setupGuideApiUrlModalHidden,
                'SETUP_GUIDE_MODAL_IS_HIDDEN' => (bool) $this->getConfiguration()->get('PS_SETUP_GUIDE_MODAL_IS_HIDDEN'),
                'PS_CLASSIC_EDITION_PS_ACADEMY_API_URL' => $psAcademyApiUrl,
                'moduleName' => $modulePsClassicEdition->displayName,
                'moduleSlug' => $modulePsClassicEdition->name,
                'moduleVersion' => $modulePsClassicEdition->version,
                'userToken' => $psAccountsSettings['accountUserToken'],
                'psAccountShopID' => $psAccountsSettings['psShopID'],
                'psAccountID' => $psAccountsSettings['psAccountID'],
                'shopName' => (string) $this->getConfiguration()->get('PS_SHOP_NAME'),
                'callBack' => [
                    'isCalledBack' => (bool) $this->getConfiguration()->get('PS_IS_CALLED_BACK'),
                ],
                'locale' => $this->getLanguageContext()->getIsoCode(),
                'shopCountry' => $shopCountry,
                'baseUrl' => $request->getBaseUrl(),
            ]),
        ]);
    }
}
