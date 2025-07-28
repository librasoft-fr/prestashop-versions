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

use PrestaShop\Module\PsxDesign\Entity\PsxdesignFonts;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PsxDesignFontConfiguration
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $variableName;

    /**
     * @var string
     */
    private $variableType;

    /**.
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $font;

    /**
     * @var string
     */
    private $style;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $placeholder;

    /**
     * @var string
     */
    private $category;

    private function __construct(ParameterBag $configuration, PsxdesignFonts $data = null)
    {
        $this->setId($data ? $data->getId() : 0);
        $this->setLabel($configuration->get('label'));
        $this->setVariableName($configuration->get('variable_name'));
        $this->setVariableType($configuration->get('variable_type'));
        $this->setFont($data ? $data->getFont() : $configuration->get('font'));
        $this->setStyle($data ? $data->getStyle() : $configuration->get('style'));
        $this->setSize($data ? $data->getSize() : $configuration->get('size'));
        $this->setDescription($configuration->get('description'));
        $this->setPlaceholder($configuration->get('placeholder'));
        $this->setCategory($configuration->get('category'));
    }

    /**
     * @param array $fontConfiguration
     *
     * @return PsxDesignFontConfiguration
     */
    public static function createFromThemeConfiguration(array $fontConfiguration): self
    {
        $parameterBag = new ParameterBag($fontConfiguration);

        return new self($parameterBag);
    }

    /**
     * @param PsxDesignFontConfiguration $fontConfiguration
     * @param PsxdesignFonts|null $data
     *
     * @return PsxDesignFontConfiguration
     */
    public static function createFromFontConfigurationAndEntity(PsxDesignFontConfiguration $fontConfiguration, PsxdesignFonts $data = null): self
    {
        return new self(new ParameterBag($fontConfiguration->toArray()), $data);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @return string
     */
    public function getVariableType(): string
    {
        return $this->variableType;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFont(): string
    {
        return $this->font;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
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
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $label
     *
     * @return void
     */
    private function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string $variableName
     *
     * @return void
     */
    private function setVariableName(string $variableName): void
    {
        $this->variableName = $variableName;
    }

    /**
     * @param string $variableType
     *
     * @return void
     */
    private function setVariableType(string $variableType): void
    {
        $this->variableType = $variableType;
    }

    /**
     * @param int|null $id
     *
     * @return void
     */
    private function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $font
     *
     * @return void
     */
    private function setFont(string $font): void
    {
        $this->font = $font;
    }

    /**
     * @param string $style
     *
     * @return void
     */
    private function setStyle(string $style): void
    {
        $this->style = $style;
    }

    /**
     * @param int $size
     *
     * @return void
     */
    private function setSize(int $size): void
    {
        $this->size = $size;
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
     * @param string $placeholder
     *
     * @return void
     */
    private function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @param string $category
     *
     * @return void
     */
    private function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->getLabel(),
            'variable_name' => $this->getVariableName(),
            'variable_type' => $this->getVariableType(),
            'font' => $this->getFont(),
            'style' => $this->getStyle(),
            'size' => $this->getSize(),
            'description' => $this->getDescription(),
            'placeholder' => $this->getPlaceholder(),
            'category' => $this->getCategory(),
        ];
    }
}
