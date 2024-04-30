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
use PrestaShop\Module\PsxDesign\VO\Color\VariableType;

/**
 * ./bin/console doctrine:schema:update --dump-sql
 *
 * Color
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class PsxdesignColor
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
     * @ORM\Column(name="variable_name", type="string", length=255)
     */
    private $variableName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64)
     * @ORM\Column(name="variable_type", type="string",  columnDefinition="'css_selector', 'scss_variable', 'css_variable'")
     */
    private $variableType;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=7)
     */
    private $value;

    /**
     * @var PsxdesignColorsPalette
     *
     * @ORM\ManyToOne(targetEntity="PsxdesignColorsPalette")
     * @ORM\JoinColumn(name="id_palette", referencedColumnName="id")
     */
    private $palette;

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
     * @return PsxdesignColor
     */
    public function setVariableName(string $variableName): self
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
     * @return PsxdesignColor
     */
    public function setVariableType(string $variableType): self
    {
        $this->variableType = (new VariableType($variableType))->getType();

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return PsxdesignColor
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return PsxdesignColorsPalette
     */
    public function getPalette(): PsxdesignColorsPalette
    {
        return $this->palette;
    }

    /**
     * @param PsxdesignColorsPalette $palette
     */
    public function setPalette(PsxdesignColorsPalette $palette): void
    {
        $this->palette = $palette;
    }

    /**
     * Convert the color object to an associative array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'variable_name' => $this->getVariableName(),
            'variable_type' => $this->getVariableType(),
            'value' => $this->getValue(),
            'palette' => $this->getPalette(),
        ];
    }
}
