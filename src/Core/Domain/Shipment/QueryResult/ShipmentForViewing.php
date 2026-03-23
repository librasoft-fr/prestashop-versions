<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult;

use PrestaShop\PrestaShop\Core\Domain\Address\QueryResult\ShippingAdressSummary;
use PrestaShop\PrestaShop\Core\Domain\Carrier\QueryResult\CarrierSummary;

class ShipmentForViewing
{
    private int $id;
    private ?string $trackingNumber;
    private CarrierSummary $carrierSummary;
    private ShippingAdressSummary $shippingAdressSummary;

    public function __construct(
        int $id,
        ?string $trackingNumber,
        CarrierSummary $carrierSummary,
        ShippingAdressSummary $shippingAdressSummary
    ) {
        $this->id = $id;
        $this->trackingNumber = $trackingNumber;
        $this->carrierSummary = $carrierSummary;
        $this->shippingAdressSummary = $shippingAdressSummary;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getCarrierSummary(): CarrierSummary
    {
        return $this->carrierSummary;
    }

    public function setCarrierSummary(CarrierSummary $carrierSummary): void
    {
        $this->carrierSummary = $carrierSummary;
    }

    public function getShippingAdressSummary(): ShippingAdressSummary
    {
        return $this->shippingAdressSummary;
    }

    public function setShippingAdressSummary(ShippingAdressSummary $shippingAdressSummary): void
    {
        $this->shippingAdressSummary = $shippingAdressSummary;
    }
}
