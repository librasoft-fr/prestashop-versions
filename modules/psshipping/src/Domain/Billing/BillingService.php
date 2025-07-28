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

namespace PrestaShop\Module\Psshipping\Domain\Billing;

use Psshipping;

if (!defined('_PS_VERSION_')) {
    exit();
}

class BillingService
{
    /**
     * @param Psshipping $module
     *
     * @return array<string, string|boolean|null>
     *
     * @throws BillingContextIsNotAvailableException
     */
    public function getBillingContext(Psshipping $module)
    {
        /** @var AbstractBillingType $billingFacade */
        $billingFacade = $module->getService('psshipping.ps_billings_facade');
        $shippingLogo = $module->getLocalPath() . 'views/img/shipping-logo.png';

        $psBillingContext = $billingFacade->present([
            'logo' => $shippingLogo,
            'tosLink' => 'https://prestashop.com/prestashop-account-terms-conditions/',
            'privacyLink' => 'https://prestashop.com/prestashop-account-privacy/',
            'emailSupport' => 'support@prestashop.com',
        ]);

        if (empty($psBillingContext['psBillingContext'])) {
            throw new BillingContextIsNotAvailableException('Billing context is empty');
        }

        return $psBillingContext['psBillingContext'];
    }

    /**
     * @param Psshipping $module
     *
     * @return bool
     **/
    public function hasActiveSubscription(Psshipping $module)
    {
        /** @var AbstractBillingType $billingService */
        $billingService = $module->getService('psshipping.ps_billings_service');
        $currentSubscription = $billingService->getCurrentSubscription();

        $subscription = [];
        // We test here the success of the request in the response's body.
        if (!empty($currentSubscription['success'])) {
            $subscription = $currentSubscription['body'];
        }

        return !empty($subscription);
    }
}
