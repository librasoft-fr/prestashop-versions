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
 * RecipientPickupPointDTO
 *
 * A Data Transfer Object (DTO) for MBE recipient information,
 * including methods for array conversion.
 * This is the format required by MBE API.
 */
final class RecipientPickupPointDTO extends RecipientDTO
{
    /**
     * WARNING - on MBE API, gelPudoPointID does not mean pickupPointID return by
     * gel proximity SDK but the "code" property instead.
     *
     * @var string pickup point id (gel proximity)
     */
    private $gelPudoPointId;

    /**
     * @var string gel proximity network code
     */
    private $gelNetworkCode;

    /**
     * Constructor for RecipientDTO.
     *
     * @param string $gelPudoPointId pickup point id (gel proximity)
     * @param string $gelNetworkCode gel network code
     */
    public function __construct(
        string $gelPudoPointId,
        string $gelNetworkCode,
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
        parent::__construct($name, $address, $address2, $phone, $zipCode, $city, $state, $country, $email);
        $this->gelPudoPointId = $gelPudoPointId;
        $this->gelNetworkCode = $gelNetworkCode;
    }

    // --- Getters ---

    /**
     * Get the gel proximity pickup point id.
     *
     * @return string
     */
    public function getGelPudoPointId(): string
    {
        return $this->gelPudoPointId;
    }

    /**
     * Get the gel proximity network code.
     *
     * @return string
     */
    public function getGelNetworkCode(): string
    {
        return $this->gelNetworkCode;
    }

    // --- Setters ---

    /**
     * Get the gel proximity pickup point id.
     *
     * @param string $gelPudoPointId
     *
     * @return self
     */
    public function setGelPudoPointId(string $gelPudoPointId): self
    {
        $this->gelPudoPointId = $gelPudoPointId;

        return $this;
    }

    /**
     * Get the gel proximity network code.
     *
     * @param string $gelNetworkCode
     *
     * @return self
     */
    public function setGelNetworkCode(string $gelNetworkCode): self
    {
        $this->gelNetworkCode = $gelNetworkCode;

        return $this;
    }

    /**
     * Converts the DTO object to an associative array.
     *
     * @return array{
     *  gelPudoPointId: string,
     *  gelNetworkCode: string,
     *  name: string,
     *  address: string,
     *  address2: string|null,
     *  phone: string,
     *  zipCode: string,
     *  state: string|null,
     *  city: string,
     *  country: string,
     *  email: string,
     * }
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'gelPudoPointId' => $this->gelPudoPointId,
            'gelNetworkCode' => $this->gelNetworkCode,
        ]);
    }

    /**
     * Creates a RecipientDTO object from an associative array.
     *
     * @param array{
     *  gelPudoPointId: string,
     *  gelNetworkCode: string,
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
     * @return static
     *
     * @throws PsshippingException if a required key is missing from the array
     */
    public static function fromPickupPointArray(array $data)
    {
        return new static(
            $data['gelPudoPointId'],
            $data['gelNetworkCode'],
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
