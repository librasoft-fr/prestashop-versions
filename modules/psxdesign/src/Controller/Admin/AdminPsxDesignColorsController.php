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

use Exception;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignCompilerException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Traits\UpgradeNotification\UpgradeNotificationTrait;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\DemoRestricted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminPsxDesignColorsController extends FrameworkBundleAdminController
{
    use UpgradeNotificationTrait;

    /**
     * Show colors page.
     *
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller'))",
     *     message="You do not have permission to edit this."
     * )
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        try {
            $this->showUpgradeNotification();
        } catch (Exception $exception) {
            // Avoid fatal errors on ServiceNotFoundException
        }

        $themeConfigurationsProvider = $this->get('prestashop.module.psxdesign.provider.theme_configuration_provider');

        return $this->render('@Modules/psxdesign/views/templates/admin/colors/index.html.twig', [
            'isSingleShopContext' => $this->get('prestashop.adapter.shop.context')->isSingleShopContext(),
            'colors' => $themeConfigurationsProvider->colors->getCurrentThemeCategorizedColorsList(),
            'isColorFeatureEnabled' => $themeConfigurationsProvider->colors->getColorFeatureAvailability(),
            'colorsPlaceholder' => $themeConfigurationsProvider->colors->getColorsCategorizedPlaceholder(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function upsertColorPaletteAction(Request $request): RedirectResponse
    {
        $colors = [];
        $previousColors = [];
        $colorsProvider = $this->get('prestashop.module.psxdesign.provider.colors_configuration_provider');

        foreach ($request->request->all() as $color) {
            $colors[] = PsxDesignColorData::createFromRequest($color);
            $previousColors = $colorsProvider->getCurrentThemeColorList();
        }

        try {
            $upsertedColors = $this->get('prestashop.module.psxdesign.handler.color_palette_upsert_handler')->upsertColors($colors);
            $updatedElements = $colorsProvider->getUpdatedColorCategoriesTitles($previousColors, $upsertedColors);
            $this->get('prestashop.module.psxdesign.tracker.segment')->track('Color Updated', ['element' => $updatedElements], $request->server);
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getUpsertColorPaletteErrorMessage($e));
            $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler')->handle($e, $this->getUpsertColorPaletteErrorCode($e));

            return $this->redirectToRoute('admin_colors_index');
        }

        $this->addFlash('success', $this->trans('Changes have been saved.', 'Modules.Psxdesign.Admin'));

        return $this->redirectToRoute('admin_colors_index');
    }

    /**
     * @return Response
     */
    public function updateColorsStylesheetsAction(): Response
    {
        $colorsStylesheetUpdater = $this->get('prestashop.module.psxdesign.handler.color_stylesheet_updater');

        try {
            $colorsStylesheetUpdater->updateStylesheets();
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getUpsertColorPaletteErrorMessage($e));
            $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler')->handle($e, $this->getUpsertColorPaletteErrorCode($e));

            return new Response('Failed to handle new stylesheets creation', Response::HTTP_BAD_REQUEST);
        }

        return new Response('New stylesheets has been created', Response::HTTP_OK);
    }

    /**
     * Enable selected theme.
     *
     * @AdminSecurity(
     *     "is_granted('update', request.get('_legacy_controller'))",
     *     redirectRoute="admin_colors_index",
     *     message="You do not have permission to edit this."
     * )
     * @DemoRestricted(redirectRoute="admin_colors_index")
     *
     * @param string $themeName
     *
     * @return RedirectResponse
     */
    public function forwardThemeEnableAction(string $themeName): RedirectResponse
    {
        $theme = $this->get('prestashop.core.addon.theme.repository');

        try {
            $theme = $theme->getInstanceByName($themeName);
        } catch (\PrestaShopException $e) {
            $this->addFlash('error', $this->trans('Classic theme is not installed.', 'Modules.Psxdesign.Admin'));

            return $this->redirectToRoute('admin_colors_index');
        }

        $this->forward(
            'PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignThemeGeneralController::enableAction',
            [
                'themeName' => $themeName,
            ]
        );

        $flashBag = $this->container->get('session')->getFlashBag();

        if ($flashBag->has('success')) {
            $flashBag->set('success', $this->trans('The theme %theme% has been set as active theme.', 'Modules.Psxdesign.Admin', ['%theme%' => $theme->get('display_name')]));

            return $this->redirectToRoute('admin_colors_index');
        }

        $this->addFlash('error', $this->trans('Enabling theme failed. Please use "Theme" tab to enable theme.', 'Modules.Psxdesign.Admin'));

        return $this->redirectToRoute('admin_colors_index');
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    private function getUpsertColorPaletteErrorMessage(Throwable $e): string
    {
        $map = [
            PsxDesignColorsException::class => [
                PsxDesignColorsException::EMPTY_COLOR_PALETTE_NAME => $this->trans('The name of the color palette can\'t be empty.', 'Modules.Psxdesign.Admin'),
                PsxDesignColorsException::FAILED_TO_UPDATE_COLOR_PALETTE => $this->trans('Connection with database to update new color palette failed. Please try again.', 'Modules.Psxdesign.Admin'),
                PsxDesignColorsException::FAILED_TO_CREATE_STYLESHEET => $this->trans('Creation of new color styles failed.', 'Modules.Psxdesign.Admin'),
            ],
            PsxDesignCompilerException::class => [
                PsxDesignCompilerException::FAILED_TO_OVERWRITE_VARIABLES_SCSS => $this->trans('File in module does not exist or not writable. Please check user permissions in server', 'Modules.Psxdesign.Admin'),
                PsxDesignCompilerException::FAILED_COMPILING => $this->trans('Process of creating new styles file failed. Please try again.', 'Modules.Psxdesign.Admin'),
                PsxDesignCompilerException::FAILED_TO_OVERWRITE => $this->trans('Replacing new theme styles file failed. Please try again. ', 'Modules.Psxdesign.Admin'),
            ],
        ];

        if (isset($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? $this->trans('The color palette update failed. Please try again.', 'Modules.Psxdesign.Admin');
        }

        return $this->getFallbackErrorMessage(get_class($e), $e->getCode());
    }

    /**
     * @param Throwable $e
     *
     * @return int
     */
    private function getUpsertColorPaletteErrorCode(Throwable $e): int
    {
        if ($e instanceof PsxDesignException) {
            return $e->getCode();
        }

        //Undefined errors we want to send
        return PsxDesignException::WARNING_SEVERITY;
    }
}
