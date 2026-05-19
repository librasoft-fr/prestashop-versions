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

namespace PrestaShop\Module\Psshipping\Domain\GelProximity;

use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\GelProximity\Models\GelProximityCredentials;
use PrestaShop\Module\Psshipping\Domain\Http\HttpClient;
use Psshipping;

class GelProximityService
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    public function getGelCredentials(): ?GelProximityCredentials
    {
        $jwt = (new AccountsService())->getPsAccountToken($this->module);
        $httpClient = new HttpClient($this->module->getApiUrl());
        $httpClient->setHeaders([
            'Accept: application/json',
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json',
        ]);

        $response = $httpClient->get('/user/gel-config');

        if (substr(strval($response->getStatusCode()), 0, 1) !== '2') {
            return null;
        }

        /** @var array{'merchantCode': string, 'apiKey': string} $config */
        $config = json_decode($response->getBody(), true);

        return GelProximityCredentials::fromArray($config);
    }

    public static function buildSessionReference(int $cartId): string
    {
        return 'PickupPointCartId-' . (string) $cartId;
    }
}
