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

namespace PrestaShop\Module\Psshipping\Domain\Orders;

if (!defined('_PS_VERSION_')) {
    exit();
}

class OrdersDto
{
    /** @var int */
    private $orderId;
    /** @var string */
    private $shippingDate;
    /** @var string */
    private $orderStatus;
    /** @var string */
    private $orderStatusBadgeColor;
    /** @var string */
    private $shippingId;
    /** @var string */
    private $shippingService;
    /** @var int */
    private $shippingCost;
    /** @var string */
    private $orderDetailLink;
    /** @var string */
    private $currency;

    /**
     * Get the value of orderDetailLink
     */
    public function getOrderDetailLink(): string
    {
        return $this->orderDetailLink;
    }

    /**
     * Set the value of orderDetailLink
     */
    public function setOrderDetailLink(string $orderDetailLink): self
    {
        $this->orderDetailLink = $orderDetailLink;

        return $this;
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Set the value of orderId
     */
    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get the value of shippingDate
     */
    public function getShippingDate(): string
    {
        return $this->shippingDate;
    }

    /**
     * Set the value of shippingDate
     */
    public function setShippingDate(string $shippingDate): self
    {
        $this->shippingDate = $shippingDate;

        return $this;
    }

    /**
     * Get the value of orderStatus
     */
    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    /**
     * Set the value of orderStatus
     */
    public function setOrderStatus(string $orderStatus): self
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Get the value of shippingId
     */
    public function getShippingId(): string
    {
        return $this->shippingId;
    }

    /**
     * Set the value of shippingId
     */
    public function setShippingId(string $shippingId): self
    {
        $this->shippingId = $shippingId;

        return $this;
    }

    /**
     * Get the value of shippingService
     */
    public function getShippingService(): string
    {
        return $this->shippingService;
    }

    /**
     * Set the value of shippingService
     */
    public function setShippingService(string $shippingService): self
    {
        $this->shippingService = $shippingService;

        return $this;
    }

    /**
     * Get the value of shippingCost
     *
     * @return int
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * Set the value of shippingCost
     */
    public function setShippingCost(int $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    /**
     * Get the value of currency
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set the value of currency
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray()
    {
        return [
            'orderId' => $this->getOrderId(),
            'orderLink' => $this->getOrderDetailLink(),
            'orderStatus' => $this->getOrderStatus(),
            'shippingCost' => $this->getShippingCost(),
            'shippingDate' => $this->getShippingDate(),
            'shippingId' => $this->getShippingId(),
            'shippingService' => $this->getShippingService(),
            'currency' => $this->getCurrency(),
            'orderStatusBadgeColor' => $this->getOrderStatusBadgeColor(),
        ];
    }

    /**
     * Get the value of orderStatusBadgeColor
     */
    public function getOrderStatusBadgeColor(): string
    {
        return $this->orderStatusBadgeColor;
    }

    /**
     * Set the value of orderStatusBadgeColor
     */
    public function setOrderStatusBadgeColor(string $orderStatusBadgeColor): self
    {
        $this->orderStatusBadgeColor = $orderStatusBadgeColor;

        return $this;
    }
}
