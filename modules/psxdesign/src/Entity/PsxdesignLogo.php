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
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoDestination;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoType;

/**
 * ./bin/console doctrine:schema:update --dump-sql
 *
 * Logo
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class PsxdesignLogo
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_destination", type="string", columnDefinition="'header', 'email', 'invoice'")
     */
    private $logoDestination;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_type", type="string",  columnDefinition="'text', 'image'")
     */
    private $logoType;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=64)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="font", type="string", length=64)
     */
    private $font;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_header_logo", type="boolean")
     */
    private $useHeaderLogo;

    /**
     * @var int
     *
     * @ORM\Column(name="font_size", type="integer")
     */
    private $fontSize;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=64)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length=64)
     */
    private $style;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

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
    public function getLogoDestination(): string
    {
        return $this->logoDestination;
    }

    /**
     * @param string $logoDestination
     *
     * @return PsxdesignLogo
     *
     * @throws PsxDesignException
     */
    public function setLogoDestination(string $logoDestination): PsxdesignLogo
    {
        $this->logoDestination = (new LogoDestination($logoDestination))->getValue();

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoType(): string
    {
        return $this->logoType;
    }

    /**
     * @param string $logoType
     *
     * @return PsxdesignLogo
     */
    public function setLogoType(string $logoType): PsxdesignLogo
    {
        $this->logoType = (new LogoType($logoType))->getValue();

        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return PsxdesignLogo
     */
    public function setText(string $text): PsxdesignLogo
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFont(): ?string
    {
        return $this->font;
    }

    /**
     * @param string $font
     *
     * @return PsxdesignLogo
     */
    public function setFont(string $font): PsxdesignLogo
    {
        $this->font = $font;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getUseHeaderLogo(): ?bool
    {
        return $this->useHeaderLogo;
    }

    /**
     * @param bool $useHeaderLogo
     *
     * @return PsxdesignLogo
     */
    public function setUseHeaderLogo(bool $useHeaderLogo): PsxdesignLogo
    {
        $this->useHeaderLogo = $useHeaderLogo;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFontSize(): ?int
    {
        return $this->fontSize;
    }

    /**
     * @param int $fontSize
     *
     * @return PsxdesignLogo
     */
    public function setFontSize(int $fontSize): PsxdesignLogo
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return PsxdesignLogo
     */
    public function setColor(string $color): PsxdesignLogo
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStyle(): ?string
    {
        return $this->style;
    }

    /**
     * @param string $style
     *
     * @return PsxdesignLogo
     */
    public function setStyle(string $style): PsxdesignLogo
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @param bool $active
     *
     * @return PsxdesignLogo
     */
    public function setActive(bool $active): PsxdesignLogo
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'logoDestination' => $this->getLogoDestination(),
            'logoType' => $this->getLogoType(),
            'text' => $this->getText(),
            'useHeaderLogo' => $this->getUseHeaderLogo(),
            'font' => $this->getFont(),
            'fontSize' => $this->getFontSize(),
            'color' => $this->getColor(),
            'style' => $this->getStyle(),
            'active' => $this->getActive(),
        ];
    }
}
