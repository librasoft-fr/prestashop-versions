<?php

/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\Form\FormChoiceAttributeProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Entity\Repository\AttributeGroupRepository;

/**
 * Class AttributeGroupChoiceProvider provides attribute group choices and choices attributes.
 */
final class AttributeGroupChoiceProvider implements FormChoiceProviderInterface, FormChoiceAttributeProviderInterface
{
    /**
     * @var \PrestaShopBundle\Entity\AttributeGroup[]
     */
    private $attributeGroups;

    /**
     * @var array
     */
    private $attributeGroupsChoices;

    /**
     * @var array<string, array{data-iscolorgroup: int}>
     */
    private $attributeGroupsChoicesAttributes;

    /**
     * @param AttributeGroupRepository $attributeGroupRepository
     * @param int $langId
     * @param int $shopId
     */
    public function __construct(
        private readonly AttributeGroupRepository $attributeGroupRepository,
        private readonly int $langId,
        private readonly int $shopId,
    ) {
    }

    /**
     * Get attribute groups choices
     *
     * @return array<string, int>
     */
    public function getChoices(): array
    {
        $this->setAttributeGroups();

        return $this->attributeGroupsChoices;
    }

    /**
     * Get attribute groups choices attributes
     *
     * @return array<string, array{data-iscolorgroup: int}>
     */
    public function getChoicesAttributes(): array
    {
        $this->setAttributeGroups();

        return $this->attributeGroupsChoicesAttributes;
    }

    /**
     * Set attribute groups to return in getChoices() and getChoicesAttributes()
     *
     * @return void
     */
    private function setAttributeGroups(): void
    {
        if (null === $this->attributeGroupsChoicesAttributes || null === $this->attributeGroupsChoices) {
            if (null === $this->attributeGroups) {
                $this->attributeGroups = $this->attributeGroupRepository->findByLangAndShop($this->langId, $this->shopId);
            }

            $this->attributeGroupsChoices = [];
            $this->attributeGroupsChoicesAttributes = [];

            foreach ($this->attributeGroups as $attributeGroup) {
                /*
                 * No need to filter duplicates like FormChoiceFormatter::formatFormChoices
                 * does since attributeGroupId has a PRIMARY key.
                */
                /** @var \Doctrine\Common\Collections\Collection<\PrestaShopBundle\Entity\AttributeGroupLang> $attributeGroupLang */
                $attributeGroupLang = $attributeGroup->getAttributeGroupLangs();
                $attributeGroupFormattedName = sprintf('%s (#%d)', $attributeGroupLang->first()?->getName(), $attributeGroup->getId());

                $this->attributeGroupsChoices[$attributeGroupFormattedName] = $attributeGroup->getId();

                if (true === $attributeGroup->getIsColorGroup()) {
                    $this->attributeGroupsChoicesAttributes[$attributeGroupFormattedName]['data-iscolorgroup'] = $attributeGroup->getId();
                }
            }

            ksort($this->attributeGroupsChoices);
        }
    }
}
