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

namespace PrestaShop\Module\PsxDesign\Traits\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Media;
use PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignColorsController;
use PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignFontsController;
use PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignLogosController;
use PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignThemeGeneralController;
use PrestaShop\Module\PsxDesign\Service\ModuleUpgradeService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

trait UseDisplayBackOfficeHeader
{
    public $psxdesignControllers = [
        AdminPsxDesignLogosController::class,
        AdminPsxDesignThemeGeneralController::class,
        AdminPsxDesignColorsController::class,
        AdminPsxDesignFontsController::class,
    ];

    /**
     * @return string
     */
    public function hookDisplayBackOfficeHeader(): string
    {
        $jsModule = '';
        $upgradeService = null;
        /* @var ModuleUpgradeService $upgradeService */
        try {
            $upgradeService = $this->get('prestashop.module.psxdesign.service.module_upgrade_service');
        } catch (ServiceNotFoundException $e) {
            // don't want to throw error from cache issue cause it's solved in PS version 8.0.3.
        }

        if ($upgradeService && $upgradeService->isUpgradeAvailable()) {
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/dashboard-notification.css');

            $this->context->smarty->assign([
                'src' => $this->getPathUri() . 'views/js/upgrade-notification.js',
            ]);

            $jsModule .= $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/displayModuleTag.tpl');

            /** @var EngineInterface $twig */
            $twig = $this->get('twig');

            Media::addJsDef([
                'psxDesignUpdateNotification' => $twig->render('@Modules/psxdesign/views/templates/components/dashboard_notification.html.twig'),
            ]);
        }

        /** @var RequestStack $requestStack */
        $requestStack = $this->get('request_stack');
        $currentRequest = $requestStack->getCurrentRequest();

        if (empty($currentRequest)) {
            return $jsModule;
        }

        $currentController = $currentRequest->get('controller_name');

        if (in_array($currentController, $this->psxdesignControllers)) {
            $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin/index.css');
            $router = $this->get('prestashop.router');

            Media::addJsDef([
                'getUuidAjaxUrl' => $router ? $router->generate('ajax_get_admin_user_id', [], UrlGeneratorInterface::ABSOLUTE_URL) : '',
            ]);
        }

        $src = '';

        switch ($currentController) {
            case AdminPsxDesignThemeGeneralController::class:
                $src = $this->getPathUri() . 'views/js/index-themes.js';
                Media::addJsDef([
                    'importThemeLink' => $this->context->link->getAdminLink('AdminPsxDesignThemeGeneral', true, ['route' => 'admin_psxdesign_theme_import_action']),
                ]);
                break;
            case AdminPsxDesignLogosController::class:
                $src = $this->getPathUri() . 'views/js/index-logos.js';
                Media::addJsDef([
                    'psxDesignSvgAlertMessage' => $this->getTranslator()->trans(
                        'You are about to import a logo in svg format. This format being incompatible with the email and invoice logos, these will retain the current logo.',
                        [],
                        'Modules.Psxdesign.Admin'
                    ),
                ]);
                break;
            case AdminPsxDesignColorsController::class:
                $src = $this->getPathUri() . 'views/js/index-colors.js';
                break;
            case AdminPsxDesignFontsController::class:
                $src = $this->getPathUri() . 'views/js/index-fonts.js';
                break;
            default:
        }

        if (!empty($src)) {
            $this->context->smarty->assign([
                'src' => $src,
            ]);

            $jsModule .= $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/hook/displayModuleTag.tpl');
        }

        return $jsModule;
    }
}
