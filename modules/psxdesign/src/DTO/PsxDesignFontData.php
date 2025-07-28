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

use Symfony\Component\HttpFoundation\ParameterBag;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsxDesignFontData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $variableName;

    /**
     * @var string
     */
    private $variableType;

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

    private function __construct(ParameterBag $data)
    {
        $this->setId($data->getInt('id'));
        $this->setVariableName($data->get('variable_name') ?? '');
        $this->setVariableType($data->get('variable_type') ?? '');
        $this->setFont($data->get('font') ?? '');
        $this->setStyle($data->get('style') ?? '');
        $this->setSize($data->getInt('size'));
    }

    /**
     * @param array $font
     *
     * @return PsxDesignFontData
     */
    public static function createFromRequest(array $font): self
    {
        return new self(new ParameterBag($font));
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
     * @param string $variableType
     *
     * @return void
     */
    public function setVariableType(string $variableType): void
    {
        $this->variableType = trim($variableType);
    }

    /**
     * @param int $id
     *
     * @return void
     */
    private function setId(int $id): void
    {
        $this->id = $id;
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
     * @param string $font
     *
     * @return void
     */
    private function setFont(string $font): void
    {
        $this->font = trim($font);
    }

    /**
     * @param string $style
     *
     * @return void
     */
    private function setStyle(string $style): void
    {
        $this->style = trim($style);
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
}
