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

namespace PrestaShop\Module\PsxDesign\Controller\Admin;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminPsxDesignThemeUpgradeController extends FrameworkBundleAdminController
{
    public function psxDesignModuleUpgradeAction(): RedirectResponse
    {
        $response = $this->forward(
            'PrestaShopBundle\Controller\Admin\Improve\ModuleController::moduleAction',
            [
                'module_name' => 'psxdesign',
                'action' => 'upgrade',
            ]
        );

        $response = json_decode($response->getContent(), false);

        if (!isset($response)) {
            $this->addFlash(
                'error',
                $this->trans('Module upgrade failed. Please try upgrading it through module manager', 'Modules.Psxdesign.Admin')
            );

            return $this->redirectToRoute('admin_psxdesign_themes_index');
        }

        if ($response->psxdesign->status) {
            $alert = 'success';
            $message = $this->trans('Your module has been successfully upgraded!', 'Modules.Psxdesign.Admin');
        } else {
            $alert = 'error';
            $message = $response->psxdesign->msg;
        }

        $this->addFlash($alert, $message);

        return $this->redirectToRoute('admin_psxdesign_themes_index');
    }
}
