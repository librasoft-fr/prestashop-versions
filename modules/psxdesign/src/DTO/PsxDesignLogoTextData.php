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

use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoDestination;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoTextStyle;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PsxDesignLogoTextData
{
    public const TYPE = 'text';
    private const MAX_TEXT_LENGTH = 64;

    /**
     * @var LogoDestination
     */
    private $destination;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $color;

    /**
     * @var LogoTextStyle
     */
    private $style;

    /**
     * @var string
     */
    private $family;

    /**
     * @param ParameterBag $data
     *
     * @throws PsxDesignLogoImportException
     */
    private function __construct(ParameterBag $data)
    {
        $this->setDestination($data->get('logo_for') ?? '');
        $this->setText($data->get('logo_text') ?? '');
        $this->setFamily($data->get('font_family') ?? '');
        $this->setSize($data->getInt('font_size'));
        $this->setColor($data->get('font_color') ?? '');
        $this->setStyle($data->get('font_style') ?? '');
    }

    /**
     * @param Request $request
     *
     * @return PsxDesignLogoTextData
     *
     * @throws PsxDesignLogoImportException
     */
    public static function createFromRequest(Request $request): self
    {
        return new self($request->request);
    }

    /**
     * @return LogoDestination
     */
    public function getDestination(): LogoDestination
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @throws PsxDesignLogoImportException
     * @throws PsxDesignException
     */
    private function setDestination(string $destination): void
    {
        $this->destination = new LogoDestination($destination);
    }

    /**
     * @param string $text
     *
     * @return void
     *
     * @throws PsxDesignLogoImportException
     */
    private function setText(string $text): void
    {
        if (!$text) {
            throw new PsxDesignLogoImportException('Logo text is empty', PsxDesignLogoImportException::EMPTY_TEXT);
        }

        if (strlen($text) > self::MAX_TEXT_LENGTH) {
            throw new PsxDesignLogoImportException('Logo text is to long', PsxDesignLogoImportException::TEXT_LENGTH_EXCEEDED);
        }

        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param int $size
     *
     * @throws PsxDesignLogoImportException
     */
    private function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param string $color
     *
     * @throws PsxDesignLogoImportException
     */
    private function setColor(string $color): void
    {
        if (!$color) {
            throw new PsxDesignLogoImportException('Logo color is invalid', PsxDesignLogoImportException::INVALID_COLOR);
        }

        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $style
     *
     * @throws PsxDesignLogoImportException
     */
    private function setStyle(string $style): void
    {
        $style = new LogoTextStyle($style);

        $this->style = $style;
    }

    /**
     * @return LogoTextStyle
     */
    public function getStyle(): LogoTextStyle
    {
        return $this->style;
    }

    /**
     * @param string $family
     *
     * @return void
     */
    private function setFamily(string $family): void
    {
        $this->family = $family;
    }

    /**
     * @return string
     */
    public function getFamily(): string
    {
        return $this->family;
    }
}
