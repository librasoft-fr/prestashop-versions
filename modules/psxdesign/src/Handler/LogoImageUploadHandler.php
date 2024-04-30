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

namespace PrestaShop\Module\PsxDesign\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignLogo;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignLogoRepository;
use PrestaShop\Module\PsxDesign\Utility\LogoUtility;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoDestination;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;
use PrestaShop\PrestaShop\Core\Shop\LogoUploader;

class LogoImageUploadHandler implements LogoUploaderInterface
{
    private const UPLOAD_LOGO_TYPE_IMAGE = 'image';
    private const HEADER = 'header';
    private const INCOMPATIBLE_HEADER_LOGO_TYPES = ['svg'];
    /**
     * @var LogoUploader
     */
    private $logoUploader;

    /**
     * @var PsxdesignLogoRepository
     */
    private $logoRepository;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var LogoUtility
     */
    private $logoUtility;

    /**
     * @var string
     */
    private $imageDir;

    public function __construct(
        LogoUploader $logoUploader,
        PsxdesignLogoRepository $logoRepository,
        LogoUtility $logoUtility,
        Configuration $configuration,
        string $imageDir
    ) {
        $this->logoUploader = $logoUploader;
        $this->logoRepository = $logoRepository;
        $this->logoUtility = $logoUtility;
        $this->configuration = $configuration;
        $this->imageDir = $imageDir;
    }

    /**
     * @param PsxDesignLogoData $logoData
     * @param string $uploadType
     *
     * @return string
     *
     * @throws PsxDesignException
     * @throws PsxDesignLogoImportException
     */
    public function uploadLogo(PsxDesignLogoData $logoData, string $uploadType): string
    {
        $this->configuration->set('PS_IMG_UPDATE_TIME', time());

        $fieldName = $logoData->getDestinationFieldName();
        $isLogoSvgType = $this->logoUtility->isSvgMimeType($logoData->getMimeType());

        if ($fieldName === ShopLogoSettings::HEADER_LOGO_FILE_NAME) {
            return $this->uploadHeaderLogo($logoData, $uploadType);
        }

        if ($isLogoSvgType) {
            throw new PsxDesignLogoImportException('Svg type is not allowed for email, invoice logo', PsxDesignLogoImportException::INVALID_FORMAT);
        }

        if ($fieldName === ShopLogoSettings::MAIL_LOGO_FILE_NAME) {
            return $this->uploadMailLogo($logoData, $uploadType);
        }

        if ($fieldName === ShopLogoSettings::INVOICE_LOGO_FILE_NAME) {
            return $this->uploadInvoiceLogo($logoData, $uploadType);
        }

        throw new PsxDesignLogoImportException('Logo destination is incorrect', PsxDesignLogoImportException::INVALID_DESTINATION);
    }

    /**
     * @param PsxDesignLogoData $logoData
     * @param string $uploadType
     *
     * @return string
     *
     * @throws PsxDesignException
     */
    protected function uploadHeaderLogo(PsxDesignLogoData $logoData, string $uploadType): string
    {
        if ($this->logoUtility->isSvgMimeType($logoData->getMimeType())) {
            $this->logoRepository->disableAllUseHeader();
            $this->duplicateLogos($logoData);
            $this->logoUploader->updateHeader();
            $this->logoRepository->updateLogoImage($logoData->getDestination(), $uploadType);

            return $logoData->getDestination();
        }

        $this->logoUploader->updateHeader();
        $this->logoRepository->updateLogoImage($logoData->getDestination(), $uploadType);

        $destinations = [];

        /** @var PsxdesignLogo $logo */
        foreach ($this->logoRepository->getActiveLogos() as $logo) {
            if (!$logo->getUseHeaderLogo() && $logo->getLogoDestination() !== self::HEADER) {
                continue;
            }

            $logoDestination = new LogoDestination($logo->getLogoDestination());
            $this->configuration->set($logoDestination->getDestinationFieldName(), $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME));
            $this->logoRepository->useSameAsHeaderLogo($logoDestination->getValue());

            $destinations[] = $logo->getLogoDestination();
        }

        return $this->getUploadedDestinations($destinations);
    }

    protected function uploadMailLogo(PsxDesignLogoData $logoData, string $uploadType): string
    {
        if ($this->configuration->get($logoData->getDestinationFieldName()) === $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME)) {
            $this->configuration->set($logoData->getDestinationFieldName(), '');
        }

        $this->logoUploader->updateMail();
        $this->logoRepository->updateLogoImage($logoData->getDestination(), $uploadType, true, false);

        return $logoData->getDestination();
    }

    protected function uploadInvoiceLogo(PsxDesignLogoData $logoData, string $uploadType): string
    {
        if ($this->configuration->get($logoData->getDestinationFieldName()) === $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME)) {
            $this->configuration->set($logoData->getDestinationFieldName(), '');
        }

        $this->logoUploader->updateInvoice();
        $this->logoRepository->updateLogoImage($logoData->getDestination(), $uploadType, true, false);

        return $logoData->getDestination();
    }

    /**
     * @param LogoDestination $logoDestination
     *
     * @return void
     *
     * @throws PsxDesignLogoImportException
     */
    public function applyHeaderLogoImage(LogoDestination $logoDestination): void
    {
        if (in_array($this->logoUtility->getHeaderLogoMimeType(), self::INCOMPATIBLE_HEADER_LOGO_TYPES)) {
            throw new PsxDesignLogoImportException('Header logo is not compatible.', PsxDesignLogoImportException::LOGO_INCOMPATIBILITY);
        }

        $fieldName = $logoDestination->getDestinationFieldName();

        if ($this->configuration->get($fieldName) !== $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME)) {
            @unlink($this->imageDir . $this->configuration->get($fieldName));
        }

        $this->configuration->set($fieldName, $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME));

        $headerLogo = $this->logoRepository->getHeaderLogo();

        if (!$headerLogo) {
            $this->logoRepository->updateLogoImage(self::HEADER, self::UPLOAD_LOGO_TYPE_IMAGE, true, true);
        }

        $this->logoRepository->useSameAsHeaderLogo($logoDestination->getValue());
    }

    /**
     * @param PsxDesignLogoData $headerLogo
     *
     * @return void
     *
     * @throws \Exception
     */
    private function duplicateLogos(PsxDesignLogoData $headerLogo): void
    {
        $headerLogoName = $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME);
        $headerImagePath = $this->imageDir . $headerLogoName;

        if ($headerLogoName === $this->configuration->get(ShopLogoSettings::MAIL_LOGO_FILE_NAME)) {
            $newMailLogoName = $this->buildLogoName('mail', $headerLogo->getDestinationFieldName());
            copy($headerImagePath, $this->imageDir . $newMailLogoName);
            $this->configuration->set(ShopLogoSettings::MAIL_LOGO_FILE_NAME, $newMailLogoName);
        }

        if ($headerLogoName === $this->configuration->get(ShopLogoSettings::INVOICE_LOGO_FILE_NAME)) {
            $newInvoiceLogoName = $this->buildLogoName('invoice', $headerLogo->getDestinationFieldName());
            copy($headerImagePath, $this->imageDir . $newInvoiceLogoName);
            $this->configuration->set(ShopLogoSettings::INVOICE_LOGO_FILE_NAME, $newInvoiceLogoName);
        }
    }

    /**
     * @param string $prefix
     * @param string $fieldName
     *
     * @return string
     */
    private function buildLogoName(string $prefix, string $fieldName): string
    {
        return 'logo_' . $prefix . '.' . pathinfo($this->imageDir . $this->configuration->get($fieldName), \PATHINFO_EXTENSION);
    }

    /**
     * @param array $destinations
     *
     * @return string
     */
    private function getUploadedDestinations(array $destinations): string
    {
        return implode(', ', $destinations);
    }
}
