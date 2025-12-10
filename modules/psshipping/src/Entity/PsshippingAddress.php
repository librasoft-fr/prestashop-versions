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

namespace PrestaShop\Module\Psshipping\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PrestaShop\Module\Psshipping\Domain\PickupPoints\PsshippingAddressRepository")
 */
class PsshippingAddress
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_address", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * ID that the gel proximity SDK return. It's the ID of the pickup point
     * on GEL PROXIMITY side.
     *
     * @var int
     *
     * @ORM\Column(name="pickup_point_id", type="integer", unique=true, nullable=false)
     */
    private $pickupPointId;

    /**
     * @var string
     *
     * @ORM\Column(name="network_code", type="string", length=20, nullable=false)
     */
    private $networkCode;

    /**
     * This var is the var to use on MBE side for the "gelPudoPointId"
     * On MBE side gelPudoPointId = "code" coming from gel proximity SDK.
     * This code looks like something like PUP_XXXXXX
     *
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=255)
     */
    private $zipCode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="department", type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    public function setPickupPointId(int $pickupPointId): self
    {
        $this->pickupPointId = $pickupPointId;

        return $this;
    }

    public function getNetworkCode(): string
    {
        return $this->networkCode;
    }

    public function setNetworkCode(string $networkCode): self
    {
        $this->networkCode = $networkCode;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'pickup_point_id' => $this->pickupPointId,
            'code' => $this->code,
            'address' => $this->address,
            'city' => $this->city,
            'zip_code' => $this->zipCode,
            'department' => $this->department,
            'country' => $this->country,
        ];
    }
}
