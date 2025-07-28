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

if (!defined('_PS_VERSION_')) {
    exit();
}

class PickupCarrierConfiguration implements CarrierConfiguration
{
    /** @var string mbe tracking url */
    private $mbe_tracking_url;

    public function __construct(string $mbe_tracking_url)
    {
        $this->mbe_tracking_url = $mbe_tracking_url;
    }

    const DEFAULT_CONFIGURATION = [
        'name' => 'Pickup delivery',
        'freeShipping' => false,
        'active' => false,
        'deleted' => false,
        'range_behavior' => false,
        'max_width' => 0,
        'max_height' => 0,
        'max_depth' => 0,
        'max_weight' => 20,
        'external_module_name' => 'psshipping_pickup',
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
            self::DEFAULT_CONFIGURATION['external_module_name']
        );
    }
}
