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
 * Executes webservice requests directly using WebserviceRequest::fetch()
 */
class WebserviceRequestExecutor
{
    /** @var WebserviceKeyManager */
    private $keyManager;

    /**
     * Constructor
     *
     * @param WebserviceKeyManager $keyManager Key manager
     */
    public function __construct(WebserviceKeyManager $keyManager)
    {
        $this->keyManager = $keyManager;
    }

    /**
     * Get the base URL for the webservice API
     *
     * @return string Base URL (e.g., 'https://domain.com/api')
     */
    public function getWebserviceBaseUrl(): string
    {
        $context = \Context::getContext();
        if ($context === null || $context->shop === null) {
            throw new \PrestaShopException('Context or Shop is not available');
        }

        $shopUrl = $context->shop->getBaseURL(true);
        if (!is_string($shopUrl)) {
            throw new \PrestaShopException('Failed to get shop base URL');
        }

        // Remove trailing slash if present
        $shopUrl = rtrim($shopUrl, '/');

        return $shopUrl . '/api';
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
    public function executeWebserviceRequest(string $method, string $url, array $params = [], string $inputXml = ''): array
    {
        // Get internal webservice key
        $key = $this->keyManager->getInternalWebserviceKey();

        // Ensure output_format is set to XML or JSON
        if (!isset($params['output_format'])) {
            $params['output_format'] = 'XML';
        }

        // Reset WebserviceRequest singleton to avoid state issues
        \WebserviceRequest::resetStaticCache();

        // Get WebserviceRequest instance
        \WebserviceRequest::$ws_current_classname = 'WebserviceRequest';
        $request = \WebserviceRequest::getInstance();

        if (!($request instanceof \WebserviceRequest)) {
            throw new \PrestaShopException('Failed to get WebserviceRequest instance');
        }

        // WORKAROUND: PrestaShop reads many parameters from $_GET instead of $params
        // We need to temporarily set $_GET to include our params, but preserve existing values
        $savedGet = $_GET;
        $_GET = array_merge($_GET, $params);

        try {
            // Call fetch() directly - this does all the webservice logic without HTTP
            $result = $request->fetch(
                $key,           // Authentication key
                $method,        // HTTP method
                $url,           // Resource URL (e.g., 'products', 'products/1')
                $params,        // Query parameters (filter, display, sort, limit, language, etc.)
                '',             // Bad class name check (empty string instead of false)
                $inputXml       // XML input for POST/PUT/PATCH
            );

            // Restore $_GET immediately after fetch
            $_GET = $savedGet;

            // Check for errors in the response
            if (isset($result['type']) && $result['type'] === 'error') {
                throw new \PrestaShopException('Webservice error: ' . $result['content']);
            }

            // Return the result with the request object so we can access internal state
            return [
                'content' => $result['content'] ?? '',
                'code' => $result['code'] ?? 200,
                'request' => $request,  // Include request object for debugging
            ];
        } catch (\Exception $e) {
            // Restore $_GET even on error
            $_GET = $savedGet;
            throw new \PrestaShopException($e->getMessage(), 0, $e);
        }
    }
}
