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

namespace PrestaShop\Module\Psshipping\Domain\GelProximity\Models;

use Exception;
use PrestaShop\Module\Psshipping\Entity\PsshippingAddress;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PickupPoint
{
    private const COOKIES_PREFIX = 'ps_shipping_';

    /**
     * @var int
     */
    private $pickupPointId;

    /**
     * @var string
     */
    private $networkCode;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $zipCode;

    /**
     * @var string|null
     */
    private $department;

    /**
     * @var string
     */
    private $country;

    /**
     * PickupPoint constructor.
     *
     * @param array{
     *   pickupPointId: int,
     *   networkCode: string,
     *   code: string,
     *   address: string,
     *   description: string,
     *   city: string,
     *   zipCode: string,
     *   department: string|null,
     *   country: string
     * } $data
     */
    public function __construct(array $data)
    {
        $this->pickupPointId = (int) $data['pickupPointId'];
        $this->networkCode = $data['networkCode'];
        $this->code = $data['code'];
        $this->address = $data['address'];
        $this->description = $data['description'];
        $this->city = $data['city'];
        $this->zipCode = $data['zipCode'];
        $this->department = isset($data['department']) ? $data['department'] : null;
        $this->country = $data['country'];
        $this->address = $data['address'];
    }

    // --- Getters ---
    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }

    public function getNetworkCode(): string
    {
        return $this->networkCode;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    // --- Setters ---
    public function setPickupPointId(int $pickupPointId): void
    {
        $this->pickupPointId = $pickupPointId;
    }

    public function setNetworkCode(string $networkCode): void
    {
        $this->networkCode = $networkCode;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function setZipCode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function setDepartment(?string $department): void
    {
        $this->department = $department;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @param array{
     *   pickupPointId: int,
     *   networkCode: string,
     *   code: string,
     *   address: string,
     *   description: string,
     *   city: string,
     *   zipCode: string,
     *   department: string|null,
     *   country: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /** @return array{
     *   pickupPointId: int,
     *   networkCode: string,
     *   code: string,
     *   address: string,
     *   description: string,
     *   city: string,
     *   zipCode: string,
     *   department: string|null,
     *   country: string
     * }
     */
    public function toArray(): array
    {
        return [
            'pickupPointId' => $this->pickupPointId,
            'networkCode' => $this->networkCode,
            'code' => $this->code,
            'description' => $this->description,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'department' => $this->department,
            'country' => $this->country,
            'address' => $this->address,
        ];
    }

    /**
     * @param \Context $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function injectIntoCookies($context)
    {
        if ($context->cookie === null) {
            throw new PsshippingException("The 'cookie' field is null in the context.");
        }

        $context->cookie->__set(self::COOKIES_PREFIX . 'pickupPointId', $this->getPickupPointId());
        $context->cookie->__set(self::COOKIES_PREFIX . 'networkCode', $this->getNetworkCode());
        $context->cookie->__set(self::COOKIES_PREFIX . 'code', $this->getCode());
        $context->cookie->__set(self::COOKIES_PREFIX . 'description', $this->getDescription());
        $context->cookie->__set(self::COOKIES_PREFIX . 'city', $this->getCity());
        $context->cookie->__set(self::COOKIES_PREFIX . 'zipCode', $this->getZipCode());
        $context->cookie->__set(self::COOKIES_PREFIX . 'department', $this->getDepartment());
        $context->cookie->__set(self::COOKIES_PREFIX . 'country', $this->getCountry());
        $context->cookie->__set(self::COOKIES_PREFIX . 'address', $this->getaddress());
    }

    /**
     * @param \Context $context
     *
     * @return self
     *
     * @throws PsshippingException
     */
    public static function fromCookies($context)
    {
        if ($context->cookie === null) {
            throw new PsshippingException("The 'cookie' field is null in the context.");
        }

        $requiredKeys = [
            'pickupPointId',
            'networkCode',
            'code',
            'city',
            'zipCode',
            'country',
            'address',
        ];

        $optionalKeys = [
            'description',
            'department',
        ];

        /** @var array{
         *   pickupPointId: int,
         *   networkCode: string,
         *   code: string,
         *   address: string,
         *   description: string,
         *   city: string,
         *   zipCode: string,
         *   department: string|null,
         *   country: string
         * } $data
         */
        $data = [];

        foreach ($requiredKeys as $key) {
            $cookieKey = self::COOKIES_PREFIX . $key;
            $value = $context->cookie->__get($cookieKey);

            // @phpstan-ignore-next-line
            if ($value === false) {
                throw new PsshippingException(sprintf("Require key from cookie '%s' is not present.", $cookieKey));
            }

            if ($key === 'pickupPointId') {
                $data[$key] = (int) $value;
            } else {
                $data[$key] = (string) $value;
            }
        }

        foreach ($optionalKeys as $key) {
            $cookieKey = self::COOKIES_PREFIX . $key;
            $value = $context->cookie->__get($cookieKey);
            $data[$key] = $value;
        }

        return self::fromArray($data);
    }

    /**
     * @param \Context $context
     *
     * @return void
     *
     * @throws PsshippingException
     */
    public static function unsetFromCookies($context)
    {
        if ($context->cookie === null) {
            throw new PsshippingException("The 'cookie' field is null in the context.");
        }

        $fields = [
            'pickupPointId',
            'networkCode',
            'code',
            'description',
            'city',
            'zipCode',
            'department',
            'country',
            'address',
        ];

        foreach ($fields as $field) {
            $context->cookie->__unset(self::COOKIES_PREFIX . $field);
        }
    }

    public static function fromShippingAddress(PsshippingAddress $address): self
    {
        $data = [
            'pickupPointId' => $address->getPickupPointId(),
            'networkCode' => $address->getNetworkCode(),
            'code' => $address->getCode(),
            'description' => $address->getDescription() ?? '',
            'address' => $address->getAddress(),
            'city' => $address->getCity(),
            'zipCode' => $address->getZipCode(),
            'department' => $address->getDepartment(),
            'country' => $address->getCountry(),
        ];

        return self::fromArray($data);
    }
}
