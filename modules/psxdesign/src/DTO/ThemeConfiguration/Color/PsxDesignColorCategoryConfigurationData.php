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

namespace PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Color;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;

class PsxDesignColorCategoryConfigurationData
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $key;

    /**
     * @var PsxDesignColorData[]
     */
    private $colors;

    private function __construct(PsxDesignColorCategoryConfiguration $colorCategory, array $colors)
    {
        $this->setTitle($colorCategory->getTitle());
        $this->setKey($colorCategory->getKey());
        $this->setColors($colors);
    }

    /**
     * @param PsxDesignColorCategoryConfiguration $colorCategory
     * @param PsxDesignColorConfiguration[] $colors
     *
     * @return PsxDesignColorCategoryConfigurationData
     */
    public static function createFromCategoryAndColors(PsxDesignColorCategoryConfiguration $colorCategory, array $colors): self
    {
        return new self($colorCategory, $colors);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    private function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    private function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return PsxDesignColorData[]
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @param PsxDesignColorData[] $colors
     */
    private function setColors(array $colors): void
    {
        $this->colors = $colors;
    }
}
