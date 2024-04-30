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

namespace PrestaShop\Module\PsxDesign\Provider;

if (!defined('_PS_VERSION_')) {
    exit;
}

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PrestaShop\Module\PsxDesign\Config\PsxDesignConfig;
use PrestaShop\Module\PsxDesign\DTO\ThemeConfiguration\Font\PsxDesignFontConfiguration;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignTextToImageConvertException;
use PrestaShop\Module\PsxDesign\Utility\DirectoryUtility;
use PrestaShop\Module\PsxDesign\VO\Font\FontStyle;
use PrestaShop\Module\PsxDesign\VO\Logo\LogoTextStyle;
use RuntimeException;
use Tools;

class FontDataProvider
{
    private const BASE_URL = 'https://fonts.googleapis.com/css2?';
    private const STATUS_SUCCESS_CODE = 200;
    public const TEMPORARY_FONT_NAME = 'tmpFont.ttf';

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $modulePath;

    public function __construct(
        Client $httpClient,
        string $modulePath
    ) {
        $this->httpClient = $httpClient;
        $this->modulePath = $modulePath;
    }

    /**
     * Get font from google apis
     *
     * @param string $fontFamily
     * @param string $fontText
     * @param LogoTextStyle $fontStyle
     *
     * @return string
     *
     * @throws PsxDesignApiException
     */
    public function getFontContent(string $fontFamily, string $fontText, LogoTextStyle $fontStyle): string
    {
        try {
            $response = $this->httpClient->get(
                self::BASE_URL .
                'family=' . $fontFamily .
                ':' . $fontStyle->formatStyleForApiCall() .
                '&text=' . $fontText
            );
        } catch (GuzzleException $e) {
            throw new PsxDesignApiException($e->getMessage(), PsxDesignApiException::FAILED_FETCH_FONT);
        }

        if ($response->getStatusCode() !== self::STATUS_SUCCESS_CODE) {
            throw new PsxDesignApiException('Failed to fetch font from google apis', PsxDesignApiException::FAILED_FETCH_FONT);
        }

        return $response->getBody()->getContents();
    }

    /**
     * Parse url from response string and save received content into temporary folder
     *
     * @param string $response
     *
     * @return void
     */
    public function saveFontDataIntoTemporaryFolder(string $response): void
    {
        $tmpDirPath = $this->modulePath . PsxDesignConfig::TMP_DIR_NAME;
        try {
            DirectoryUtility::createDirIfNotExist($tmpDirPath);
        } catch (RuntimeException $e) {
            throw new PsxDesignTextToImageConvertException('Failed to create temporary directory', PsxDesignTextToImageConvertException::FAILED_CREATE_TMP_DIR);
        }

        preg_match("/url\((.*?)\)/", $response, $url);
        $fontData = Tools::file_get_contents($url[1]);
        file_put_contents($tmpDirPath . DIRECTORY_SEPARATOR . self::TEMPORARY_FONT_NAME, $fontData);
    }

    /**
     * @return array{name: string, code: string, variant: array{normal: string, italic: string}}
     */
    public function provideDefaultFonts(): array
    {
        $font = Tools::file_get_contents($this->modulePath . 'default_fonts.json');

        return json_decode($font, true);
    }

    /**
     * @return array{normal: array<string, string>, italic: array<string, string>}
     */
    public function provideFontsVariants(): array
    {
        $font_variants = Tools::file_get_contents($this->modulePath . 'font_variants.json');

        return json_decode($font_variants, true);
    }

    /**
     * Function to validate url with api call
     *
     * @return void
     *
     * @throws PsxDesignApiException
     */
    public function validateUrl(string $url): void
    {
        try {
            $response = $this->httpClient->get($url);
        } catch (GuzzleException $e) {
            throw new PsxDesignApiException($e->getMessage(), PsxDesignApiException::FAILED_FETCH_FONT);
        }

        if ($response->getStatusCode() !== self::STATUS_SUCCESS_CODE) {
            throw new PsxDesignApiException('Failed to fetch font from google apis', PsxDesignApiException::FAILED_FETCH_FONT);
        }
    }

    /**
     * @param PsxDesignFontConfiguration[] $fonts
     *
     * @return string
     */
    public function buildFontUrl(array $fonts): string
    {
        $url = '';
        $counter = 0;

        foreach ($fonts as $font) {
            if ($counter !== 0) {
                $url .= '&';
            }

            $fontStyle = new FontStyle($font->getStyle());
            ++$counter;

            $url .= 'family=' . $font->getFont() .
                ':' . $fontStyle->formatStyleUrl();
        }

        return self::BASE_URL . $url;
    }
}
