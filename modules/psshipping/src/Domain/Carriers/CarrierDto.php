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

use Carrier;
use Language;
use ZoneCore;

if (!defined('_PS_VERSION_')) {
    exit();
}

class CarrierDto
{
    /** @var string Name */
    private $name;

    /** @var string URL with a '@' for */
    private $tackingUrl;

    /** @var bool */
    private $freeShipping;

    /** @var string[] */
    private $ranges;

    /** @var string Delay needed to deliver customer */
    private $delay;

    /** @var bool */
    private $active;

    /** @var bool True if carrier has been deleted (staying in database as deleted) */
    private $deleted;

    /** @var bool Behavior for out-of-range weights: true to disable carrier, false to apply the cost of the highest defined range */
    private $rangeBehavior;

    /** @var int maximum package width managed by the transporter */
    private $maxWidth;

    /** @var int maximum package height managed by the transporter */
    private $maxHeight;

    /** @var int maximum package deep managed by the transporter */
    private $maxDepth;

    /** @var int maximum package weight managed by the transporter */
    private $maxWeight;

    /** @var string */
    private $externalModuleName;

    public function __construct(
        string $name,
        string $tackingUrl,
        bool $freeShipping,
        bool $active,
        bool $deleted,
        bool $range_behavior,
        int $max_width,
        int $max_height,
        int $max_depth,
        int $max_weight,
        string $externalModuleName
    ) {
        $this->name = $name;
        $this->tackingUrl = $tackingUrl;
        $this->freeShipping = $freeShipping;
        $this->active = $active;
        $this->deleted = $deleted;
        $this->rangeBehavior = $range_behavior;
        $this->maxWidth = $max_width;
        $this->maxHeight = $max_height;
        $this->maxDepth = $max_depth;
        $this->maxWeight = $max_weight;
        $this->name = $name;
        $this->tackingUrl = $tackingUrl;
        $this->freeShipping = $freeShipping;
        $this->setTransitTimeWithLangs();
        $this->active = $active;
        $this->deleted = $deleted;
        $this->rangeBehavior = $range_behavior;
        $this->maxWidth = $max_width;
        $this->maxHeight = $max_height;
        $this->maxDepth = $max_depth;
        $this->maxWeight = $max_weight;
        $this->setShippingZones();
        $this->externalModuleName = $externalModuleName;
    }

    /**
     * @param Carrier $carrier
     *
     * @return CarrierDto
     */
    public static function toDomain(Carrier $carrier)
    {
        return new CarrierDto(
            $carrier->name,
            $carrier->url,
            $carrier->is_free,
            $carrier->active,
            $carrier->deleted,
            $carrier->range_behavior,
            $carrier->max_width,
            $carrier->max_height,
            $carrier->max_depth,
            $carrier->max_weight,
            $carrier->external_module_name
        );
    }

    /**
     * @param CarrierDto $carrierDto
     *
     * @return Carrier
     */
    public static function fromDomain(CarrierDto $carrierDto)
    {
        $carrier = new Carrier();

        $carrier->name = $carrierDto->name;
        $carrier->url = $carrierDto->tackingUrl;
        $carrier->is_free = $carrierDto->freeShipping;
        $carrier->delay = $carrierDto->delay;
        $carrier->active = $carrierDto->active;
        $carrier->deleted = $carrierDto->deleted;
        $carrier->range_behavior = $carrierDto->rangeBehavior;
        $carrier->max_width = $carrierDto->maxWidth;
        $carrier->max_height = $carrierDto->maxHeight;
        $carrier->max_depth = $carrierDto->maxDepth;
        $carrier->max_weight = $carrierDto->maxWeight;
        $carrier->external_module_name = $carrierDto->externalModuleName;
        $carrier->active = $carrierDto->active;
        $carrier->shipping_handling = false;

        return $carrier;
    }

    private function setShippingZones(): void
    {
        $zones = ZoneCore::getZones(true);

        foreach ($zones as $zone) {
            $this->ranges[] = $zone['id_zone'];
        }
    }

    private function setTransitTimeWithLangs(): void
    {
        $langs = Language::getLanguages(true);

        foreach ($langs as $lang) {
            if (!empty($lang['id_lang'])) {
                $this->delay[$lang['id_lang']] = '1-4 days';
            }
        }
    }

    /**
     * @return array<string>
     */
    public function getRanges()
    {
        return $this->ranges;
    }
}
