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

namespace PrestaShop\Module\PsxDesign\Traits\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

trait UseActionThemeConfiguration
{
    /**
     * @return array
     */
    public function hookActionThemeConfiguration(): array
    {
        return [
            'theme' => 'classic',
            'customize_url' => $this->context->link->getAdminLink('AdminPsThemeCustoConfiguration'),
            'colors' => [
                [
                    'label' => $this->trans('Background', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$btn-primary-bg',
                    'variable_type' => 'scss_variable',
                    'value' => '#24b9d7',
                    'category' => 'main_button',
                ],
                [
                    'label' => $this->trans('Text', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$btn-primary-color',
                    'variable_type' => 'scss_variable',
                    'value' => '#ffffff',
                    'category' => 'main_button',
                ],
                [
                    'label' => $this->trans('Background', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$btn-secondary-bg',
                    'variable_type' => 'scss_variable',
                    'value' => '#f6f6f6',
                    'category' => 'secondary_button',
                ],
                [
                    'label' => $this->trans('Text', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$btn-secondary-color',
                    'variable_type' => 'scss_variable',
                    'value' => '#232323',
                    'category' => 'secondary_button',
                ],
                [
                    'label' => $this->trans('Body text', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$gray-darker',
                    'variable_type' => 'scss_variable',
                    'value' => '#232323',
                    'category' => 'content',
                ],
                [
                    'label' => $this->trans('Text', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$brand-primary',
                    'variable_type' => 'scss_variable',
                    'value' => '#24b9d7',
                    'category' => 'links',
                ],
                [
                    'label' => $this->trans('Background and discounted prices', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => '$brand-secondary',
                    'variable_type' => 'scss_variable',
                    'value' => '#f39d72',
                    'category' => 'discounts',
                ],
            ],
            'fonts' => [
                [
                    'label' => $this->trans('Main title', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h1, .h1',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 22,
                    'description' => $this->trans('The main title of a page has to use the Heading 1 format (e.g. Product page name).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Main title', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'headings',
                ],
                [
                    'label' => $this->trans('Section title', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h2, .h2',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 21,
                    'description' => $this->trans('Section titles mark the main sections of your page and use Heading 2 format (e.g. Popular products).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Section title', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'headings',
                ],
                [
                    'label' => $this->trans('Footer title', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h3, .h3',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 18,
                    'description' => $this->trans('Footer titles structure your footer and use Heading 3 format (e.g. Contact us).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Footer title', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'headings',
                ],
                [
                    'label' => $this->trans('Subcategory title', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h5, .h5',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 16,
                    'description' => $this->trans('Subcategory titles help structure your category page and use Heading 5 format (e.g. Men).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Subcategory title', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'headings',
                ],
                [
                    'label' => $this->trans('Product attributes', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h6, .h6',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 15,
                    'description' => $this->trans('Product attributes of your Category page use Heading 6 format (e.g. Size, Color). ', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Product attributes', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'headings',
                ],
                [
                    'label' => $this->trans('Body copy', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'p',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-400',
                    'size' => 14,
                    'description' => $this->trans('The body copy format is used for the main content in the pages (e.g. Product description).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Body copy', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'content',
                ],
                [
                    'label' => $this->trans('Links', [], 'Modules.Psxdesign.Admin'),
                    'variable_name' => 'h4, .h4',
                    'variable_type' => 'css_selector',
                    'font' => 'Manrope',
                    'style' => 'normal-700',
                    'size' => 18,
                    'description' => $this->trans('This is used for links in the page (e.g. All sale products).', [], 'Modules.Psxdesign.Admin'),
                    'placeholder' => $this->trans('Links', [], 'Modules.Psxdesign.Admin'),
                    'category' => 'content',
                ],
            ],
            'fonts_categories' => [
                [
                    'title' => $this->trans('Headings', [], 'Modules.Psxdesign.Admin'),
                    'description' => $this->trans('Headings are titles that help organize content on a page and can be used to create sections.', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'headings',
                ],
                [
                    'title' => $this->trans('Content', [], 'Modules.Psxdesign.Admin'),
                    'description' => $this->trans('Content refers to any information written within the pages. It is used to instruct, to provide descriptions, to explain the benefits of a product or highlight any feature.', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'content',
                ],
            ],
            'colors_categories' => [
                [
                    'title' => $this->trans('Main button', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'main_button',
                ],
                [
                    'title' => $this->trans('Secondary button', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'secondary_button',
                ],
                [
                    'title' => $this->trans('Content', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'content',
                ],
                [
                    'title' => $this->trans('Links', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'links',
                ],
                [
                    'title' => $this->trans('Discounts', [], 'Modules.Psxdesign.Admin'),
                    'key' => 'discounts',
                ],
            ],
        ];
    }
}
