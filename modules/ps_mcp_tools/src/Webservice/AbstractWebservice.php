<?php
/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Tools module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Tools module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpTools\Webservice;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Abstract class for tools that interact with PrestaShop webservices programmatically
 * This class orchestrates the webservice components and provides a simplified API
 */
abstract class AbstractWebservice
{
    /** @var bool Whether error suppression is active */
    private static $errorSuppressionActive = false;

    /** @var WebserviceKeyManager */
    protected $keyManager;

    /** @var WebserviceRequestExecutor */
    protected $requestExecutor;

    /** @var WebserviceResponseParser */
    protected $responseParser;

    /** @var WebserviceXmlBuilder */
    protected $xmlBuilder;

    /** @var WebserviceResourceManager */
    protected $resourceManager;

    /** @var WebserviceBinaryHandler */
    protected $binaryHandler;

    /**
     * Constructor - Initialize error handling and webservice components
     */
    public function __construct()
    {
        $this->initializeErrorHandling();
        $this->initializeComponents();
    }

    /**
     * Initialize error handling to prevent warnings from breaking JSON-RPC responses
     */
    protected function initializeErrorHandling(): void
    {
        if (!self::$errorSuppressionActive) {
            // Save errors to log file instead of displaying them
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');

            // Set a custom error handler to catch warnings and errors
            set_error_handler(function () {
                // Don't execute PHP internal error handler
                // Parameters are intentionally not used as we suppress all errors
                return true;
            }, E_ALL);

            self::$errorSuppressionActive = true;
        }
    }

    /**
     * Initialize webservice components
     */
    protected function initializeComponents(): void
    {
        // Create components in the correct order (respecting dependencies)
        $this->keyManager = new WebserviceKeyManager();
        $this->requestExecutor = new WebserviceRequestExecutor($this->keyManager);
        $this->responseParser = new WebserviceResponseParser();
        $this->xmlBuilder = new WebserviceXmlBuilder();
        $this->resourceManager = new WebserviceResourceManager(
            $this->requestExecutor,
            $this->responseParser,
            $this->xmlBuilder
        );
        $this->binaryHandler = new WebserviceBinaryHandler(
            $this->keyManager,
            $this->requestExecutor
        );
    }

    /**
     * Get the base URL for the webservice API
     *
     * @return string Base URL (e.g., 'https://domain.com/api')
     */
    protected function getWebserviceBaseUrl(): string
    {
        return $this->requestExecutor->getWebserviceBaseUrl();
    }

    /**
     * Get or create an internal webservice key for MCP tools
     *
     * @return string Webservice key
     *
     * @throws \PrestaShopException If webservices are not enabled or key cannot be created
     */
    protected function getInternalWebserviceKey(): string
    {
        return $this->keyManager->getInternalWebserviceKey();
    }

    /**
     * Execute a webservice request directly using WebserviceRequest::fetch()
     * This bypasses HTTP/cURL and calls the webservice logic directly
     *
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $url Resource URL (e.g., 'categories' or 'categories/1')
     * @param array $params Query parameters
     * @param string $inputXml XML input for POST/PUT/PATCH operations
     *
     * @return array Webservice response with 'content', 'code' keys, and optionally 'request' object
     *
     * @throws \PrestaShopException If request fails
     */
    protected function executeWebserviceRequest(string $method, string $url, array $params = [], string $inputXml = ''): array
    {
        return $this->requestExecutor->executeWebserviceRequest($method, $url, $params, $inputXml);
    }

    /**
     * Parse webservice XML/JSON response to array
     *
     * @param array $result Webservice response (includes 'content' and optionally 'request')
     *
     * @return array Parsed response as array
     *
     * @throws \PrestaShopException If response is invalid or parsing fails
     */
    protected function parseWebserviceResponse(array $result): array
    {
        return $this->responseParser->parseWebserviceResponse($result);
    }

    /**
     * Normalize PrestaShop response to ensure consistent array structure
     *
     * @param array|null $data Parsed response data
     *
     * @return array Normalized response
     */
    protected function normalizeResponse(?array $data): array
    {
        return $this->responseParser->normalizeResponse($data);
    }

    /**
     * Build XML for entity creation/update
     *
     * @param string $resourceName Resource name (e.g., 'category', 'product')
     * @param array $data Entity data
     * @param int|null $entityId Entity ID for update (null for creation)
     *
     * @return string XML string
     */
    protected function buildEntityXml(string $resourceName, array $data, ?int $entityId = null): string
    {
        return $this->xmlBuilder->buildEntityXml($resourceName, $data, $entityId);
    }

    /**
     * Execute GET request for a list of resources
     *
     * @param string $resource Resource name (e.g., 'categories', 'products')
     * @param string $display Display parameter (full, [field1,field2], etc.)
     * @param string $filter Filter parameter (e.g., 'active=[0|1]' or 'active=[0|1],id_parent=2')
     * @param string $sort Sort parameter (e.g., 'id_ASC')
     * @param string $limit Limit parameter (e.g., '10' or '0,10')
     * @param int|null $langId Language ID to filter multilang fields
     *
     * @return array Parsed response
     */
    protected function getResourceList(
        string $resource,
        string $display,
        string $filter = '',
        string $sort = '',
        string $limit = '10',
        ?int $langId = null,
    ): array {
        return $this->resourceManager->getResourceList($resource, $display, $filter, $sort, $limit, $langId);
    }

    /**
     * Execute GET request for a specific resource by ID
     *
     * @param string $resource Resource name (e.g., 'categories', 'products', 'images/products')
     * @param int $resourceId Resource ID
     * @param string $display Display parameter
     * @param int|null $langId Language ID to filter multilang fields
     *
     * @return array Parsed response (always returns array parsed from JSON structure)
     */
    protected function getResourceById(string $resource, int $resourceId, string $display, ?int $langId = null): array
    {
        return $this->resourceManager->getResourceById($resource, $resourceId, $display, $langId);
    }

    /**
     * Execute POST request to create a new resource
     *
     * @param string $resource Resource name (e.g., 'categories', 'products')
     * @param array $data Resource data
     *
     * @return array Parsed response
     */
    protected function createResource(string $resource, array $data): array
    {
        return $this->resourceManager->createResource($resource, $data);
    }

    /**
     * Execute DELETE request to remove a resource
     *
     * @param string $resource Resource URL (e.g., 'products/1', 'images/products/1/2')
     *
     * @return array Parsed response
     */
    protected function deleteResource(string $resource): array
    {
        return $this->resourceManager->deleteResource($resource);
    }

    /**
     * Upload a binary file (like an image) via webservice
     * This is specific for image uploads which require multipart/form-data
     *
     * @param string $url Resource URL (e.g., 'images/products/1')
     * @param string $filePath Path to the file to upload
     *
     * @return array Parsed response
     *
     * @throws \PrestaShopException If upload fails
     */
    protected function uploadBinaryFile(string $url, string $filePath): array
    {
        return $this->binaryHandler->uploadBinaryFile($url, $filePath);
    }

    /**
     * Execute PATCH request to update a resource
     * PATCH only updates the provided fields, while PUT would replace the entire resource
     *
     * @param string $resource Resource name (e.g., 'categories', 'products')
     * @param int $resourceId Resource ID
     * @param array $data Resource data to update (only the fields to modify)
     *
     * @return array Parsed response
     */
    protected function updateResource(string $resource, int $resourceId, array $data): array
    {
        return $this->resourceManager->updateResource($resource, $resourceId, $data);
    }
}
