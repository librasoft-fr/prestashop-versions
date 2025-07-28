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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ./bin/console doctrine:schema:update --dump-sql
 *
 * Colors Palette
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class PsxdesignColorsPalette
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
     * @ORM\Column(name="name", type="string", length=64)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="smallint")
     */
    private $active = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="`default`", type="smallint")
     */
    private $default = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=64)
     */
    private $theme;

    /**
     * @var Collection|PsxdesignColor[]
     *
     * @ORM\OneToMany(targetEntity="PsxdesignColor", mappedBy="palette", cascade={"persist"})
     */
    private $colors;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return PsxdesignColorsPalette
     */
    public function setName(string $name): PsxdesignColorsPalette
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    /**
     * @param bool $active
     *
     * @return PsxdesignColorsPalette
     */
    public function setActive(bool $active): PsxdesignColorsPalette
    {
        $this->active = (int) $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool) $this->default;
    }

    /**
     * @param bool $default
     *
     * @return PsxdesignColorsPalette
     */
    public function setDefault(bool $default): PsxdesignColorsPalette
    {
        $this->default = (int) $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     *
     * @return PsxdesignColorsPalette
     */
    public function setTheme(string $theme): PsxdesignColorsPalette
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return PsxdesignColor[]
     */
    public function getColors(): array
    {
        return $this->colors->toArray();
    }

    /**
     * @param ArrayCollection $colors
     *
     * @return PsxdesignColorsPalette
     */
    public function setColors(ArrayCollection $colors): PsxdesignColorsPalette
    {
        $this->colors = $colors;

        return $this;
    }
}
