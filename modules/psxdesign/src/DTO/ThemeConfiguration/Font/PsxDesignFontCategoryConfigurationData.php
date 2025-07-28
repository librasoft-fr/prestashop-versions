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

namespace PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font;

if (!defined('_PS_VERSION_')) {
    exit;
}
class PsxDesignFontCategoryConfigurationData
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $key;

    /**
     * @var PsxDesignFontConfiguration[]
     */
    private $fonts;

    private function __construct(PsxDesignFontCategoryConfiguration $fontCategory, array $fonts)
    {
        $this->setTitle($fontCategory->getTitle());
        $this->setDescription($fontCategory->getDescription());
        $this->setKey($fontCategory->getKey());
        $this->setFonts($fonts);
    }

    /**
     * @param PsxDesignFontCategoryConfiguration $fontCategory
     * @param PsxDesignFontConfiguration[]|array $fonts
     *
     * @return PsxDesignFontCategoryConfigurationData
     */
    public static function createFromCategoryAndFonts(PsxDesignFontCategoryConfiguration $fontCategory, array $fonts): self
    {
        return new self($fontCategory, $fonts);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return PsxDesignFontConfiguration[]
     */
    public function getFonts(): array
    {
        return $this->fonts;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    private function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    private function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    private function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     *
     * @return void
     */
    private function setFonts(array $fonts): void
    {
        $this->fonts = $fonts;
    }
}
