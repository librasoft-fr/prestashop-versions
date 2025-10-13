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

namespace PrestaShop\Module\PsClassicEdition\Controller;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdminPsClassicEditionPsAcademyController extends PrestaShopAdminController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * Handle the call back requests
     *
     * @return JsonResponse
     */
    public function getProducts(): JsonResponse
    {
        $isoCode = strtolower($this->getLanguageContext()->getIsoCode());
        $psAcademyLangId = match ($isoCode) {
            'fr' => 1,
            'es' => 3,
            'it' => 4,
            // Default is en
            default => 2,
        };

        $cachedProducts = $this->cache->get('ps_academy_products_' . $isoCode, function (ItemInterface $item, bool &$save) use ($psAcademyLangId) {
            $products = [];
            $ids = $this->getProductsId($psAcademyLangId);

            if (!empty($ids)) {
                foreach ($ids as $id) {
                    $response = $this->httpClient->request('GET', 'https://care-center.prestashop.com/api/products/' . $id . '?ws_key=QG8Z1KD7HAYMAPKK1FR2DKXUIF9LTRJE&output_format=JSON&id_lang=' . $psAcademyLangId);
                    $httpStatusCode = $response->getStatusCode();
                    if ($httpStatusCode <= 300) {
                        $responseContents = json_decode($response->getContent(), true);
                        $tempObject = $this->createObjectFromResponse($responseContents['product']);
                        array_push($products, $tempObject);
                    }
                }
            }

            if (!empty($products)) {
                $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
            } else {
                $save = false;
            }

            return $products;
        });

        return new JsonResponse($cachedProducts);
    }

    private function getProductsId(int $psAcademyLangId): array
    {
        $responseVideoHosted = $this->httpClient->request(
            'GET',
            'https://care-center.prestashop.com/api/products?filter[mpn]=[videoHosted]&ws_key=QG8Z1KD7HAYMAPKK1FR2DKXUIF9LTRJE&output_format=JSON&id_lang=' . $psAcademyLangId,
        );
        $responseLiveHosted = $this->httpClient->request(
            'GET',
            'https://care-center.prestashop.com/api/products?filter[mpn]=[liveHosted]&ws_key=QG8Z1KD7HAYMAPKK1FR2DKXUIF9LTRJE&output_format=JSON&id_lang=' . $psAcademyLangId,
        );

        $responseContentsVideoHosted = json_decode($responseVideoHosted->getContent(), true);
        $responseContentsLiveHosted = json_decode($responseLiveHosted->getContent(), true);

        $httpStatusCodeVideoHosted = $responseVideoHosted->getStatusCode();
        $httpStatusCodeLiveHosted = $responseLiveHosted->getStatusCode();

        if ($httpStatusCodeVideoHosted >= 400 || $httpStatusCodeLiveHosted >= 400) {
            return [];
        }

        return array_column(array_merge($responseContentsLiveHosted['products'], $responseContentsVideoHosted['products']), 'id');
    }

    private function createObjectFromResponse(array $response): array
    {
        $locale = 'gb';
        $contextIsoCode = $this->getLanguageContext()->getIsoCode();
        if ($contextIsoCode) {
            $locale = $contextIsoCode;

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

        $responseCategory = $this->httpClient->request('GET', 'https://care-center.prestashop.com/api/categories/' . $response['id_category_default'] . '?ws_key=QG8Z1KD7HAYMAPKK1FR2DKXUIF9LTRJE&output_format=JSON');
        $httpStatusCode = $responseCategory->getStatusCode();

        if ($httpStatusCode > 300) {
            return [];
        }
        $responseContents = json_decode($responseCategory->getContent(), true);
        $category = $responseContents['category']['link_rewrite'][$langIds[$locale]]['value'];
        $link_rewrite = $response['link_rewrite'][$langIds[$locale]]['value'];
        $productUrl = 'https://care-center.prestashop.com/' . $locale . '/' . $category . '/' . $response['id'] . '-' . $link_rewrite . '.html';

        $tmp = [
            'name' => $response['name'][$langIds[$locale]]['value'],
            'description' => $response['description'][$langIds[$locale]]['value'],
            'url' => $productUrl,
        ];

        return $tmp;
    }
}
