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

use Cache;
use ErrorException;
use Exception;
use Hook;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignThemeException;
use PrestaShop\Module\PsxDesign\Handler\ThemeUploader;
use PrestaShop\Module\PsxDesign\Traits\UpgradeNotification\UpgradeNotificationTrait;
use PrestaShop\PrestaShop\Core\Addon\Theme\Exception\ThemeUploadException;
use PrestaShop\PrestaShop\Core\Addon\Theme\Theme;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeRepository;
use PrestaShop\PrestaShop\Core\Domain\Theme\Exception\ImportedThemeAlreadyExistsException;
use PrestaShop\PrestaShop\Core\Domain\Theme\Exception\NotSupportedThemeImportSourceException;
use PrestaShop\PrestaShop\Core\Domain\Theme\Exception\ThemeConstraintException;
use PrestaShop\PrestaShop\Core\Domain\Theme\ValueObject\ThemeImportSource;
use PrestaShopBundle\Controller\Admin\Improve\Design\ThemeController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\DemoRestricted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tools;

class AdminPsxDesignThemeGeneralController extends ThemeController
{
    use UpgradeNotificationTrait;

    /**
     * Show main themes page.
     *
     * @AdminSecurity(
     *     "is_granted('read', request.get('_legacy_controller'))",
     *     message="You do not have permission to edit this."
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        try {
            $this->showUpgradeNotification();
        } catch (Exception $exception) {
            // Avoid fatal errors on ServiceNotFoundException
        }

        $themeProvider = $this->get('prestashop.module.psxdesign.provider.theme_attributes_provider');

        return $this->render('@Modules/psxdesign/views/templates/admin/themes/index.html.twig', [
            'importThemeUrl' => $this->generateUrl('admin_psxdesign_theme_import_action'),
            'baseShopUrl' => $this->get('prestashop.adapter.shop.url.base_url_provider')->getUrl(),
            'currentlyUsedTheme' => $themeProvider->getCurrentThemeAttributes(),
            'notUsedThemes' => $themeProvider->getNotUsedThemesAttributes(),
            'themesZipFiles' => $this->get('prestashop.core.form.choice_provider.theme_zip')->getChoices(),
            'isSingleShopContext' => $this->get('prestashop.adapter.shop.context')->isSingleShopContext(),
            'shopName' => $this->get('prestashop.adapter.shop.context')->getShopName(),
        ]);
    }

    /**
     * @AdminSecurity(
     * "is_granted('create', request.get('_legacy_controller'))",
     * redirectRoute="admin_psxdesign_themes_index",
     * message="You do not have permission to add this."
     * )
     * @DemoRestricted(redirectRoute="admin_psxdesign_themes_index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importThemeAction(Request $request): Response
    {
        /** @var ThemeUploader $themeUploader */
        $themeUploader = $this->get('prestashop.module.psxdesign.handler.theme_uploader');

        try {
            $importedTheme = $themeUploader->upload($this->buildImportSource($request));
            $template = $this->render('@Modules/psxdesign/views/templates/admin/themes/Blocks/Alerts/theme_enable_alert.html.twig', [
                'importedThemeName' => $importedTheme->getName(),
                'importedThemeDisplayName' => $importedTheme->get('display_name'),
                'isSingleShopContext' => $this->get('prestashop.adapter.shop.context')->isSingleShopContext(),
            ]);

            // File uploaded we want to remove this variable as getting service with file request fails
            $_FILES = [];

            $this->get('prestashop.module.psxdesign.tracker.segment')->track('Theme Installed', ['theme_name' => $importedTheme->getName(), 'entry_point' => $request->get('action')], $request->server);
            $this->addFlash('psxdesign-success', $template->getContent());

            return $this->json(['message' => $this->trans('Theme imported successfully', 'Modules.Psxdesign.Admin')]);
        } catch (Throwable $e) {
            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getImportThemeErrorCode($e));

            return $this->json(['message' => $this->getImportThemeErrorMessage($e)], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Enable selected theme.
     *
     * @DemoRestricted(redirectRoute="admin_psxdesign_themes_index")
     *
     * @param string $themeName
     *
     * @return RedirectResponse
     */
    public function enableAction($themeName): RedirectResponse
    {
        // TODO : Contribution to Core
        // This part prevents PsxDesign from being disabled if it is set to a theme. We can remove this code when the Core has done an update on this part.
        // current discussion about it on the Core repo : https://github.com/PrestaShop/PrestaShop/discussions/35146
        $themeConfigurationJsonPath = _PS_CONFIG_DIR_ . 'themes' . DIRECTORY_SEPARATOR . $this->getContext()->shop->theme->get('name') . DIRECTORY_SEPARATOR . 'shop' . $this->getContext()->shop->id . '.json';

        if (file_exists($themeConfigurationJsonPath)) {
            $themeConfiguration = json_decode(Tools::file_get_contents($themeConfigurationJsonPath), true);

            if (isset($themeConfiguration['dependencies']['modules']) && is_array($themeConfiguration['dependencies']['modules'])) {
                $themeConfiguration['dependencies']['modules'] = array_diff($themeConfiguration['dependencies']['modules'], ['psxdesign']);

                file_put_contents($themeConfigurationJsonPath, json_encode($themeConfiguration, JSON_PRETTY_PRINT));
            }
        }

        parent::enableAction($themeName);

        /** @var ThemeRepository $themeRepository */
        $themeRepository = $this->get('prestashop.core.addon.theme.repository');

        /** @var Theme $theme */
        $theme = $themeRepository->getInstanceByName($themeName);

        $flashBag = $this->container->get('session')->getFlashBag();

        if ($flashBag->has('success')) {
            $flashBag->set('success', $this->trans('The theme %theme% has been set as active theme.', 'Modules.Psxdesign.Admin', ['%theme%' => $theme->get('display_name')]));
        }

        // invalidate hook cache to refresh activated theme hooks
        Cache::clean(Hook::MODULE_LIST_BY_HOOK_KEY . '*');

        $this->forward(
            'PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignFontsController::updateFontsStylesheetAction'
        );

        $this->forward(
            'PrestaShop\Module\PsxDesign\Controller\Admin\AdminPsxDesignColorsController::updateColorsStylesheetsAction'
        );

        $this->get('prestashop.module.psxdesign.tracker.segment')->track('Theme Activated', ['theme_name' => $themeName, 'native_edition_theme' => $this->isNativeEditionTheme($themeName)]);

        return $this->redirectToRoute('admin_psxdesign_themes_index');
    }

    /**
     * Delete selected theme.
     *
     * @AdminSecurity(
     *     "is_granted('delete', request.get('_legacy_controller'))",
     *     redirectRoute="admin_psxdesign_themes_index",
     *     message="You do not have permission to delete this."
     * )
     * @DemoRestricted(redirectRoute="admin_psxdesign_themes_index")
     *
     * @param string $themeName
     *
     * @return RedirectResponse
     */
    public function deleteAction($themeName): RedirectResponse
    {
        /** @var ThemeRepository $themeRepository */
        $themeRepository = $this->get('prestashop.core.addon.theme.repository');

        /** @var Theme $theme */
        $theme = $themeRepository->getInstanceByName($themeName);
        $themeDisplayName = $theme->get('display_name');

        if ($themeName === 'classic') {
            $this->addFlash('error', $this->trans('The theme %theme% cannot be deleted because it\'s the default Prestashop theme.', 'Modules.Psxdesign.Admin', ['%theme%' => $themeDisplayName]));

            return $this->redirectToRoute('admin_themes_index');
        }

        parent::deleteAction($themeName);

        $flashBag = $this->container->get('session')->getFlashBag();

        if ($flashBag->has('success')) {
            $flashBag->set('success', $this->trans('The theme %themeName% has been deleted.', 'Modules.Psxdesign.Admin', ['%themeName%' => $themeDisplayName]));
        }

        $this->get('prestashop.module.psxdesign.tracker.segment')->track('Theme Deleted', ['theme_name' => $themeName, 'native_edition_theme' => $this->isNativeEditionTheme($themeName)]);

        return $this->redirectToRoute('admin_psxdesign_themes_index');
    }

    /**
     * @param Request $request
     *
     * @return ThemeImportSource
     *
     * @throws NotSupportedThemeImportSourceException
     */
    private function buildImportSource(Request $request): ThemeImportSource
    {
        $action = $request->get('action');
        $path = null;

        if ($action === null && $request->getContent() !== '') {
            $data = json_decode($request->getContent(), false);
            $action = $data->action;
            $path = $data->path;
        }

        if ('import-from-computer' === $action) {
            return ThemeImportSource::fromArchive($request->files->get('file'));
        }

        if ('import-from-web' === $action) {
            return ThemeImportSource::fromWeb($path);
        }

        if ('import-from-ftp' === $action) {
            return ThemeImportSource::fromFtp($path);
        }

        throw new NotSupportedThemeImportSourceException('Invalid import source');
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    private function getImportThemeErrorMessage(Throwable $e): string
    {
        $map = [
            NotSupportedThemeImportSourceException::class => $this->trans('Please select correct theme\'s import source.', 'Modules.Psxdesign.Admin'),
            PsxDesignThemeException::class => [
                PsxDesignThemeException::FAILED_FIND_IMPORTED_THEME => $this->trans('Failed to find imported theme.', 'Modules.Psxdesign.Admin'),
            ],
            ErrorException::class => $this->trans('File does not exist or bad type uploaded.', 'Modules.Psxdesign.Admin'),
            ImportedThemeAlreadyExistsException::class => $this->trans(
                'There is already a theme %theme_name% in your themes folder. Remove it if you want to continue.',
                'Modules.Psxdesign.Admin',
                [
                    '%theme_name%' => $e instanceof ImportedThemeAlreadyExistsException ? $e->getThemeName()->getValue() : '',
                ]
            ),
            ThemeUploadException::class => [
                ThemeUploadException::FILE_SIZE_EXCEEDED_ERROR => $this->trans('Allowed file size exceeded for uploaded theme.', 'Modules.Psxdesign.Admin'),
                ThemeUploadException::INVALID_MIME_TYPE => $this->trans('The file type is invalid. Allowed file type is .zip.', 'Modules.Psxdesign.Admin'),
                ThemeUploadException::UNKNOWN_ERROR => $this->trans('Unknown error occurred. Please try again.', 'Modules.Psxdesign.Admin'),
            ],
            ThemeConstraintException::class => [
                ThemeConstraintException::RESTRICTED_ONLY_FOR_SINGLE_SHOP => $this->trans(
                    'Themes can only be imported in single store context.', 'Modules.Psxdesign.Admin'
                ),
                ThemeConstraintException::MISSING_CONFIGURATION_FILE => $this->trans(
                    'Missing configuration file', 'Modules.Psxdesign.Admin'
                ),
                ThemeConstraintException::INVALID_CONFIGURATION => $this->trans(
                    'Invalid configuration', 'Modules.Psxdesign.Admin'
                ),
                ThemeConstraintException::INVALID_DATA => $this->trans(
                    'Invalid data', 'Modules.Psxdesign.Admin'
                ),
            ],
        ];

        $map[get_class($e)] = $map[get_class($e)] ?? null;

        if (is_array($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? $this->trans('The import of your theme failed. Please try again.', 'Modules.Psxdesign.Admin');
        }

        return $map[get_class($e)] ?? $this->trans('The import of your theme failed. Please try again.', 'Modules.Psxdesign.Admin');
    }

    /**
     * @param Throwable $e
     *
     * @return int
     */
    private function getImportThemeErrorCode(Throwable $e): int
    {
        if ($e instanceof PsxDesignException) {
            return $e->getCode();
        }

        $map = [
            NotSupportedThemeImportSourceException::class => PsxDesignException::INFO_SEVERITY,
            ErrorException::class => PsxDesignException::INFO_SEVERITY,
            ImportedThemeAlreadyExistsException::class => PsxDesignException::INFO_SEVERITY,
            ThemeUploadException::class => [
                ThemeUploadException::FILE_SIZE_EXCEEDED_ERROR => PsxDesignException::INFO_SEVERITY,
                ThemeUploadException::INVALID_MIME_TYPE => PsxDesignException::INFO_SEVERITY,
                ThemeUploadException::UNKNOWN_ERROR => PsxDesignException::WARNING_SEVERITY,
            ],
            ThemeConstraintException::class => [
                ThemeConstraintException::RESTRICTED_ONLY_FOR_SINGLE_SHOP => PsxDesignException::INFO_SEVERITY,
                ThemeConstraintException::MISSING_CONFIGURATION_FILE => PsxDesignException::INFO_SEVERITY,
                ThemeConstraintException::INVALID_CONFIGURATION => PsxDesignException::INFO_SEVERITY,
                ThemeConstraintException::INVALID_DATA => PsxDesignException::INFO_SEVERITY,
            ],
        ];
        $map[get_class($e)] = $map[get_class($e)] ?? null;

        /* If undefined error occurred, and it is not in the list then we want to send it */
        if (is_array($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? PsxDesignException::WARNING_SEVERITY;
        }

        return $map[get_class($e)] ?? PsxDesignException::WARNING_SEVERITY;
    }

    /**
     * @param string $themeName
     *
     * @return bool
     */
    private function isNativeEditionTheme(string $themeName): bool
    {
        $nativeThemeNamesMap = ['classic'];

        return in_array($themeName, $nativeThemeNamesMap);
    }
}
