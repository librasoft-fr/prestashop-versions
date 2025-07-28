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

namespace PrestaShop\Module\PsxDesign\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PrestaShop\Module\PsxDesign\DTO\PsxDesignFontData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignFonts;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignFontsException;

class PsxdesignFontsRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $currentThemeName;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $currentThemeName
    ) {
        $this->entityManager = $entityManager;
        $this->currentThemeName = $currentThemeName;
    }

    /**
     * @param PsxDesignFontData $fontsData
     *
     * @return PsxdesignFonts
     *
     * @throws PsxDesignFontsException
     */
    public function upsertFontData(PsxDesignFontData $fontsData): PsxdesignFonts
    {
        $font = $this->getFontDataById($fontsData->getId());

        if (empty($font)) {
            $font = new PsxdesignFonts();
            $font->setVariableName($fontsData->getVariableName())
                ->setVariableType($fontsData->getVariableType())
                ->setThemeName($this->currentThemeName);
        }

        $font->setFont($fontsData->getFont())
            ->setStyle($fontsData->getStyle())
            ->setSize($fontsData->getSize());

        $this->entityManager->persist($font);

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new PsxDesignFontsException('SQL error. Failed to upsert new font.', PsxDesignFontsException::FAILED_TO_UPSERT_FONT);
        }

        return $font;
    }

    /**
     * @param int $fontId
     *
     * @return PsxdesignFonts|null
     */
    public function getFontDataById(int $fontId): ?PsxdesignFonts
    {
        return $this->entityManager->getRepository(PsxdesignFonts::class)->findOneBy(['id' => $fontId]);
    }

    /**
     * @param string $themeName
     *
     * @return PsxdesignFonts[]|null
     */
    public function getFontsByThemeName(string $themeName): ?array
    {
        return $this->entityManager->getRepository(PsxdesignFonts::class)->findBy(['themeName' => $themeName]);
    }

    /**
     * @param int $fontId
     *
     * @return bool
     *
     * @throws PsxDesignFontsException
     */
    public function deleteFontData(int $fontId): bool
    {
        $font = $this->getFontDataById($fontId);

        if (empty($font)) {
            throw new PsxDesignFontsException('SQL error. Failed to delete font data.', PsxDesignFontsException::FAILED_TO_DELETE_FONT);
        }

        $this->entityManager->remove($font);
        $this->entityManager->flush();

        return true;
    }
}
