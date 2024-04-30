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
use GuzzleHttp\Psr7\Request;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;

(new \Symfony\Component\Dotenv\Dotenv(true))->loadEnv(dirname(__DIR__, 2) . '/.env');

define('URL', getenv('PS_EDITION_BASIC_PS_ACADEMY_URL'));
define('KEY', getenv('PS_EDITION_BASIC_PS_ACADEMY_KEY'));

class AdminPsEditionBasicPsAcademyController extends FrameworkBundleAdminController
{
    private function getProductsId(): array
    {
        $client = (new ClientFactory())->getClient();
        $requestVideoHosted = new Request('GET', URL . '/api/products?filter[mpn]=[videoHosted]&ws_key=' . KEY . '&output_format=JSON');
        $requestLiveHosted = new Request('GET', URL . '/api/products?filter[mpn]=[liveHosted]&ws_key=' . KEY . '&output_format=JSON');

        $responseVideoHosted = $client->sendRequest($requestVideoHosted);
        $responseLiveHosted = $client->sendRequest($requestLiveHosted);

        $responseContentsVideoHosted = json_decode($responseVideoHosted->getBody()->getContents(), true);
        $responseContentsLiveHosted = json_decode($responseLiveHosted->getBody()->getContents(), true);

        $httpStatusCodeVideoHosted = $responseVideoHosted->getStatusCode();
        $httpStatusCodeLiveHosted = $responseLiveHosted->getStatusCode();

        if ($httpStatusCodeVideoHosted >= 400 || $httpStatusCodeLiveHosted >= 400) {
            return [];
        }

        return array_column(array_merge($responseContentsLiveHosted['products'], $responseContentsVideoHosted['products']), 'id');
    }

    private function createObjectFromResponse(array $response): array
    {
        $context = Context::getContext();
        $locale = 'gb';
        if ($context->language->iso_code) {
            $locale = $context->language->iso_code;

            $availableLang = ['fr', 'it', 'es'];
            if ($locale === 'en' || !in_array($locale, $availableLang)) {
                $locale = 'gb';
            }
        }

        $langIds = [
            'fr' => 0,
            'gb' => 1,
            'es' => 2,
            'it' => 3,
        ];

        $client = (new ClientFactory())->getClient();
        $requestCategory = new Request('GET', URL . '/api/categories/' . $response['id_category_default'] . '?ws_key=' . KEY . '&output_format=JSON');
        $responseCategory = $client->sendRequest($requestCategory);
        $httpStatusCode = $responseCategory->getStatusCode();

        if ($httpStatusCode > 300) {
            return [];
        }
        $responseContents = json_decode($responseCategory->getBody()->getContents(), true);
        $category = $responseContents['category']['link_rewrite'][$langIds[$locale]]['value'];
        $link_rewrite = $response['link_rewrite'][$langIds[$locale]]['value'];
        $productUrl = URL . '/' . $locale . '/' . $category . '/' . $response['id'] . '-' . $link_rewrite . '.html';

        $tmp = [
            'name' => $response['name'][$langIds[$locale]]['value'],
            'description' => $response['description'][$langIds[$locale]]['value'],
            'url' => $productUrl,
        ];

        return $tmp;
    }

    /**
     * Handle the call back requests
     *
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        $ids = $this->getProductsId();
        $client = (new ClientFactory())->getClient();
        $products = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $request = new Request('GET', URL . '/api/products/' . $id . '?ws_key=' . KEY . '&output_format=JSON');
                $response = $client->sendRequest($request);
                $httpStatusCode = $response->getStatusCode();
                if ($httpStatusCode <= 300) {
                    $responseContents = json_decode($response->getBody()->getContents(), true);
                    $tempObject = $this->createObjectFromResponse($responseContents['product']);
                    array_push($products, $tempObject);
                }
            }
        }

        return new JsonResponse($products);
    }
}
