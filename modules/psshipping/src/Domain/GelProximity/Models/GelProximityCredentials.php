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

if (!defined('_PS_VERSION_')) {
    exit();
}

class GelProximityCredentials
{
    /**
     * @var string
     */
    private $merchantCode;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $merchantCode
     * @param string $apiKey
     */
    public function __construct(string $merchantCode, string $apiKey)
    {
        $this->merchantCode = $merchantCode;
        $this->apiKey = $apiKey;
    }

    /**
     * @param array{
     *   merchantCode: string,
     *   apiKey: string
     * } $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data['merchantCode'], $data['apiKey']);
    }

    /**
     * @return array{
     *   merchantCode: string,
     *   apiKey: string
     * }
     */
    public function toArray(): array
    {
        return [
            'merchantCode' => $this->merchantCode,
            'apiKey' => $this->apiKey,
        ];
    }

    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    public function setMerchantCode(string $merchantCode): void
    {
        $this->merchantCode = $merchantCode;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
