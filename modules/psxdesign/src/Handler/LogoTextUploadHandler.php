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

use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoTextData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignLogoRepository;
use PrestaShop\Module\PsxDesign\Utility\LogoUtility;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;

class LogoTextUploadHandler
{
    private const LOGO_EXTENSION = '.png';
    private const EMAIL = 'email';
    private const INVOICE = 'invoice';
    private const HEADER = 'header';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PsxdesignLogoRepository
     */
    private $logoRepository;

    /**
     * @var LogoUtility
     */
    private $logoUtility;

    /**
     * @var string
     */
    private $imageDir;

    public function __construct(Configuration $configuration, PsxdesignLogoRepository $logoRepository, LogoUtility $logoUtility, string $imageDir)
    {
        $this->configuration = $configuration;
        $this->logoRepository = $logoRepository;
        $this->logoUtility = $logoUtility;
        $this->imageDir = $imageDir;
    }

    /**
     * @throws PsxDesignLogoImportException
     */
    public function uploadLogoImage(string $newLogoPath, PsxDesignLogoTextData $logoData): string
    {
        $fieldName = $logoData->getDestination()->getDestinationFieldName();

        if ($fieldName === ShopLogoSettings::HEADER_LOGO_FILE_NAME) {
            return $this->replaceHeaderLogo($newLogoPath, $logoData);
        }

        if ($fieldName === ShopLogoSettings::MAIL_LOGO_FILE_NAME) {
            return $this->uploadMailLogo($newLogoPath, $logoData);
        }

        if ($fieldName === ShopLogoSettings::INVOICE_LOGO_FILE_NAME) {
            return $this->uploadInvoiceLogo($newLogoPath, $logoData);
        }

        throw new PsxDesignLogoImportException('Logo destination is incorrect', PsxDesignLogoImportException::INVALID_DESTINATION);
    }

    /**
     * @param string $tmpLogo
     *
     * @return string
     *
     * @throws PsxDesignLogoImportException
     */
    protected function replaceHeaderLogo(string $tmpLogo, PsxDesignLogoTextData $logoData): string
    {
        $oldHeaderLogoName = $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME);
        $newLogoName = $this->buildLogoName($logoData->getDestination()->getValue());
        $destination[] = self::HEADER;

        unlink($this->imageDir . $oldHeaderLogoName);
        rename($tmpLogo, $this->imageDir . $newLogoName);
        $this->configuration->set(ShopLogoSettings::HEADER_LOGO_FILE_NAME, $newLogoName);

        $sizes = $this->logoUtility->getImageSizes($this->imageDir);
        $this->configuration->set('SHOP_LOGO_HEIGHT', (int) round($sizes['height']));
        $this->configuration->set('SHOP_LOGO_WIDTH', (int) round($sizes['width']));

        $this->logoRepository->updateLogoTextImage($logoData);

        if ($oldHeaderLogoName === $this->configuration->get(ShopLogoSettings::MAIL_LOGO_FILE_NAME)) {
            $this->configuration->set(ShopLogoSettings::MAIL_LOGO_FILE_NAME, $newLogoName);
            $this->logoRepository->useSameAsHeaderLogo(self::EMAIL);
            $destination[] = self::EMAIL;
        }

        if ($oldHeaderLogoName === $this->configuration->get(ShopLogoSettings::INVOICE_LOGO_FILE_NAME)) {
            $this->configuration->set(ShopLogoSettings::INVOICE_LOGO_FILE_NAME, $newLogoName);
            $this->logoRepository->useSameAsHeaderLogo(self::INVOICE);
            $destination[] = self::INVOICE;
        }

        return implode(', ', $destination);
    }

    /**
     * @param string $tmpLogo
     * @param PsxDesignLogoTextData $logoData
     *
     * @return string
     *
     * @throws \PrestaShop\Module\PsxDesign\Exception\PsxDesignException
     */
    protected function uploadMailLogo(string $tmpLogo, PsxDesignLogoTextData $logoData): string
    {
        $this->logoRepository->updateLogoTextImage($logoData);

        rename($tmpLogo, $this->imageDir . $this->buildLogoName($logoData->getDestination()->getValue()));

        $this->configuration->set(ShopLogoSettings::MAIL_LOGO_FILE_NAME, $this->buildLogoName($logoData->getDestination()->getValue()));

        return self::EMAIL;
    }

    /**
     * @param string $tmpLogo
     *
     * @return string
     *
     * @throws PsxDesignLogoImportException
     */
    protected function uploadInvoiceLogo(string $tmpLogo, PsxDesignLogoTextData $logoData): string
    {
        $this->logoRepository->updateLogoTextImage($logoData);

        rename($tmpLogo, $this->imageDir . $this->buildLogoName($logoData->getDestination()->getValue()));

        $this->configuration->set(ShopLogoSettings::INVOICE_LOGO_FILE_NAME, $this->buildLogoName($logoData->getDestination()->getValue()));

        return self::INVOICE;
    }

    private function buildLogoName(string $destination): string
    {
        return $destination . self::LOGO_EXTENSION;
    }
}
