<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

namespace KlaviyoPs\Classes\Webservice;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration;
use Currency;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\Webservice\QueryServices\CartQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\CustomerQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\OrderQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\ProductQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\QueryServiceInterface;
use KlaviyoPsModule;
use Language;
use OrderState;
use PrestaShopDatabaseException;
use Shop;
use WebserviceException;
use WebserviceOutputBuilder;
use WebserviceRequest;
use WebserviceSpecificManagementInterface;

abstract class AbstractWebserviceSpecificManagementKlaviyo implements WebserviceSpecificManagementInterface
{
    /** @var WebserviceOutputBuilder */
    protected $objOutput;
    protected $output;

    /** @var WebserviceRequest */
    protected $wsObject;

    /** @var string Base endpoint of klaviyo request for easier access. */
    protected $baseEndpoint;

    /** @var int|null Third segment of request path which may be an ID. */
    protected $possibleObjectId;

    /** @var array Content to return in response. */
    public $jsonOutput = array();

    public function callbackSetObjectOutput(WebserviceOutputBuilder $obj)
    {
        $this->objOutput = $obj;
        return $this;
    }

    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    public function callbackSetWsObject(WebserviceRequest $obj)
    {
        $this->wsObject = $obj;
        return $this;
    }

    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * Handle incoming request. Called in WebserviceRequest->fetch under the
     * WebserviceSpecificManagement conditional.
     *
     * @return bool
     * @throws WebserviceException
     */
    public function manage()
    {
        // Klaviyo only wants JSON formatted response and wsObject->outputFormat is protected
        // so we can't override. And as a result this error still returns xml so beware and be aware.
        if (
            !isset($this->wsObject->urlFragments['output_format']) ||
            $this->wsObject->urlFragments['output_format'] !== 'JSON'
        ) {
            throw new WebserviceException(
                'Invalid request, output_format parameter must be set to JSON.',
                [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]
            );
        }

        $this->handleRequest();

        return true;
    }

    /**
     * Route request based on endpoint.
     *
     * @throws WebserviceException
     */
    private function handleRequest()
    {
        /*
         * Available cases api/...
         *
         *     klaviyo/version
         *         GET     (json)
         *
         *     klaviyo/shops
         *         GET     (json)
         *
         *     klaviyo/languages
         *         GET     (json)
         *
         *     klaviyo/order_states
         *         GET     (json)
         *
         *     klaviyo/currencies
         *         GET     (json)
         *
         *     klaviyo/carts
         *         GET     (json)
         *     klaviyo/carts/count
         *         GET     (json)
         *     klaviyo/carts/[1,+]
         *         GET     (json)
         *
         *     klaviyo/customers
         *         GET     (json)
         *     klaviyo/customers/count
         *         GET     (json)
         *     klaviyo/customers/[1,+]
         *         GET     (json)
         *
         *     klaviyo/orders
         *         GET     (json)
         *     klaviyo/orders/count
         *         GET     (json)
         *     klaviyo/orders/[1,+]
         *         GET     (json)
         *
         *     klaviyo/products
         *         GET     (json)
         *     klaviyo/products/count
         *         GET     (json)
         *     klaviyo/products/[1,+]
         *         GET     (json)
         */

        // Preconfigure url segments up to 3 nodes deep so it's easier to check values of each
        // endpoint segment. Taken from WebserviceSpecificManagementImagesCore.
        if (isset($this->wsObject->urlSegment)) {
            for ($i = 1; $i < 3; ++$i) {
                if (count($this->wsObject->urlSegment) == $i) {
                    $this->wsObject->urlSegment[$i] = '';
                }
            }
        }

        // TODO: This might make more sense up in the manage() method. Same with the above.
        $this->baseEndpoint = (string) $this->wsObject->urlSegment[1];
        $this->possibleObjectId = self::castIdToInt($this->wsObject->urlSegment[2]);

        switch ($this->baseEndpoint) {
            case 'carts':
                $this->handleCartsRequest();
                break;
            case 'customers':
                $this->handleCustomersRequest();
                break;
            case 'languages':
                $this->handleLanguagesRequest();
                break;
            case 'order_states':
                $this->handleOrderStatesRequest();
                break;
            case 'currencies':
                $this->handleCurrenciesRequest();
                break;
            case 'orders':
                $this->handleOrdersRequest();
                break;
            case 'products':
                $this->handleProductsRequest();
                break;
            case 'shops':
                $this->handleShopsRequest();
                break;
            case 'version':
                $this->handleVersionRequest();
                break;
            default:
                $exception = new WebserviceException(sprintf(
                    'Invalid endpoint, resource of type \'%s\' does not exist under the klaviyo endpoint',
                    $this->wsObject->urlSegment[1]
                ), [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]);

                throw $exception->setDidYouMean($this->baseEndpoint, [
                    'carts', 'customers', 'languages', 'order_states', 'orders', 'products', 'shops', 'version'
                ]);
        }
    }

    /**
     * Handle cart related requests.
     *
     * @throws WebserviceException
     */
    private function handleCartsRequest()
    {
        $this->handleRequestMethod(['GET']);
        $this->handleDefaultEndpointRequest('CartQueryService');
    }

    /**
     * Handle customer related requests.
     *
     * @throws WebserviceException
     */
    private function handleCustomersRequest()
    {
        $this->handleRequestMethod(['GET']);
        $this->handleDefaultEndpointRequest('CustomerQueryService');
    }

    /**
     * Handle language related requests.
     *
     * @throws WebserviceException
     */
    private function handleLanguagesRequest()
    {
        $this->handleRequestMethod(['GET']);
        if ('' === $this->possibleObjectId) {
            $languages = Language::getLanguages();
            $this->jsonOutput = (KlaviyoUtils::hasStringKeys($languages)) ? [$languages] : $languages;
        } else {
            $this->throwInvalidSubresourceException();
        };
    }

    /**
     * Handle order related requests.
     *
     * @throws WebserviceException
     */
    private function handleOrdersRequest()
    {
        $this->handleRequestMethod(['GET']);
        $this->handleDefaultEndpointRequest('OrderQueryService');
    }

    /**
     * Handle order status requests.
     *
     * @throws WebserviceException
     */
    private function handleOrderStatesRequest()
    {
        $this->handleRequestMethod(['GET']);
        if ('' === $this->possibleObjectId) {
            $this->jsonOutput = OrderState::getOrderStates(Configuration::get('PS_LANG_DEFAULT'));
        } elseif ('map' === $this->possibleObjectId) {
            try {
                $this->jsonOutput = KlaviyoUtils::getAllOrderStatusMaps();
            } catch (PrestaShopDatabaseException $e) {
                throw new WebserviceException(
                    $e->getMessage(),
                    [QueryServiceInterface::DEFAULT_ERROR_CODE, 500]
                );
            }
        } else {
            $this->throwInvalidSubresourceException();
        };
    }

    /**
     * Handle currencies requests
     *
     * @throws WebserviceException
     */
    public function handleCurrenciesRequest()
    {
        $this->handleRequestMethod(['GET']);
        if ('' === $this->possibleObjectId) {
            $this->jsonOutput = Currency::getCurrencies(Configuration::get('PS_LANG_DEFAULT'));
        } else {
            $this->throwInvalidSubresourceException();
        };
    }
    /**
     * Handle product related requests.
     *
     * @throws WebserviceException
     */
    private function handleProductsRequest()
    {
        $this->handleRequestMethod(['GET']);
        $this->handleDefaultEndpointRequest('ProductQueryService');
    }

    /**
     * Handle shops related requests.
     *
     * @throws WebserviceException
     */
    private function handleShopsRequest()
    {
        $this->handleRequestMethod(['GET']);
        if ('' === $this->possibleObjectId) {
            $shops = Shop::getShops();
            foreach ($shops as $shopId => &$shopDetails) {
                $shopLanguages = Language::getLanguages($active = true, $id_shop = $shopId);
                $shopDetails['languages'] = $shopLanguages;
            }
            $this->jsonOutput = $shops;
        } else {
            $this->throwInvalidSubresourceException();
        };
    }

    /**
     * Handle version related requests.
     *
     * @throws WebserviceException
     */
    private function handleVersionRequest()
    {
        $this->handleRequestMethod(['GET']);
        if ('' === $this->possibleObjectId) {
            $klaviyops = KlaviyoPsModule::getInstance();
            $this->jsonOutput = [
                'moduleVersion' => $klaviyops->version,
                'phpVersion' => phpversion(),
            ];
        } else {
            $this->throwInvalidSubresourceException();
        };
    }

    /**
     * Ensure request method is valid for endpoint.
     *
     * @param array $validMethods
     * @throws WebserviceException
     */
    private function handleRequestMethod(array $validMethods)
    {
        if (!in_array($this->wsObject->method, $validMethods)) {
            throw new WebserviceException(sprintf(
                'Method \'%s\' is not allowed for \'%s\' endpoint',
                $this->wsObject->method,
                $this->baseEndpoint
            ), [QueryServiceInterface::DEFAULT_ERROR_CODE, 405]);
        }
    }

    /**
     * Route default endpoint structure to handle a specific ID or fetching objects by time range.
     *
     * @param $queryServiceClass
     * @throws WebserviceException
     */
    private function handleDefaultEndpointRequest($queryServiceClassName)
    {
        $queryServiceClass = 'KlaviyoPs\Classes\Webservice\QueryServices\\' . $queryServiceClassName;
        $queryService = new $queryServiceClass($this->wsObject->urlFragments);
        if ('' === $this->possibleObjectId) {
            $this->jsonOutput = $queryService->getObjectsByTimeRange();
        } elseif ('count' === $this->possibleObjectId) {
            $this->jsonOutput = $queryService->getCountByTimeRange();
        } elseif (is_int($this->possibleObjectId)) {
            $this->jsonOutput = $queryService->getObjectById($this->possibleObjectId);
        } else {
            $this->throwInvalidSubresourceException();
        }
    }

    /**
     * Throw WebserviceException for invalid resources with standard messaging.
     *
     * @throws WebserviceException
     */
    private function throwInvalidSubresourceException()
    {
        throw new WebserviceException(sprintf(
            'Invalid endpoint, resource of type \'%s\' does not exist under the \'klaviyo\\%s\' endpoint',
            $this->possibleObjectId,
            $this->baseEndpoint
        ), [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]);
    }

    /**
     * Try to cast string to integer otherwise return string. Use for casting IDs in the request path.
     *
     * @param string $text Text parsed from WebserviceRequest urlSegment array.
     * @return int|string
     */
    private static function castIdToInt($text)
    {
        if (ctype_digit($text)) {
            return (int) $text;
        }
        return $text;
    }

    /**
     * Content to return in the webservice request. Called in WebserviceRequest->returnOutput.
     *
     * @return array|false|string
     * @throws WebserviceException
     */
    public function getContent()
    {
        $json = json_encode($this->jsonOutput);
        if (!$json) {
            throw new WebserviceException(
                sprintf(
                    'Klaviyo\'s module could not encode the response object. JSON encode error message: %s',
                    json_last_error_msg()
                ),
                [QueryServiceInterface::DEFAULT_ERROR_CODE, 500]
            );
        }

        return $json;
    }
}
