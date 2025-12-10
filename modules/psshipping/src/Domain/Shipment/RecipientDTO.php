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

namespace PrestaShop\Module\Psshipping\Domain\Shipment;

use PrestaShop\Module\Psshipping\Exception\PsshippingException;

/**
 * RecipientDTO
 *
 * A Data Transfer Object (DTO) for MBE recipient information,
 * including methods for array conversion.
 * This is the format required by MBE API.
 */
class RecipientDTO
{
    /**
     * @var string the name of the recipient (could be a person's name or company name)
     */
    private $name;

    /**
     * @var string the first line of the address
     */
    private $address;

    /**
     * @var string|null the second line of the address, if available
     */
    private $address2;

    /**
     * @var string the phone number of the recipient, if available
     */
    private $phone;

    /**
     * @var string the postal code or zip code
     */
    private $zipCode;

    /**
     * @var string the city
     */
    private $city;

    /**
     * @var string|null the state or province, if applicable
     */
    private $state;

    /**
     * @var string The country ISO code (e.g., 'US', 'FR').
     */
    private $country;

    /**
     * @var string the email address of the recipient
     */
    private $email;

    /**
     * Constructor for RecipientDTO.
     *
     * @param string $name the name of the recipient
     * @param string $address the first line of the address
     * @param string|null $address2 the second line of the address
     * @param string $phone the phone number
     * @param string $zipCode the postal/zip code
     * @param string $city the city
     * @param string|null $state the state/province
     * @param string $country the country ISO code
     * @param string $email the email address
     */
    public function __construct(
        string $name,
        string $address,
        ?string $address2,
        string $phone,
        string $zipCode,
        string $city,
        ?string $state,
        string $country,
        string $email
    ) {
        $this->name = $name;
        $this->address = $address;
        $this->address2 = $address2;
        $this->phone = $phone;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->email = $email;
    }

    // --- Getters ---

    /**
     * Get the recipient's name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the first address line.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Get the second address line.
     *
     * @return string
     */
    public function getAddress2(): string
    {
        return $this->address2 ?? '';
    }

    /**
     * Get the phone number.
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Get the zip/postal code.
     *
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * Get the city.
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Get the state/province.
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state ?? '';
    }

    /**
     * Get the country ISO code.
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Get the email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    // --- Setters ---

    /**
     * Set the recipient's name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the first address line.
     *
     * @param string $address
     *
     * @return self
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Set the second address line.
     *
     * @param string|null $address2
     *
     * @return self
     */
    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Set the phone number.
     *
     * @param string $phone
     *
     * @return self
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Set the zip/postal code.
     *
     * @param string $zipCode
     *
     * @return self
     */
    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Set the city.
     *
     * @param string $city
     *
     * @return self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Set the state/province.
     *
     * @param string|null $state
     *
     * @return self
     */
    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Set the country ISO code.
     *
     * @param string $country
     *
     * @return self
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Set the email address.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Converts the DTO object to an associative array.
     *
     * @return array{
     *  name: string,
     *  address: string,
     *  address2: string,
     *  phone: string,
     *  zipCode: string,
     *  state: string,
     *  city: string,
     *  country: string,
     *  email: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'address' => $this->getAddress(),
            'address2' => $this->getAddress2(),
            'phone' => $this->getPhone(),
            'zipCode' => $this->getZipCode(),
            'city' => $this->getCity(),
            'state' => $this->getState(),
            'country' => $this->getCountry(),
            'email' => $this->getEmail(),
        ];
    }

    /**
     * Creates a RecipientDTO object from an associative array.
     *
     * @param array{
     *  name: string,
     *  address: string,
     *  address2: string|null,
     *  phone: string,
     *  zipCode: string,
     *  state: string|null,
     *  city: string,
     *  country: string,
     *  email: string,
     * } $data
     *
     * @return self
     *
     * @throws PsshippingException if a required key is missing from the array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['address'],
            $data['address2'] ?? null,
            $data['phone'],
            $data['zipCode'],
            $data['city'],
            $data['state'] ?? null,
            $data['country'],
            $data['email']
        );
    }
}
