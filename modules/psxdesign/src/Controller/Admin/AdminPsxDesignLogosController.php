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
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoData;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoTextData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignTextToImageConvertException;
use PrestaShop\Module\PsxDesign\Provider\FontDataProvider;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignLogoRepository;
use PrestaShop\Module\PsxDesign\Traits\UpgradeNotification\UpgradeNotificationTrait;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoDestination;
use PrestaShop\PrestaShop\Core\Domain\Exception\FileUploadException;
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\NotSupportedFaviconExtensionException;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\NotSupportedLogoImageExtensionException;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\NotSupportedMailAndInvoiceImageExtensionException;
use PrestaShop\PrestaShop\Core\Domain\Shop\Query\GetLogosPaths;
use PrestaShop\PrestaShop\Core\Domain\Shop\QueryResult\LogosPaths;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminPsxDesignLogosController extends FrameworkBundleAdminController
{
    use UpgradeNotificationTrait;

    private const UPLOAD_LOGO_TYPE_IMAGE = 'image';
    private const UPLOAD_LOGO_TYPE_TEXT = 'text';
    private const HEADER = 'header';

    /**
     * Show logos page.
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

        /** @var LogosPaths $logoProvider */
        $logoProvider = $this->getQueryBus()->handle(new GetLogosPaths());
        $shopLogoSettings = new ShopLogoSettings();
        $fontsProvider = $this->get('prestashop.module.psxdesign.provider.font_data_provider');

        //added random number at each path on every page load as sometimes heavy caching, caches previous images
        $faviconPath = $logoProvider->getFaviconPath() . '?' . random_int(1, 100);
        $invoiceLogoPath = $logoProvider->getInvoiceLogoPath() . '?' . random_int(1, 100);
        $mailLogoPath = $logoProvider->getMailLogoPath() . '?' . random_int(1, 100);
        $headerLogoPath = $logoProvider->getHeaderLogoPath() . '?' . random_int(1, 100);

        return $this->render('@Modules/psxdesign/views/templates/admin/logos/index.html.twig', [
            'shopName' => $this->get('prestashop.adapter.shop.context')->getShopName(),
            'isSingleShopContext' => $this->get('prestashop.adapter.shop.context')->isSingleShopContext(),
            'faviconPath' => $faviconPath,
            'invoiceLogoPath' => $invoiceLogoPath,
            'mailLogoPath' => $mailLogoPath,
            'headerLogoPath' => $headerLogoPath,
            'baseShopUrl' => $this->get('prestashop.adapter.shop.url.base_url_provider')->getUrl(),
            'headerFormats' => $shopLogoSettings->getLogoImageExtensionsWithDot(),
            'otherFormats' => $shopLogoSettings->getLogoImageExtensionsWithDot('PS_LOGO_MAIL'),
            'iconFormat' => $shopLogoSettings->getIconImageExtensionWithDot(),
            'logos' => $this->getLogosData(),
            'fonts' => $fontsProvider->provideDefaultFonts(),
            'fontVariants' => $fontsProvider->provideFontsVariants(),
        ]);
    }

    /**
     * @throws PsxDesignLogoImportException
     */
    public function importLogoImageAction(Request $request): Response
    {
        $logoUploadHandler = $this->get('prestashop.module.psxdesign.handler.logo_image_uploader');

        try {
            $logoData = PsxDesignLogoData::createFromRequest($request);
            $destination = $logoUploadHandler->uploadLogo($logoData, self::UPLOAD_LOGO_TYPE_IMAGE);
            $this->addFlash('success', $this->trans('The %destination% logo has been added.', 'Modules.Psxdesign.Admin', ['%destination%' => $this->translateDestination($destination)]));

            // After logo upload temporary file is deleted.
            // Kernel fails to load tmp file because file do not exist.
            // Error is thrown if files variable is not reseted
            // Suggestion to do it found on https://github.com/laravel/framework/issues/12350
            $_FILES = [];

            $this->get('prestashop.module.psxdesign.tracker.segment')
                ->track(
                    'Logo Uploaded', ['logo_type' => self::UPLOAD_LOGO_TYPE_IMAGE, 'logo_location' => $destination], $request->server);
        } catch (Throwable $e) {
            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getErrorCode($e));

            $this->addFlash('error', $this->getMessage($e, $request->request->get('logo_for')));
        }

        return $this->redirectToRoute('admin_logos_index');
    }

    public function applyHeaderLogoAction(Request $request): Response
    {
        if (!($request->request->get('use_same_as_header'))) {
            $this->addFlash('error', $this->trans('Use same header logo is unavailable.', 'Modules.Psxdesign.Admin'));

            return $this->redirectToRoute('admin_logos_index');
        }

        $logoUploadHandler = $this->get('prestashop.module.psxdesign.handler.logo_image_uploader');

        try {
            $logoDestination = new LogoDestination($request->get('logo_for'));
            $logoUploadHandler->applyHeaderLogoImage($logoDestination);
            $this->addFlash('success', $this->trans('Header logo applied successfully.', 'Modules.Psxdesign.Admin'));
        } catch (Throwable $e) {
            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getErrorCode($e));

            $this->addFlash('error', $this->getMessage($e));
        }

        return $this->redirectToRoute('admin_logos_index');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function uploadFaviconAction(Request $request): Response
    {
        $uploader = $this->get('prestashop.core.shop.logo_uploader');
        $logoUtility = $this->get('prestashop.module.psxdesign.utility.logo_utility');

        /** @var UploadedFile $favicon */
        $favicon = $request->files->get(ShopLogoSettings::FAVICON_FILE_NAME);

        try {
            $logoUtility->assertFaviconType($favicon->guessClientExtension());
            $uploader->updateFavicon();
            $this->get('prestashop.module.psxdesign.tracker.segment')->track('Favicon Uploaded', [], $request->server);
            $this->addFlash('success', $this->trans('Favicon updated successfully', 'Modules.Psxdesign.Admin'));
        } catch (Throwable $e) {
            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getErrorCode($e));
            $this->addFlash('error', $this->getMessage($e));
        }

        return $this->redirectToRoute('admin_logos_index');
    }

    /**
     * @throws PsxDesignLogoImportException
     */
    public function importLogoTextAction(Request $request): Response
    {
        $fontProvider = $this->get('prestashop.module.psxdesign.provider.font_data_provider');

        try {
            $logoTextData = PsxDesignLogoTextData::createFromRequest($request);
            $fontContent = $fontProvider->getFontContent($logoTextData->getFamily(), $logoTextData->getText(), $logoTextData->getStyle());
            $fontProvider->saveFontDataIntoTemporaryFolder($fontContent);

            $converter = $this->get('prestashop.module.psxdesign.converter.text_to_logo_converter');
            $imagePath = $converter->convertToImage($logoTextData);

            $logoTextUploader = $this->get('prestashop.module.psxdesign.handler.logo_text_upload_handler');
            $destination = $logoTextUploader->uploadLogoImage($imagePath, $logoTextData);
            $this->get('prestashop.module.psxdesign.tracker.segment')
                ->track(
                    'Logo Uploaded', ['logo_type' => self::UPLOAD_LOGO_TYPE_TEXT, 'logo_location' => $destination], $request->server);
            $this->addFlash('success', $this->trans('The %destination% logo has been added.', 'Modules.Psxdesign.Admin', ['%destination%' => $this->translateDestination($destination)]));
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getErrorMessageForLogoText($e));

            $errorHandler = $this->get('prestashop.module.psxdesign.exception.handler.sentry_exception_error_handler');
            $errorHandler->handle($e, $this->getErrorCode($e));
        }

        $tmpFontFile = _PS_MODULE_DIR_ . 'psxdesign/' . PsxDesignConfig::TMP_DIR_NAME . '/' . FontDataProvider::TEMPORARY_FONT_NAME;

        if (file_exists($tmpFontFile)) {
            unlink($tmpFontFile);
        }

        return $this->redirectToRoute('admin_logos_index');
    }

    /**
     * @param string $destination
     *
     * @return string
     */
    private function translateDestination(string $destination): string
    {
        switch ($destination) {
            case 'header':
                return $this->trans('header', 'Modules.Psxdesign.Admin');
            case 'email':
                return $this->trans('email', 'Modules.Psxdesign.Admin');
            case 'invoice':
                return $this->trans('invoice', 'Modules.Psxdesign.Admin');
            default:
                return $destination;
        }
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    private function getMessage(Throwable $e, $destination = null): string
    {
        $availableLogoFormatsImploded = implode(', .', ShopLogoSettings::AVAILABLE_LOGO_IMAGE_EXTENSIONS);
        $availableMailAndInvoiceFormatsImploded = implode(', .', ShopLogoSettings::AVAILABLE_MAIL_AND_INVOICE_LOGO_IMAGE_EXTENSIONS);
        $availableIconFormat = ShopLogoSettings::AVAILABLE_ICON_IMAGE_EXTENSION;

        $logoImageFormatError = $this->trans(
            'The image format is not recognized, allowed formats are: %formats%.',
            'Modules.Psxdesign.Admin',
            ['%formats%' => $availableLogoFormatsImploded]
        );

        $mailAndInvoiceImageFormatError = $this->trans(
            'The image format is not recognized, allowed formats are: %formats%.',
            'Modules.Psxdesign.Admin',
            ['%formats%' => $availableMailAndInvoiceFormatsImploded]
        );

        $iconFormatError = $this->trans(
            'The image format is not recognized, allowed format is: .%format%.',
            'Modules.Psxdesign.Admin',
            ['%format%' => $availableIconFormat]
        );

        $map = [
            PsxDesignLogoImportException::class => [
                PsxDesignLogoImportException::INVALID_FORMAT => $this->trans('The format is invalid, allowed formats are %allowed_formats%.', 'Modules.Psxdesign.Admin', ['%allowed_formats%' => implode(', ', ShopLogoSettings::AVAILABLE_MAIL_AND_INVOICE_LOGO_IMAGE_EXTENSIONS)]),
                PsxDesignLogoImportException::INVALID_DESTINATION => $this->trans('The logo destination is invalid. Please try again.', 'Modules.Psxdesign.Admin'),
                PsxDesignLogoImportException::LOGO_INCOMPATIBILITY => $this->trans('The .svg format is not compatible.', 'Modules.Psxdesign.Admin'),
                PsxDesignLogoImportException::INSERT_LOGO_FAILED => $this->trans('The logo upload failed. Please try again.', 'Modules.Psxdesign.Admin'),
            ],

            NotSupportedLogoImageExtensionException::class => $logoImageFormatError,
            NotSupportedMailAndInvoiceImageExtensionException::class => $mailAndInvoiceImageFormatError,
            NotSupportedFaviconExtensionException::class => $iconFormatError,
            FileUploadException::class => [
                UPLOAD_ERR_INI_SIZE => $this->trans(
                    'The file is too large (limit of %size% bytes).',
                    'Modules.Psxdesign.Admin',
                    [
                        '%size%' => UploadedFile::getMaxFilesize(),
                    ]
                ),
            ],
        ];

        if (isset($map[get_class($e)]) && is_array($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? $this->trans('The logo upload failed. Please try again.', 'Modules.Psxdesign.Admin');
        }

        if (get_class($e) === PrestaShopException::class) {
            return $destination === self::HEADER
                ? $logoImageFormatError
                : $mailAndInvoiceImageFormatError;
        }

        return $map[get_class($e)] ?? $this->getFallbackErrorMessage(get_class($e), $e->getCode());
    }

    /**
     * @param Throwable $e
     *
     * @return int
     */
    private function getErrorCode(Throwable $e): int
    {
        if ($e instanceof PsxDesignException) {
            return $e->getCode();
        }

        $map = [
            NotSupportedLogoImageExtensionException::class => PsxDesignException::INFO_SEVERITY,
            NotSupportedMailAndInvoiceImageExtensionException::class => PsxDesignException::INFO_SEVERITY,
            NotSupportedFaviconExtensionException::class => PsxDesignException::INFO_SEVERITY,
            FileUploadException::class => [
                UPLOAD_ERR_INI_SIZE => PsxDesignException::INFO_SEVERITY,
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
     * @param Throwable $e
     *
     * @return string
     */
    private function getErrorMessageForLogoText(Throwable $e): string
    {
        $map = [
            PsxDesignTextToImageConvertException::class => [
                PsxDesignTextToImageConvertException::FAILED_INIT_GD_STREAM => $this->trans('Failed to initialize image creation. PHP GD extension missing.', 'Modules.Psxdesign.Admin'),
                PsxDesignTextToImageConvertException::FAILED_TO_CONVERT => $this->trans('Failed convert text to image. Please select different font.', 'Modules.Psxdesign.Admin'),
                PsxDesignTextToImageConvertException::FAILED_ADD_TEXT => $this->trans('Failed convert text to image. Please select different font.', 'Modules.Psxdesign.Admin'),
                PsxDesignTextToImageConvertException::FAILED_FETCH_FONT => $this->trans('This font does not exist. Please choose another font.', 'Modules.Psxdesign.Admin'),
                PsxDesignTextToImageConvertException::FAILED_CREATE_TMP_DIR => $this->trans('The creation of a temporary directory to store the uploaded font has failed. Please check the user permissions on your system.', 'Modules.Psxdesign.Admin'),
            ],
            PrestaShopException::class => $this->trans('Failed to convert text to logo. Please try again.', 'Modules.Psxdesign.Admin'),
            PsxDesignApiException::class => [
                PsxDesignApiException::FAILED_FETCH_FONT => $this->trans('Failed to retrieve font. Please use different font.', 'Modules.Psxdesign.Admin'),
            ],
            PsxDesignLogoImportException::class => [
                PsxDesignLogoImportException::TEXT_LENGTH_EXCEEDED => $this->trans('The text may not be longer than 64 characters.', 'Modules.Psxdesign.Admin'),
            ],
        ];

        if (isset($map[get_class($e)]) && is_array($map[get_class($e)])) {
            return $map[get_class($e)][$e->getCode()] ?? $this->trans('The logo upload failed. Please try again.', 'Modules.Psxdesign.Admin');
        }

        return $map[get_class($e)] ?? $this->getFallbackErrorMessage(get_class($e), $e->getCode());
    }

    /**
     * @return array
     */
    private function getLogosData(): array
    {
        /** @var LogosPaths $logoProvider */
        $logoProvider = $this->getQueryBus()->handle(new GetLogosPaths());

        /** @var PsxdesignLogoRepository $logosRepository */
        $logosRepository = $this->get('prestashop.module.psxdesign.repository.psxdesign_logo_repository');
        $headerLogo = $logosRepository->getHeaderLogo();
        $invoiceLogo = $logosRepository->getInvoiceLogo();
        $emailLogo = $logosRepository->getEmailLogo();

        $logos = [];

        if ($headerLogo !== null) {
            $logos['header'] = $headerLogo->toArray();
        }

        if ($invoiceLogo !== null) {
            $logos['invoice'] = $invoiceLogo->toArray();
        }

        if ($emailLogo !== null) {
            $logos['email'] = $emailLogo->toArray();
        }

        if ($logoProvider->getHeaderLogoPath() === $logoProvider->getMailLogoPath()) {
            $logos['email']['useHeaderLogo'] = true;
        }

        if ($logoProvider->getHeaderLogoPath() === $logoProvider->getInvoiceLogoPath()) {
            $logos['invoice']['useHeaderLogo'] = true;
        }

        return $logos;
    }
}
