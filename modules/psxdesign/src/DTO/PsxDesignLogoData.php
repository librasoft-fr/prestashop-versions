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

namespace PrestaShop\Module\PsxDesign\DTO;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoDestination;
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class PsxDesignLogoData
{
    private const HEADER = 'header';
    private const EMAIL = 'email';
    private const INVOICE = 'invoice';
    /**
     * @var string
     */
    private $destination;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @param array<string, mixed> $data
     * @param UploadedFile $file
     *
     * @throws PsxDesignLogoImportException
     */
    private function __construct(array $data, UploadedFile $file)
    {
        $this->destination = $this->setDestination($data);
        $this->fieldName = $this->setDestinationFieldName();
        $this->mimeType = $this->setMimeType($file);
    }

    /**
     * @throws PsxDesignLogoImportException
     */
    public static function createFromRequest(Request $request): self
    {
        $destination = new LogoDestination($request->request->get('logo_for'));

        return new self($request->request->all(), $request->files->get($destination->getDestinationFieldName()));
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     */
    private function setMimeType(UploadedFile $file): string
    {
        return $this->mimeType = $file->getMimeType();
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return string
     *
     * @throws PsxDesignLogoImportException
     */
    private function setDestination(array $data): string
    {
        if (empty($data['logo_for'])) {
            throw new PsxDesignLogoImportException('Logo destination is not valid', PsxDesignLogoImportException::INVALID_DESTINATION);
        }

        return $data['logo_for'];
    }

    /**
     * @return string
     *
     * @throws PsxDesignLogoImportException
     */
    private function setDestinationFieldName(): string
    {
        $map = [
            self::HEADER => ShopLogoSettings::HEADER_LOGO_FILE_NAME,
            self::EMAIL => ShopLogoSettings::MAIL_LOGO_FILE_NAME,
            self::INVOICE => ShopLogoSettings::INVOICE_LOGO_FILE_NAME,
        ];

        if (empty($this->destination)) {
            throw new PsxDesignLogoImportException('Logo destination field name is not valid', PsxDesignLogoImportException::INVALID_DESTINATION);
        }

        return $map[$this->destination];
    }

    /**
     * @return string
     */
    public function getDestinationFieldName(): string
    {
        return $this->fieldName;
    }
}
