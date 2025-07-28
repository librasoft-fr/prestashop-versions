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

use PrestaShop\Module\PsxDesign\VO\Color\VariableType;
use Symfony\Component\HttpFoundation\ParameterBag;

class PsxDesignColorData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $paletteId;

    /**
     * @var string
     */
    private $title;

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

    private function __construct(ParameterBag $data)
    {
        $this->id = $data->getInt('id');
        $this->paletteId = $data->getInt('palette_id');
        $this->title = $data->get('title');
        $this->variableName = $data->get('variable_name') ?? '';
        $this->properties = $data->get('properties') ?? null;
        $this->variableType = (new VariableType($data->get('variable_type') ?? ''))->getType();
        $this->value = $data->get('value');
    }

    /**
     * @param array $color
     *
     * @return PsxDesignColorData
     */
    public static function createFromRequest(array $color): self
    {
        return new self(new ParameterBag($color));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
    public function getTitle()
    {
        return $this->title;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'palette_id' => $this->getPaletteId(),
            'title' => $this->getTitle(),
            'variable_name' => $this->getVariableName(),
            'properties' => $this->getProperties(),
            'variable_type' => $this->getVariableType(),
            'value' => $this->getValue(),
        ];
    }
}
