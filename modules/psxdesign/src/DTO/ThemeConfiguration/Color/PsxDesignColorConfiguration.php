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

use PrestaShop\Module\PsxDesign\Entity\PsxdesignColor;
use PrestaShop\Module\PsxDesign\VO\Color\VariableType;
use Symfony\Component\HttpFoundation\ParameterBag;

class PsxDesignColorConfiguration
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $paletteId;

    /**
     * @var string
     */
    private $variableName;

    /**
     * @var array|null
     */
    private $properties;

    /**
     * @var string
     */
    private $variableType;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $category;

    private function __construct(ParameterBag $configuration, PsxdesignColor $data = null)
    {
        $this->setId($data ? $data->getId() : 0);
        $this->setLabel($configuration->get('label'));
        $this->setPaletteId($data ? $data->getPalette()->getId() : 0);
        $this->setVariableName($configuration->get('variable_name'));
        $this->setProperties($configuration->get('properties') ?? null);
        $this->setVariableType($configuration->get('variable_type'));
        $this->setValue($data ? $data->getValue() : $configuration->get('value'));
        $this->setCategory($configuration->get('category'));
    }

    /**
     * @param array $colorConfiguration
     *
     * @return PsxDesignColorConfiguration
     */
    public static function createFromThemeConfiguration(array $colorConfiguration): PsxDesignColorConfiguration
    {
        $parameterBag = new ParameterBag($colorConfiguration);

        return new self($parameterBag);
    }

    /**
     * @param PsxDesignColorConfiguration $colorConfiguration
     * @param PsxdesignColor|null $color
     *
     * @return PsxDesignColorConfiguration
     */
    public static function createFromConfigurationAndEntity(PsxDesignColorConfiguration $colorConfiguration, ?PsxdesignColor $color): PsxDesignColorConfiguration
    {
        return new self(new ParameterBag($colorConfiguration->toArray()), $color);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getPaletteId(): int
    {
        return $this->paletteId;
    }

    /**
     * @return string
     */
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @return array|null
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getVariableType(): string
    {
        return $this->variableType;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param int $id
     */
    private function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $label
     */
    private function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param int $id
     */
    private function setPaletteId(int $id): void
    {
        $this->paletteId = $id;
    }

    /**
     * @param string $variableName
     *
     * @return void
     */
    private function setVariableName(string $variableName): void
    {
        $this->variableName = trim($variableName);
    }

    /**
     * @param array|null $properties
     *
     * @return void
     */
    private function setProperties(?array $properties): void
    {
        $this->properties = $properties;
    }

    /**
     * @param string $variableType
     *
     * @return void
     */
    private function setVariableType(string $variableType): void
    {
        $this->variableType = (new VariableType($variableType))->getType();
    }

    /**
     * @param string $value
     *
     * @return void
     */
    private function setValue(string $value): void
    {
        $this->value = trim($value);
    }

    /**
     * @param string $category
     *
     * @return void
     */
    private function setCategory(string $category): void
    {
        $this->category = trim($category);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'variable_name' => $this->getVariableName(),
            'properties' => $this->getProperties(),
            'variable_type' => $this->getVariableType(),
            'value' => $this->getValue(),
            'category' => $this->getCategory(),
        ];
    }
}
