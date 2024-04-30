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

namespace PrestaShop\Module\FacetedSearch\Form\Attribute;

use PrestaShop\Module\FacetedSearch\Constraint\UrlSegment;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Translation\TranslatorComponent;
use Symfony\Component\Form\FormBuilderInterface;

class FormModifier
{
    /**
     * @var TranslatorComponent
     */
    private $translator;

    public function __construct(TranslatorComponent $translator)
    {
        $this->translator = $translator;
    }

    public function modify(FormBuilderInterface $formBuilder)
    {
        $invalidCharsHint = $this->translator->trans(
            'Invalid characters: <>;=#{}_',
            [],
            'Modules.Facetedsearch.Admin'
        );

        $urlTip = $this->translator->trans(
            'When the Faceted Search module is enabled, you can get more detailed URLs by choosing the word that best represent this attribute. By default, PrestaShop uses the attribute\'s name, but you can change that setting using this field.',
            [],
            'Modules.Facetedsearch.Admin'
        );
        $metaTitleTip = $this->translator->trans(
            'When the Faceted Search module is enabled, you can get more detailed page titles by choosing the word that best represent this attribute. By default, PrestaShop uses the attribute\'s name, but you can change that setting using this field.',
            [],
            'Modules.Facetedsearch.Admin'
        );

        $formBuilder
            ->add(
                'url_name',
                TranslatableType::class,
                [
                    'required' => false,
                    'label' => $this->translator->trans('URL', [], 'Modules.Facetedsearch.Admin'),
                    'help' => $urlTip . ' ' . $invalidCharsHint,
                    'options' => [
                        'constraints' => [
                            new UrlSegment([
                                'message' => $this->translator->trans('%s is invalid.', [], 'Admin.Notifications.Error'),
                            ]),
                        ],
                    ],
                ]
            )
            ->add(
                'meta_title',
                TranslatableType::class,
                [
                    'required' => false,
                    'label' => $this->translator->trans('Meta title', [], 'Modules.Facetedsearch.Admin'),
                    'help' => $metaTitleTip,
                ]
            )
        ;
    }
}
