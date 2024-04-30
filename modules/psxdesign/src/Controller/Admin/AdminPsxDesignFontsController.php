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
use PrestaShop\Module\PsxDesign\DTO\PsxDesignFontData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;
use PrestaShop\Module\PsxDesign\Traits\UpgradeNotification\UpgradeNotificationTrait;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminPsxDesignFontsController extends FrameworkBundleAdminController
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

        $fontsProvider = $this->get('prestashop.module.psxdesign.provider.font_data_provider');
        $themeConfigurationsProvider = $this->get('prestashop.module.psxdesign.provider.theme_configuration_provider');

        return $this->render('@Modules/psxdesign/views/templates/admin/fonts/index.html.twig', [
            'fonts' => $fontsProvider->provideDefaultFonts(),
            'fontVariants' => $fontsProvider->provideFontsVariants(),
            'themeFontsDataByCategories' => $themeConfigurationsProvider->fonts->getFontsCategorizedFromThemeConfigurationAndData(),
            'fontFeatureAvailability' => $themeConfigurationsProvider->fonts->getFontFeatureAvailability(),
            'fontsByCategoriesPlaceholder' => $themeConfigurationsProvider->fonts->getFontsCategorizedPlaceholder(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function upsertFontAction(Request $request): RedirectResponse
    {
        $fonts = [];
        $previousFonts = [];
        $fontsProvider = $this->get('prestashop.module.psxdesign.provider.fonts_configuration_provider');

        foreach ($request->request as $font) {
            $fonts[] = PsxDesignFontData::createFromRequest($font);
            $previousFonts = $fontsProvider->getFontsFromThemeConfigurationAndData();
        }

        try {
            $upsertedFonts = $this->get('prestashop.module.psxdesign.handler.font_upsert_handler')->upsertFonts($fonts);
            $fontTypes = $fontsProvider->getUpdatedFontsCategoriesTitles($previousFonts, $upsertedFonts);
            $this->get('prestashop.module.psxdesign.tracker.segment')->track('Font Updated', ['font_type' => $fontTypes], $request->server);

            $this->addFlash('success', $this->trans('Changes have been saved.', 'Modules.Psxdesign.Admin'));
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getUpsertFontsErrorMessage($e));

            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getUpsertFontsErrorCode($e));
        }

        return $this->redirectToRoute('admin_fonts_index');
    }

    /**
     * @return Response
     */
    public function updateFontsStylesheetAction(): Response
    {
        $fontStylesheetUpdater = $this->get('prestashop.module.psxdesign.handler.font_stylesheet_updater');

        try {
            $fontStylesheetUpdater->updateStylesheets();
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getUpsertFontsErrorMessage($e));
            $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler')->handle($e, $this->getUpsertFontsErrorCode($e));

            return new Response('Failed to handle new stylesheets creation', Response::HTTP_BAD_REQUEST);
        }

        return new Response('New stylesheets has been created', Response::HTTP_OK);
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    private function getUpsertFontsErrorMessage(Throwable $e): string
    {
        $map = [
            PsxDesignFontsException::class => [
                PsxDesignFontsException::FAILED_TO_UPSERT_FONT => $this->trans('Failed to add or update the font.', 'Modules.Psxdesign.Admin'),
                PsxDesignFontsException::FAILED_TO_DELETE_FONT => $this->trans('Failed to delete the font.', 'Modules.Psxdesign.Admin'),
                PsxDesignFontsException::INVALID_TYPE => $this->trans('Invalid font style is provided.', 'Modules.Psxdesign.Admin'),
                PsxDesignFontsException::INVALID_WEIGHT => $this->trans('Invalid font style is provided.', 'Modules.Psxdesign.Admin'),
                PsxDesignFontsException::NO_FONT_PROVIDED => $this->trans('The font customization is only available with compatible themes.', 'Modules.Psxdesign.Admin'),
                PsxDesignFontsException::FAILED_TO_CREATE_STYLESHEET => $this->trans('Failed to generate stylesheet', 'Modules.Psxdesign.Admin'),
            ],
            PsxDesignApiException::class => [
                PsxDesignApiException::FAILED_FETCH_FONT => $this->trans('Failed to receive font from google fonts', 'Modules.Psxdesign.Admin'),
            ],
            IOException::class => $this->trans('Failed to generate stylesheet', 'Modules.Psxdesign.Admin'),
        ];

        if (isset($map[get_class($e)]) && is_array($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? $this->trans('The fonts update failed. Please try again.', 'Modules.Psxdesign.Admin');
        }

        return $map[get_class($e)] ?? $this->getFallbackErrorMessage(get_class($e), $e->getCode());
    }

    /**
     * @param Throwable $e
     *
     * @return int
     */
    private function getUpsertFontsErrorCode(Throwable $e): int
    {
        if ($e instanceof PsxDesignException) {
            return $e->getCode();
        }

        $map = [
            IOException::class => PsxDesignException::WARNING_SEVERITY,
        ];

        /* If undefined error occurred, and it is not in the list then we want to send it */
        return $map[get_class($e)] ?? PsxDesignException::WARNING_SEVERITY;
    }
}
