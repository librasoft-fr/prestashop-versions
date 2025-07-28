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

namespace PrestaShop\Module\PsxDesign\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ImageManager;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\NotSupportedFaviconExtensionException;

class LogoUtility
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getHeaderLogoMimeType(): string
    {
        $info = pathinfo(_PS_IMG_DIR_ . $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME));

        return $info['extension'];
    }

    public function isSvgMimeType(string $mimeType): bool
    {
        return ImageManager::isSvgMimeType($mimeType);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function getMimeType(string $fileName): string
    {
        return ImageManager::getMimeType($fileName) ?: '';
    }

    /**
     * @param string $imgDir
     *
     * @return array{width: float, height: float}
     */
    public function getImageSizes(string $imgDir): array
    {
        [$width, $height] = getimagesize($imgDir . $this->configuration->get(ShopLogoSettings::HEADER_LOGO_FILE_NAME));

        return ['width' => $width, 'height' => $height];
    }

    /**
     * @param string $extension
     *
     * @return void
     *
     * @throws NotSupportedFaviconExtensionException
     */
    public function assertFaviconType(string $extension): void
    {
        if ($extension !== ShopLogoSettings::AVAILABLE_ICON_IMAGE_EXTENSION) {
            throw new NotSupportedFaviconExtensionException(sprintf('Not supported "%s" favicon extension. Supported extension is "ico".', $extension));
        }
    }
}
