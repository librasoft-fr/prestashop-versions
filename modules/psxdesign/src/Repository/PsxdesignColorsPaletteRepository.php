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
use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorPalette;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColorsPalette as PsxdesignColorsPaletteEntity;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;

class PsxdesignColorsPaletteRepository
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
     * @return PsxdesignColorsPaletteEntity|null
     */
    public function getActiveColorsPaletteByThemeName(string $themeName): ?PsxdesignColorsPaletteEntity
    {
        return $this->entityManager->getRepository(PsxdesignColorsPaletteEntity::class)->findOneBy(['theme' => $themeName, 'active' => 1]);
    }

    /**
     * @param PsxDesignColorPalette $colorPalette
     *
     * @return PsxdesignColorsPaletteEntity
     *
     * @throws PsxDesignColorsException
     */
    public function upsertColorPalette(PsxDesignColorPalette $colorPalette): PsxdesignColorsPaletteEntity
    {
        $colorPaletteEntity = $this->getColorPaletteById($colorPalette->getId());

        if (!$colorPaletteEntity) {
            $colorPaletteEntity = new PsxdesignColorsPaletteEntity();
            $colorPaletteEntity
                ->setDefault($colorPalette->isDefault())
                ->setTheme($colorPalette->getTheme());
        }

        $colorPaletteEntity
            ->setName($colorPalette->getName());

        try {
            $this->entityManager->persist($colorPaletteEntity);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new PsxDesignColorsException('SQL error, the color palette can not be added.', PsxDesignColorsException::FAILED_TO_ADD_COLOR_PALETTE);
        }

        return $colorPaletteEntity;
    }

    /**
     * @param int $id
     *
     * @return PsxdesignColorsPaletteEntity|null
     */
    public function getColorPaletteById(int $id): ?PsxdesignColorsPaletteEntity
    {
        return $this->entityManager->getRepository(PsxdesignColorsPaletteEntity::class)->findOneBy(['id' => $id]);
    }

    /**
     * @param PsxdesignColorsPaletteEntity $paletteToActivate
     *
     * @return PsxdesignColorsPaletteEntity
     *
     * @throws PsxDesignColorsException
     */
    public function activateColorPalette(PsxdesignColorsPaletteEntity $paletteToActivate): PsxdesignColorsPaletteEntity
    {
        $currentActivePalette = $this->entityManager->getRepository(PsxdesignColorsPaletteEntity::class)->findOneBy(['active' => 1, 'theme' => $this->currentThemeName]);

        try {
            if ($currentActivePalette) {
                $currentActivePalette->setActive(false);
                $this->entityManager->persist($currentActivePalette);
            }

            $paletteToActivate->setActive(true);

            $this->entityManager->persist($paletteToActivate);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new PsxDesignColorsException('SQL error, the color palette can not be added.', PsxDesignColorsException::FAILED_TO_ADD_COLOR_PALETTE);
        }

        return $paletteToActivate;
    }

    public function isCurrentThemeDefaultPaletteExist(): bool
    {
        return (bool) $this->entityManager->getRepository(PsxdesignColorsPaletteEntity::class)->findOneBy(['default' => 1, 'theme' => $this->currentThemeName]);
    }

    /**
     * @param string $themeName
     *
     * @return PsxdesignColorsPaletteEntity[]
     */
    public function getColorPalettesByThemeName(string $themeName): array
    {
        return $this->entityManager->getRepository(PsxdesignColorsPaletteEntity::class)->findBy(['theme' => $themeName]);
    }
}
