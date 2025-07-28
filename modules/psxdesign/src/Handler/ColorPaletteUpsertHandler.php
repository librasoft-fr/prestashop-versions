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

namespace PrestaShop\Module\PsxDesign\Handler;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PrestaShop\Module\PsxDesign\Builder\ColorPaletteBuilder;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColor;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColorsPalette;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignCompilerException;
use PrestaShop\Module\PsxDesign\Processor\ColorsStylesheetsProcessor;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignColorRepository;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignColorsPaletteRepository;
use PrestaShop\Module\PsxDesign\Validator\ColorPaletteValidator;

class ColorPaletteUpsertHandler
{
    /**
     * @var ColorPaletteValidator
     */
    private $colorPaletteValidator;

    /**
     * @var PsxdesignColorRepository
     */
    private $colorRepository;

    /**
     * @var PsxdesignColorsPaletteRepository
     */
    private $colorsPaletteRepository;

    /**
     * @var ColorsStylesheetsProcessor
     */
    private $stylesheetsProcessor;

    /**
     * @var ColorPaletteBuilder
     */
    private $colorPaletteBuilder;

    /**
     * @var ThemeConfigurationProvider
     */
    private $themeConfigurationProvider;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ColorPaletteValidator $colorPaletteValidator,
        PsxdesignColorRepository $colorRepository,
        PsxdesignColorsPaletteRepository $colorsPaletteRepository,
        ColorsStylesheetsProcessor $stylesheetsProcessor,
        ColorPaletteBuilder $colorPaletteBuilder,
        ThemeConfigurationProvider $themeConfigurationProvider,
        EntityManagerInterface $entityManager
    ) {
        $this->colorPaletteValidator = $colorPaletteValidator;
        $this->colorRepository = $colorRepository;
        $this->colorsPaletteRepository = $colorsPaletteRepository;
        $this->stylesheetsProcessor = $stylesheetsProcessor;
        $this->colorPaletteBuilder = $colorPaletteBuilder;
        $this->themeConfigurationProvider = $themeConfigurationProvider;
        $this->entityManager = $entityManager;
    }

    /**
     * Add or updates color palette depends on if id is received from front end
     *
     * @param PsxDesignColorData[] $colors
     *
     * @return PsxdesignColor[]
     *
     * @throws PsxDesignColorsException
     * @throws PsxDesignCompilerException
     */
    public function upsertColors(array $colors): array
    {
        $this->colorPaletteValidator->validateColors($colors);

        $colorsPalette = $this->colorsPaletteRepository->getColorPaletteById(end($colors)->getPaletteId());

        $this->entityManager->beginTransaction();
        try {
            $this->deleteUnusedColors();

            if (!$colorsPalette || $colorsPalette->isDefault()) {
                $colorsPalette = $this->createCurrentThemeColorPalettes($colors);
            }

            $upsertedColors = $this->upsertColorsTable($colors, $colorsPalette);

            // we need to refresh color palette, so it has upserted colors inside
            $this->entityManager->refresh($colorsPalette);

            // we need updated colors palette so we can pass the colors into stylesheet creation
            $updatedColorPalette = $this->colorsPaletteRepository->getColorPaletteById($colorsPalette->getId());
            // if palette we just updated is active we want to process stylesheet creation
            if ($updatedColorPalette && $updatedColorPalette->isActive()) {
                $this->stylesheetsProcessor->processStylesheetCreation();
            }

            $this->entityManager->commit();
        } catch (Exception $exception) {
            //we want to catch all exceptions and rollback database in case of error.
            $this->entityManager->rollback();
            throw $exception;
        }

        return $upsertedColors;
    }

    /**
     * @param array $colors
     * @param PsxdesignColorsPalette $palette
     *
     * @return PsxdesignColor[]
     *
     * @throws PsxDesignColorsException
     */
    private function upsertColorsTable(array $colors, PsxdesignColorsPalette $palette): array
    {
        $upsertedColors = [];

        foreach ($colors as $color) {
            $upsertedColors[] = $this->colorRepository->upsertColor($color, $palette);
        }

        return $upsertedColors;
    }

    /**
     * @param PsxDesignColorData[] $colors
     *
     * @return PsxdesignColorsPalette
     *
     * @throws PsxDesignColorsException
     */
    private function createCurrentThemeColorPalettes(array $colors): PsxdesignColorsPalette
    {
        // we need to build customizable color palette
        $buildedColorsPalette = $this->colorPaletteBuilder->buildCurrentThemeColorsPalette(false, $colors);

        // add new palette to the database table
        $newColorPalette = $this->colorsPaletteRepository->upsertColorPalette($buildedColorsPalette);

        // activate new palette
        $this->colorsPaletteRepository->activateColorPalette($newColorPalette);

        // adding default palette so merchant can not edit it later
        if (!$this->colorsPaletteRepository->isCurrentThemeDefaultPaletteExist()) {
            $this->colorsPaletteRepository->upsertColorPalette($this->colorPaletteBuilder->buildCurrentThemeColorsPalette(true));
        }

        return $newColorPalette;
    }

    /**
     * Delete unused colors in case theme creators decides to change configurations
     *
     * @return bool
     *
     * @throws PsxDesignColorsException
     */
    private function deleteUnusedColors(): bool
    {
        $unusedColors = $this->themeConfigurationProvider->colors->getUnusedColorsData();
        $isColorDeleted = false;

        foreach ($unusedColors as $color) {
            $this->colorRepository->deleteColorData($color->getId());
            $isColorDeleted = true;
        }

        return $isColorDeleted;
    }
}
