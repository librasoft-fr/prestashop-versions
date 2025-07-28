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
use KlaviyoPs\Classes\BusinessLogicServices\CombinationPayloadService;
use KlaviyoPs\Classes\KlaviyoUtils;
use KlaviyoPs\Classes\KlaviyoValue;
use KlaviyoPs\Classes\PrestashopServices\LoggerService;
use KlaviyoPs\Classes\Webservice\QueryServices\InventoryQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\CombinationQueryService;
use KlaviyoPs\Classes\Webservice\QueryServices\QueryServiceInterface;
use KlaviyoPs\Classes\BusinessLogicServices\CartRulePayloadService;
use KlaviyoPs\Classes\PrestashopServices\CartRuleService;
use KlaviyoPs\Classes\PrestashopServices\DateTimeService;
use KlaviyoV3Sdk\Exception\KlaviyoException;
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

        try {
            $this->handleRequest();
        } catch (\PrestaShopException $e) {
            $klaviyops = KlaviyoPsModule::getInstance();
            /** @var LoggerService $logger */
            $logger = $klaviyops->getService('klaviyops.prestashop_services.logger');
            $formattedTrace = $logger->formatTrace($e);
            $logger->error(sprintf("Error processing request to '%s' endpoint. Stack trace: %s", $this->baseEndpoint, $formattedTrace));

            throw new WebserviceException(
                'Klaviyo encountered an error when trying to process the request. Please see your logs for more detail.',
                [QueryServiceInterface::DEFAULT_ERROR_CODE, 500]
            );
        }


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
         *     klaviyo/combinations
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
         *
         *     klaviyo/coupons/{cart_rule_id}
         *         GET     (json)
         *     klaviyo/coupons/
         *         POST    (json)
         *     klaviyo/coupons/
         *         DELETE  (json)
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
            case 'combinations':
                $this->handleCombinationsRequest();
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
            case 'coupons':
                $this->handleCouponsRequest();
                break;
            case 'inventory':
                $this->handleInventoryRequest();
                break;
            case 'inventory_policy':
                $this->handleInventoryPolicyRequest();
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
     * Handle Combination product related requests.
     *
     * @throws WebserviceException
     */
    private function handleCombinationsRequest()
    {
        $this->handleRequestMethod(['GET']);
        $wsObject = $this->getWsObject();

        switch ($wsObject->method) {
            case 'GET':
                $requiredParams = array('shop', 'product_ids');
                $cqs = new CombinationQueryService();
                $params = $wsObject->urlFragments;
                foreach ($requiredParams as $paramName) {
                    $paramVal = $params[$paramName];
                    if (!isset($paramVal)) {
                        throw new WebServiceException(
                            "'" . $paramName . "'" . " parameter is required.",
                            [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]
                        );
                    } elseif (empty($paramVal) or !trim($paramVal)) {
                        throw new WebServiceException(
                            "Invalid value '" . $paramVal . "' for '" . $paramName . "'.",
                            [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]
                        );
                    }
                }
                $productIds = $cqs->handleProductIdsParam($params['product_ids']);
                $shopId = $cqs->handleShopParam($params['shop']);
                $cursor = $cqs->handleNextParam($params['next'] ?? null);
                # Cursor format is ['identifier_field_name_1' => id1, 'identifier_field_name_2' => id2]
                $batchSize = $cqs->handleBatchSizeParam($params['batch_size'] ?? null);
                $records = $cqs->getCombinationPage($shopId, $productIds, $batchSize, $cursor);
                $nextCursor = $cqs->getCursorValue($records, $batchSize);
                $normalized_records = $cqs->normalizeCombinationsData($records, $shopId);

                $this->jsonOutput = $cqs->buildCursorResultsPayload($normalized_records, $nextCursor);
                break;
        }
    }

    /**
     * Handle order related requests.
     *
     * @throws WebserviceException
     */
    private function handleOrdersRequest()
    {
        # Need to check params?
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
     * Handle coupons related requests.
     *
     * @throws WebserviceException
     */
    private function handleCouponsRequest()
    {
        $this->handleRequestMethod(['HEAD', 'GET', 'POST', 'DELETE']);

        $klaviyops = KlaviyoPsModule::getInstance();
        /** @var CartRuleService $cartRuleService */
        $cartRuleService = $klaviyops->getService('klaviyops.prestashop_services.cart_rule');
        /** @var DateTimeService $dateTimeService */
        $dateTimeService = $klaviyops->getService('klaviyops.prestashop_services.datetime');

        $wsObject = $this->getWsObject();

        switch ($wsObject->method) {
            case 'HEAD':
                $this->jsonOutput = '';
                break;
            case 'GET':
                try {
                    $cartRule = $cartRuleService->getObjectById($this->possibleObjectId);
                    $this->jsonOutput = CartRulePayloadService::buildPayload($cartRule);
                } catch (KlaviyoException $e) {
                    throw new WebserviceException(
                        $e->getMessage(),
                        [404, 404]
                    );
                }
                break;
            case 'POST':
                try {
                    $data = $this->extractRequestContent();

                    // Convert utc date
                    $data['active_start'] = $dateTimeService->convertFromUTC($data['active_start'] ?? null);
                    $data['active_end'] = $dateTimeService->convertFromUTC($data['active_end'] ?? null);

                    // Check request body
                    $params = ['cart_rule_id', 'count', 'prefix', 'active_start', 'active_end'];
                    foreach ($params as $key) {
                        switch ($key) {
                            case 'prefix':
                                $data[$key] = trim($data[$key]);
                                if (is_null($data[$key]) || $data[$key] === '') {
                                    $data[$key] = '';
                                }
                                break;

                            default:
                                if (empty($data[$key])) {
                                    throw new WebserviceException(
                                        sprintf("Bad request body, bad parameter '%s'.", $key),
                                        [400, 400]
                                    );
                                }
                                break;
                        }
                    }

                    $cartRules = CartRulePayloadService::generateFromId(
                        $data['cart_rule_id'],
                        (int)$data['count'],
                        $data['active_start'],
                        $data['active_end'],
                        $data['prefix']
                    );
                    $this->jsonOutput = CartRulePayloadService::buildPostPayload($cartRules);
                } catch (KlaviyoException $e) {
                    throw new WebserviceException(
                        $e->getMessage(),
                        [400, 400]
                    );
                }
                break;
            case 'DELETE':
                try {
                    $data = $this->extractRequestContent();
                    if (empty($data['codes']) || !is_array($data['codes'])) {
                        throw new KlaviyoException('Bad parameter codes sent');
                    }

                    $codesCnt = count($data['codes']);
                    if ($codesCnt > KlaviyoValue::WEBSERVICE_CREATE_DELETE_LIMIT) {
                        throw new KlaviyoException(
                            sprintf('The list of cart rules to delete is too long (limit=%s, request=%s).', KlaviyoValue::WEBSERVICE_CREATE_DELETE_LIMIT, $codesCnt)
                        );
                    }

                    $this->jsonOutput = $cartRuleService->deleteByCodes($data['codes']);
                    if (empty($this->jsonOutput)) {
                        $this->jsonOutput = '';
                    } else {
                        foreach ($this->jsonOutput as $code) {
                            $wsObject->setError(400, sprintf("The cart rule '%s' does not exist.", $code), 400);
                        }
                    }
                } catch (KlaviyoException $e) {
                    throw new WebserviceException(
                        $e->getMessage(),
                        [400, 400]
                    );
                }
                break;
        }
    }

    /**
     * Handles inventory route request.
     *
     * @return void
     * @throws WebserviceException
     */
    private function handleInventoryRequest()
    {
        $this->handleRequestMethod(['HEAD', 'GET']);
        $wsObject = $this->getWsObject();

        switch ($wsObject->method) {
            case 'HEAD':
                $this->jsonOutput = '';
                break;
            case 'GET':
                $iqs = new InventoryQueryService();
                $params = $wsObject->urlFragments;
                if (!isset($params['shop'])) {
                    throw new WebServiceException(
                        '\'shop\' parameter is required.',
                        [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]
                    );
                }

                $shopId = $iqs->handleShopParam($params['shop']);
                # Cursor format is ['identifier_field_name', id]
                $cursor = $iqs->handleNextParam($params['next'] ?? null);
                $batchSize = $iqs->handleBatchSizeParam($params['batch_size'] ?? null);

                $records = $iqs->getInventoryPage($shopId, $batchSize, $cursor);
                $nextCursor = $iqs->getCursorValue($records, $batchSize);

                $this->jsonOutput = $iqs->buildCursorResultsPayload($records, $nextCursor);
                break;
        }
    }

    /**
     * Handle inventory policy request
     *
     * @return void
     * @throws WebserviceException
     */
    private function handleInventoryPolicyRequest()
    {
        $this->handleRequestMethod(['GET']);
        $wsObject = $this->getWsObject();

        $iqs = new InventoryQueryService();
        $params = $wsObject->urlFragments;
        if (!isset($params['shop'])) {
            throw new WebServiceException(
                '\'shop\' parameter is required.',
                [QueryServiceInterface::DEFAULT_ERROR_CODE, 400]
            );
        }

        $shopId = $iqs->handleShopParam($params['shop']);

        # try to get shop record
        $record = $iqs->getInventoryPolicy($shopId);

        if (!$record) {
            # if there's no shop-specific record, return the global default (where id_shop = NULL)
            $record = $iqs->getInventoryPolicy();
        }

        if (!$record) {
            $this->jsonOutput = array();
        } else {
            $this->jsonOutput = $record[0];
        }
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
        if (ctype_digit((string) $text)) {
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

    /**
     * @return array|null
     */
    private function extractRequestContent()
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true);
    }
}
