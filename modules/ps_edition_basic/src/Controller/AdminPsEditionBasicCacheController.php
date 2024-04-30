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

use Context;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPsEditionBasicCacheController extends FrameworkBundleAdminController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string $serviceName
     *
     * @return object
     */
    public function get($serviceName)
    {
        if (null === $this->container) {
            $this->container = SymfonyContainer::getInstance();
        }

        return $this->container->get($serviceName);
    }

    /**
     * Handle the call back requests
     *
     * @return JsonResponse
     */
    public function handleRequest(): JsonResponse
    {
        $triggered = true;

        try {
            // Force MBO's getModulesList to call MBO's API
            $languageIsoCode = Context::getContext()->language->getIsoCode();
            $countryIsoCode = mb_strtolower(Context::getContext()->country->iso_code);
            $user = $this->get('mbo.addons.user.provider')->getUser();
            $userCacheKey = '';
            if ($user->isAuthenticated()) {
                $credentials = $user->getCredentials(true);

                if (array_key_exists('accounts_token', $credentials)) {
                    $userCacheKey = md5($credentials['accounts_token']);
                } else {
                    $userCacheKey = md5($credentials['username'] . $credentials['password']);
                }
            }

            $cacheKey = 'PrestaShop\Module\Mbo\Distribution\ConnectedClient::getModulesList' . $languageIsoCode . $countryIsoCode . $userCacheKey . _PS_VERSION_;
            $mboCache = $this->get('mbo.doctrine.cache.provider');
            $mboCache->delete($cacheKey);
        } catch (\Exception $e) {
            // TOTO: Log ?
            $triggered = false;
        } finally {
            return new JsonResponse(['eventTriggered' => $triggered]);
        }
    }
}
