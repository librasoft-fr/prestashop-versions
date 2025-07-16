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

namespace PrestaShop\Module\Psshipping\Controller\Admin;

use Context;
use GuzzleHttp\Exception\BadResponseException;
use PrestaShop\Module\Psshipping\Domain\Http\HttpClient;
use PrestaShop\Module\Psshipping\Exception\BadRequestException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Psshipping;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit();
}

class PsshippingFaqController extends FrameworkBundleAdminController
{
    /** @var Psshipping */
    private $module;

    public function __construct(Psshipping $module)
    {
        $this->module = $module;
    }

    /**
     * @throws BadResponseException
     */
    public function getFaq(): Response
    {
        try {
            $context = Context::getContext();
            $faq = [
                'categories' => [],
            ];

            if (!empty($context->language)) {
                $request = new HttpClient('https://api.addons.prestashop.com');
                $result = $request->get('/request/faq/' . $this->module->module_key . '/' . _PS_VERSION_ . '/' . $context->language->iso_code, []);

                if ($result->getStatusCode() === 200) {
                    $faq['categories'] = json_decode($result->getBody(), true);
                }
            }

            return new Response(
                json_encode([
                    'faq' => $faq,
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }
    }
}
