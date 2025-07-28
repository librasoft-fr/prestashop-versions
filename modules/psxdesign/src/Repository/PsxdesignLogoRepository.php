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
use PrestaShop\Module\PsxDesign\DTO\PsxDesignLogoTextData;
use PrestaShop\Module\PsxDesign\Entity\PsxdesignLogo;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignException;
use PrestaShop\Module\PsxDesign\Exception\PsxDesignLogoImportException;

class PsxdesignLogoRepository
{
    private const IMAGE_TYPE = 'image';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $logoDestination
     * @param string $uploadType
     * @param bool $active
     * @param bool $useHeader
     *
     * @return int
     *
     * @throws PsxDesignLogoImportException
     */
    public function insertLogoImage(string $logoDestination, string $uploadType, bool $active = true, bool $useHeader = false): int
    {
        $logo = new PsxDesignLogo();

        $logo->setLogoType($uploadType)
            ->setLogoDestination($logoDestination)
            ->setLogoType(self::IMAGE_TYPE)
            ->setStyle('')
            ->setColor('')
            ->setFont('')
            ->setFontSize(0)
            ->setText('')
            ->setActive($active)
            ->setUseHeaderLogo($useHeader);

        try {
            $this->entityManager->persist($logo);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new PsxDesignLogoImportException('Inserting logo image failed', PsxDesignLogoImportException::INSERT_LOGO_FAILED);
        }

        return $logo->getId();
    }

    /**
     * @param string $logoDestination
     * @param string $uploadType
     * @param bool $active
     * @param bool $useHeader
     *
     * @return int
     *
     * @throws PsxDesignException
     */
    public function updateLogoImage(string $logoDestination, string $uploadType, bool $active = true, bool $useHeader = false): int
    {
        /** @var PsxdesignLogo|null $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => $logoDestination, 'active' => $active]);

        if (empty($logo)) {
            return $this->insertLogoImage($logoDestination, $uploadType);
        }

        $logo->setLogoType($uploadType)
            ->setLogoDestination($logoDestination)
            ->setLogoType(self::IMAGE_TYPE)
            ->setStyle('')
            ->setColor('')
            ->setFont('')
            ->setFontSize(0)
            ->setText('')
            ->setActive($active)
            ->setUseHeaderLogo($useHeader);

        $this->entityManager->persist($logo);
        $this->entityManager->flush();

        return $logo->getId();
    }

    /**
     * @return PsxdesignLogo[]
     */
    public function getActiveLogos(): array
    {
        return $this->entityManager->getRepository(PsxdesignLogo::class)->findBy(['active' => true]);
    }

    public function getHeaderLogo(): ?PsxdesignLogo
    {
        /* @var PsxdesignLogo|null $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => 'header', 'active' => true]);

        if (empty($logo)) {
            return null;
        }

        return $logo;
    }

    /**
     * @return PsxdesignLogo|null
     */
    public function getInvoiceLogo(): ?PsxdesignLogo
    {
        /* @var PsxdesignLogo|null $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => 'invoice', 'active' => true]);

        if (empty($logo)) {
            return null;
        }

        return $logo;
    }

    /**
     * @return PsxdesignLogo|null
     */
    public function getEmailLogo(): ?PsxdesignLogo
    {
        /* @var PsxdesignLogo $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => 'email', 'active' => true]);

        if (empty($logo)) {
            return null;
        }

        return $logo;
    }

    /**
     * @param string $uploadType
     * @param bool $active
     * @param bool $useHeader
     *
     * @return int
     *
     * @throws PsxDesignLogoImportException
     */
    public function insertLogoTextImage(PsxDesignLogoTextData $logoTextData, string $uploadType = 'text', bool $active = true, bool $useHeader = false): int
    {
        $logo = new PsxDesignLogo();

        $logo
            ->setLogoDestination($logoTextData->getDestination()->getValue())
            ->setLogoType($uploadType)
            ->setText($logoTextData->getText())
            ->setFontSize($logoTextData->getSize())
            ->setColor($logoTextData->getColor())
            ->setFont($logoTextData->getFamily())
            ->setStyle($logoTextData->getStyle()->getValue())
            ->setActive($active)
            ->setUseHeaderLogo($useHeader);

        try {
            $this->entityManager->persist($logo);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new PsxDesignLogoImportException('Inserting logo image failed', PsxDesignLogoImportException::INSERT_LOGO_FAILED);
        }

        return $logo->getId();
    }

    /**
     * @param string $uploadType
     * @param bool $active
     * @param bool $useHeader
     *
     * @return int
     *
     * @throws PsxDesignException
     */
    public function updateLogoTextImage(PsxDesignLogoTextData $logoTextData, string $uploadType = 'text', bool $active = true, bool $useHeader = false): int
    {
        /** @var PsxdesignLogo|null $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => $logoTextData->getDestination()->getValue(), 'active' => $active]);

        if (empty($logo)) {
            return $this->insertLogoTextImage($logoTextData, $uploadType);
        }

        $logo
            ->setLogoDestination($logoTextData->getDestination()->getValue())
            ->setLogoType($uploadType)
            ->setText($logoTextData->getText())
            ->setFontSize($logoTextData->getSize())
            ->setColor($logoTextData->getColor())
            ->setFont($logoTextData->getFamily())
            ->setStyle($logoTextData->getStyle()->getValue())
            ->setActive($active)
            ->setUseHeaderLogo($useHeader);

        $this->entityManager->persist($logo);
        $this->entityManager->flush();

        return $logo->getId();
    }

    /**
     * @return void
     */
    public function disableAllUseHeader(): void
    {
        $logos = $this->getActiveLogos();

        /** @var array<PsxdesignLogo> $logos */
        foreach ($logos as $logo) {
            $this->entityManager->persist($logo);
            $logo->setUseHeaderLogo(false);
        }
        $this->entityManager->flush();
    }

    public function useSameAsHeaderLogo(string $destination)
    {
        /** @var PsxdesignLogo $headerLogo */
        $headerLogo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => 'header']);

        /** @var PsxdesignLogo|null $logo */
        $logo = $this->entityManager->getRepository(PsxdesignLogo::class)->findOneBy(['logoDestination' => $destination]);

        if (empty($logo)) {
            $logo = new PsxdesignLogo();
        }

        $logo
            ->setLogoType($headerLogo->getLogoType())
            ->setLogoDestination($destination)
            ->setText($headerLogo->getText())
            ->setFont($headerLogo->getFont())
            ->setUseHeaderLogo(true)
            ->setFontSize($headerLogo->getFontSize())
            ->setColor($headerLogo->getColor())
            ->setStyle($headerLogo->getStyle())
            ->setActive(true);

        $this->entityManager->persist($logo);
        $this->entityManager->flush();
    }
}
