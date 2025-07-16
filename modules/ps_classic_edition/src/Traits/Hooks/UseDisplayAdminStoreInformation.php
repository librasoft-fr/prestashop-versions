<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsClassicEdition\Traits\Hooks;

trait UseDisplayAdminStoreInformation {
    public function hookDisplayAdminStoreInformation(): string
    {
        $metadataFile = _PS_ROOT_DIR_ . '/app/metadata.json';
        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
            if (!empty($metadata['distribution']) && !empty($metadata['distributionVersion'])) {
                return <<<END
                <p class="mb-0">
                    <strong>{$this->trans('PrestaShop distribution:', [], 'Modules.Classicedition.Admin')}</strong> {$metadata['distribution']}
                </p>
                <p class="mb-0">
                    <strong>{$this->trans('PrestaShop distribution version:', [], 'Modules.Classicedition.Admin')}</strong> {$metadata['distributionVersion']}
                </p>
                END;
            }
        }

        return '';
    }
}
