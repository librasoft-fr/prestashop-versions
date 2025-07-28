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
use PrestaShop\Module\PsxDesign\DTO\PsxDesignColorData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColor as PsxdesignColorEntity;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignColorsPalette;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignColorsException;

class PsxdesignColorRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param PsxDesignColorData $color
     *
     * @return PsxdesignColorEntity
     *
     * @throws PsxDesignColorsException
     */
    public function upsertColor(PsxDesignColorData $color, PsxdesignColorsPalette $colorPalette): PsxdesignColorEntity
    {
        $colorEntity = $this->getColorById($color->getId());

        if (!$colorEntity) {
            $colorEntity = new PsxdesignColorEntity();
            $colorEntity
                ->setVariableName($color->getVariableName())
                ->setVariableType($color->getVariableType())
                ->setPalette($colorPalette);
        }

        $colorEntity->setValue($color->getValue());

        try {
            $this->entityManager->persist($colorEntity);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new PsxDesignColorsException('SQL error, the color can not be added.', PsxDesignColorsException::FAILED_TO_ADD_COLOR_PALETTE);
        }

        return $colorEntity;
    }

    /**
     * @param int $id
     *
     * @return PsxdesignColorEntity|null
     */
    public function getColorById(int $id): ?PsxdesignColorEntity
    {
        return $this->entityManager->getRepository(PsxdesignColorEntity::class)->findOneBy(['id' => $id]);
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws PsxDesignColorsException
     */
    public function deleteColorData(int $id): bool
    {
        $color = $this->getColorById($id);

        if (empty($id)) {
            throw new PsxDesignColorsException('SQL error. Failed to delete color data.', PsxDesignColorsException::FAILED_TO_DELETE_COLOR);
        }

        $this->entityManager->remove($color);
        $this->entityManager->flush();

        return true;
    }
}
