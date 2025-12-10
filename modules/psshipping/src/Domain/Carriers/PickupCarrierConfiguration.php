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

namespace PrestaShop\Module\Psshipping\Domain\Carriers;

use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PickupCarrierConfiguration extends CarrierConfiguration
{
    const DEFAULT_CONFIGURATION = [
        'name' => 'Delivery point',
        'freeShipping' => false,
        'active' => false,
        'deleted' => false,
        'range_behavior' => false,
        'max_width' => 0,
        'max_height' => 0,
        'max_depth' => 0,
        'max_weight' => 20,
        'grade' => 5,
        'external_module_name' => Psshipping::MODULE_TECHNICAL_NAME,
    ];

    /**
     * @return CarrierDto
     */
    public function transform()
    {
        return new CarrierDto(
            self::DEFAULT_CONFIGURATION['name'],
            $this->mbe_tracking_url,
            self::DEFAULT_CONFIGURATION['freeShipping'],
            self::DEFAULT_CONFIGURATION['active'],
            self::DEFAULT_CONFIGURATION['deleted'],
            self::DEFAULT_CONFIGURATION['range_behavior'],
            self::DEFAULT_CONFIGURATION['max_width'],
            self::DEFAULT_CONFIGURATION['max_height'],
            self::DEFAULT_CONFIGURATION['max_depth'],
            self::DEFAULT_CONFIGURATION['max_weight'],
            self::DEFAULT_CONFIGURATION['grade'],
            self::DEFAULT_CONFIGURATION['external_module_name'],
            CarrierService::CARRIERS_PICKUP
        );
    }
}
