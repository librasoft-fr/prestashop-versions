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
use PrestaShop\Module\PsxDesign\DTO\PsxDesignFontData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignFonts;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignApiException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;
use PrestaShop\Module\PsxDesign\Processor\FontStylesheetsProcessor;
use PrestaShop\Module\PsxDesign\Provider\ThemeConfiguration\ThemeConfigurationProvider;
use PrestaShop\Module\PsxDesign\Repository\PsxdesignFontsRepository;
use Throwable;

class FontUpsertHandler
{
    /**
     * @var PsxdesignFontsRepository
     */
    private $fontsRepository;

    /**
     * @var FontStylesheetsProcessor
     */
    private $fontProcessor;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ThemeConfigurationProvider
     */
    private $themeConfigurationProvider;

    public function __construct(
        PsxdesignFontsRepository $fontsRepository,
        FontStylesheetsProcessor $fontProcessor,
        EntityManagerInterface $entityManager,
        ThemeConfigurationProvider $themeConfigurationProvider
    ) {
        $this->fontsRepository = $fontsRepository;
        $this->fontProcessor = $fontProcessor;
        $this->entityManager = $entityManager;
        $this->themeConfigurationProvider = $themeConfigurationProvider;
    }

    /**
     * @param PsxDesignFontData[] $fonts
     *
     * @return PsxdesignFonts[]
     *
     * @throws Throwable
     * @throws PsxDesignApiException
     * @throws PsxDesignFontsException
     */
    public function upsertFonts(array $fonts): array
    {
        $upsertedFonts = [];

        try {
            $this->entityManager->beginTransaction();
            $this->deleteUnusedFonts();

            foreach ($fonts as $font) {
                $upsertedFonts[] = $this->fontsRepository->upsertFontData($font);
            }

            $this->fontProcessor->processStylesheetCreation();
            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }

        return $upsertedFonts;
    }

    /**
     * Delete unused fonts in case theme creators decides to change configurations
     *
     * @return void
     *
     * @throws PsxDesignFontsException
     */
    private function deleteUnusedFonts(): void
    {
        $unusedFonts = $this->themeConfigurationProvider->fonts->getUnusedFontsData();

        foreach ($unusedFonts as $font) {
            $this->fontsRepository->deleteFontData($font->getId());
        }
    }
}
