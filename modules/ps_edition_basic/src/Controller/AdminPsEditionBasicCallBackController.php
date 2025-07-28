<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPsEditionBasicCallBackController extends FrameworkBundleAdminController
{
    /**
     * Handle the call back requests
     *
     * @return JsonResponse
     */
    public function handleRequest(): JsonResponse
    {
        $triggered = false;
        if ($isCalledBack = (bool) \Tools::getValue('isCalledBack')) {
            $triggered = $this->storeIsCalled($isCalledBack);
        } elseif ($apiResponse = (string) \Tools::getValue('apiResponse')) {
            $triggered = $this->storeApiResponse($apiResponse);
        } elseif ($subscriptionId = (string) \Tools::getValue('subscriptionId')) {
            $triggered = $this->storeSubscriptionId($subscriptionId);
        }

        return new JsonResponse(['eventTriggered' => $triggered]);
    }

    private function storeIsCalled(bool $isCalledBack): bool
    {
        if (!(bool) $this->configuration->get('PS_IS_CALLED_BACK')) {
            $this->configuration->set('PS_IS_CALLED_BACK', $isCalledBack);

            return true;
        }

        return false;
    }

    private function storeApiResponse(string $apiResponse): bool
    {
        $this->configuration->set('PS_CALLBACK_API_RESPONSE', $apiResponse);

        return true;
    }

    private function storeSubscriptionId(string $subscriptionId): bool
    {
        if (!(string) $this->configuration->get('PS_SHOP_SUBSCRIPTION_ID')) {
            $this->configuration->set('PS_SHOP_SUBSCRIPTION_ID', $subscriptionId);

            return true;
        }

        return false;
    }
}
