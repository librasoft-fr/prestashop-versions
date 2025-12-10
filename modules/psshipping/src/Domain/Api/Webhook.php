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

namespace PrestaShop\Module\Psshipping\Domain\Api;

use Context as LegacyContext;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use PrestaShop\Module\Psshipping\Domain\Http\HttpClient;
use PrestaShop\Module\Psshipping\Exception\PsshippingException;
use PrestaShop\PrestaShop\Adapter\Configuration;
use Psshipping;

class Webhook
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    /**
     * @param string $svixSecret
     *
     * @return void
     *
     * @throws PsshippingException
     */
    public function createSvixEndpoint(string $svixSecret)
    {
        $jwt = (new AccountsService())->getPsAccountToken($this->module);
        $configuration = new Configuration();
        $context = LegacyContext::getContext();

        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }
        $httpClient = new HttpClient($this->module->getApiUrl());
        $httpClient->setHeaders([
            'Accept: application/json',
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json',
        ]);

        $response = $httpClient->post('/shipment-status/webhook', (string) json_encode([
            'svixSecret' => $svixSecret,
        ]));

        if (substr(strval($response->getStatusCode()), 0, 1) !== '2') {
            throw new PsshippingException('An error occured while sending the secret to the API.', 400);
        }

        $configuration->set('PS_SHIPPING_WEBHOOK_SECRET', $svixSecret);
    }

    public function deleteSvixEndpoint(): void
    {
        // if accounts is not installed, we cannot anymore proceed to the deletion
        // of svix endpoint, catch and silent the error in this case in order to
        // not block the uninstall() process
        try {
            $jwt = (new AccountsService())->getPsAccountToken($this->module);
        } catch (\Exception $exception) {
            return;
        }

        if (empty($jwt)) {
            return;
        }

        $configuration = new Configuration();
        $context = LegacyContext::getContext();

        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        $response = (new HttpClient($this->module->getApiUrl()))->setHeaders([
            'Accept: application/json',
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json',
        ])->delete('/shipment-status/webhook');

        if (substr(strval($response->getStatusCode()), 0, 1) !== '2') {
            throw new PsshippingException('An error occured while removing the svix endpoint to the API.', 400, false);
        }
    }

    private function generateSvixSecret(): string
    {
        return 'whsec_' . base64_encode(random_bytes(24));
    }

    /**
     * @throws PsshippingException
     */
    public function saveSvixSecret(): void
    {
        $this->createSvixEndpoint(
            $this->generateSvixSecret()
        );
    }
}
