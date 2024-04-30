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

namespace PrestaShop\Module\PsxDesign\Entity;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\ORM\Mapping as ORM;

/**
 * ./bin/console doctrine:schema:update --dump-sql
 *
 * Font
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class PsxdesignFonts
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", unique="true")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="variable_name", type="string", length="255")
     */
    private $variableName;

    /**
     * @var string
     *
     * @ORM\Column(name="variable_type", type="string", length="255")
     */
    private $variableType;

    /**
     * @var string
     *
     * @ORM\Column(name="`font`", type="string", length="64")
     */
    private $font;

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length="64")
     */
    private $style;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="theme_name", type="string", length="64")
     */
    private $themeName;

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
    public function getVariableName(): string
    {
        return $this->variableName;
    }

    /**
     * @param string $variableName
     *
     * @return PsxdesignFonts
     */
    public function setVariableName(string $variableName): PsxdesignFonts
    {
        $this->variableName = $variableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariableType(): string
    {
        return $this->variableType;
    }

    /**
     * @param string $variableType
     *
     * @return PsxdesignFonts
     */
    public function setVariableType(string $variableType): PsxdesignFonts
    {
        $this->variableType = $variableType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFont(): string
    {
        return $this->font;
    }

    /**
     * @param string $font
     *
     * @return PsxdesignFonts
     */
    public function setFont(string $font): PsxdesignFonts
    {
        $this->font = $font;

        return $this;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @param string $style
     *
     * @return PsxdesignFonts
     */
    public function setStyle(string $style): PsxdesignFonts
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return PsxdesignFonts
     */
    public function setSize(int $size): PsxdesignFonts
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getThemeName(): string
    {
        return $this->themeName;
    }

    /**
     * @param string $themeName
     *
     * @return PsxdesignFonts
     */
    public function setThemeName(string $themeName): PsxdesignFonts
    {
        $this->themeName = $themeName;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'variable_name' => $this->getVariableName(),
            'variable_type' => $this->getVariableType(),
            'font' => $this->getFont(),
            'style' => $this->getStyle(),
            'size' => $this->getSize(),
            'theme_name' => $this->getThemeName(),
        ];
    }
}
