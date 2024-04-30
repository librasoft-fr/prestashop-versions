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

namespace PrestaShop\Module\PsxDesign\Converter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use GdImage;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoTextData;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignTextToImageConvertException;
use PrestaShop\Module\PsxDesign\Provider\FontDataProvider;

class TextToLogoConverter implements TextToImageConverterInterface
{
    private const TRANSPARENT_OPACITY = 127;
    private const WHITE_RGB = ['r' => 255, 'g' => 255, 'b' => 255];
    private const HORIZONTAL_ANGLE = 0;
    private const PADDING_FOR_BOX = 20;
    private const TMP_IMAGE_NAME = 'tmp_logo';
    private const EXTENSION = '.png';

    /**
     * @var false|resource|GdImage
     */
    private $image;

    /**
     * @var int
     */
    private $boxWidth;

    /**
     * @var int
     */
    private $boxHeight;

    /**
     * @var string
     */
    protected $fontPath;

    /**
     * @var int
     */
    protected $textAngle = self::HORIZONTAL_ANGLE;

    /**
     * A value between 0 and 127.
     * 0 indicates completely opaque while
     * 127 indicates completely transparent.
     *
     * @var int
     */
    protected $backgroundOpacity = self::TRANSPARENT_OPACITY;

    /**
     * Background color defined with rgb colors.
     *
     * @var array{r: int, g: int, b: int}
     */
    protected $backgroundColor = self::WHITE_RGB;

    public function __construct()
    {
        $path = realpath(_PS_MODULE_DIR_ . 'psxdesign/' . PsxDesignConfig::TMP_DIR_NAME . '/' . FontDataProvider::TEMPORARY_FONT_NAME);
        $this->fontPath = $path !== false ? $path : '';
    }

    /**
     * @param PsxDesignLogoTextData $logoData
     *
     * @return string
     *
     * @throws PsxDesignTextToImageConvertException
     */
    public function convertToImage(PsxDesignLogoTextData $logoData): string
    {
        $this
            ->initImageCreation($logoData->getSize(), $logoData->getText())
            ->renderTextOnImage(
                $logoData->getSize(),
                $logoData->getText(),
                $logoData->getColor()
            )
        ;

        $imagePath = $this->createImage();
        $this->destroyImageFromMemory();

        return $imagePath;
    }

    /**
     * Initializes image bounds by font width and height.
     *
     * @param int $fontSize
     * @param string $text
     *
     * @return TextToLogoConverter
     *
     * @throws PsxDesignTextToImageConvertException
     */
    private function initImageCreation(int $fontSize, string $text): self
    {
        $typeSpace = imageftbbox($fontSize, $this->textAngle, $this->fontPath, $text);

        // Determine image width and height by text width with  padding
        $this->boxWidth = abs($typeSpace[4] - $typeSpace[0]) + self::PADDING_FOR_BOX;
        $this->boxHeight = abs($typeSpace[5] - $typeSpace[1]) + self::PADDING_FOR_BOX;

        $this->image = imagecreatetruecolor((int) $this->boxWidth, (int) $this->boxHeight);

        if (!$this->image) {
            throw new PsxDesignTextToImageConvertException('Cannot Initialize new GD image stream', PsxDesignTextToImageConvertException::FAILED_INIT_GD_STREAM);
        }

        return $this;
    }

    /**
     * Apply background for created image box.
     *
     * @return TextToLogoConverter
     *
     * @throws PsxDesignTextToImageConvertException
     */
    private function setBackgroundColor(): self
    {
        imagealphablending($this->image, false);
        $color = imagecolorallocatealpha($this->image, $this->backgroundColor['r'], $this->backgroundColor['g'], $this->backgroundColor['b'], $this->backgroundOpacity);
        imagefill($this->image, 0, 0, $color);
        imagesavealpha($this->image, true);

        return $this;
    }

    /**
     * @param int $logoSize
     * @param string $text
     * @param string $logoColor
     *
     * @return void
     *
     * @throws PsxDesignTextToImageConvertException
     */
    private function renderTextOnImage(int $logoSize, string $text, string $logoColor): void
    {
        $textColor = $this->getTextColor($logoColor);
        $this->setBackgroundColor();

        $coordinates = $this->getCoordinatesForText($logoSize, $text);

        $isTextAdded = imagettftext(
            $this->image,
            $logoSize,
            $this->textAngle,
            (int) $coordinates['x'],
            (int) $coordinates['y'],
            $textColor,
            $this->fontPath,
            $text);

        if (!$isTextAdded) {
            throw new PsxDesignTextToImageConvertException('Cannot add text into image', PsxDesignTextToImageConvertException::FAILED_ADD_TEXT);
        }
    }

    /**
     * Coordinates determine where should text start be rendered on the image
     *
     * @param int $logoSize
     * @param string $text
     *
     * @return array{x: float, y: float}
     */
    private function getCoordinatesForText(int $logoSize, string $text): array
    {
        $dimensions = imagettfbbox($logoSize, 0, $this->fontPath, $text);

        $ascent = abs($dimensions[7]);
        $descent = abs($dimensions[1]);
        $height = $ascent + $descent;

        $coordinates['x'] = ($this->boxWidth - $dimensions[2]) / 2;
        $coordinates['y'] = (($this->boxHeight / 2) - ($height / 2)) + $ascent;

        return $coordinates;
    }

    /**
     * Converts hex to rgb and returns color id
     *
     * @param string $logoColor
     *
     * @return int
     */
    private function getTextColor(string $logoColor): int
    {
        [$r, $g, $b] = sscanf($logoColor, '#%02x%02x%02x'); //converts hex to rgb

        return (int) imagecolorallocate($this->image, $r, $g, $b);
    }

    /**
     * Save created image into temporary folder
     *
     * @return string
     *
     * @throws PsxDesignTextToImageConvertException
     */
    private function createImage(): string
    {
        $path = _PS_TMP_IMG_DIR_ . self::TMP_IMAGE_NAME . self::EXTENSION;
        $isCreated = imagepng($this->image, $path);

        if (!$isCreated) {
            throw new PsxDesignTextToImageConvertException('Failed to convert the image', PsxDesignTextToImageConvertException::FAILED_TO_CONVERT);
        }

        return $path;
    }

    /**
     * Destroys image from memory
     *
     * @return void
     */
    private function destroyImageFromMemory(): void
    {
        imagedestroy($this->image);
    }
}
