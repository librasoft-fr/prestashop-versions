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

use Context as LegacyContext;
use PrestaShop\Module\Psshipping\Domain\Orders\OrdersRepository;
use PrestaShop\PrestaShop\Adapter\Configuration;
use Svix\Exception\WebhookVerificationException;
use Svix\Webhook;

class psshippingWebhookOrderStatusModuleFrontController extends \ModuleFrontController
{
    public function init()
    {
        $this->ajax = true;
        $this->content_only = true;
        $this->controller_type = 'module';
    }

    /**
     * @return void
     *
     * @throws\PrestaShopException
     */
    public function postProcess()
    {
        $configuration = new Configuration();
        $context = LegacyContext::getContext();

        if (!empty($context) && !empty($context->shop)) {
            $configuration->restrictUpdatesTo($context->shop);
        }

        $secretKey = $configuration->get('PS_SHIPPING_WEBHOOK_SECRET');

        if (empty($secretKey)) {
            $this->exitWithResponse([
                'success' => false,
                'httpCode' => 400,
                'msg' => 'missing secret key',
            ]);
        }

        $json = file_get_contents('php://input');

        if (empty($json)) {
            $this->exitWithResponse([
                'success' => false,
                'httpCode' => 400,
                'msg' => 'payload is empty',
            ]);
        }

        try {
            (new Webhook($secretKey))->verify($json, array_change_key_case(getallheaders(), CASE_LOWER));
            $resp = json_decode($json, true);
            $orderRepository = new OrdersRepository($this->module);
            $orderRepository->updateOrder($resp['orderId'], $resp['status']);
            $this->exitWithResponse([
                'success' => true,
                'httpCode' => 200,
                'body' => $resp,
            ]);
        } catch (WebhookVerificationException $e) {
            $this->exitWithResponse([
                'success' => false,
                'httpCode' => 400,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array $response
     *
     * @return void
     */
    protected function exitWithResponse(array $response)
    {
        $httpCode = isset($response['httpCode']) ? (int) $response['httpCode'] : 200;

        $this->dieWithResponse($response, $httpCode);
    }

    /**
     * @param array $response
     * @param int $code
     *
     * @return void
     */
    private function dieWithResponse(array $response, $code)
    {
        $httpStatusText = "HTTP/1.1 $code";
        $response['httpCode'] = (int) $code;

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/json;charset=utf-8');
        header($httpStatusText);

        echo json_encode($response, JSON_UNESCAPED_SLASHES);

        exit;
    }
}
