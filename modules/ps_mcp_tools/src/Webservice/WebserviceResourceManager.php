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
 * Manages CRUD operations for webservice resources
 */
class WebserviceResourceManager
{
    /** @var WebserviceRequestExecutor */
    private $requestExecutor;

    /** @var WebserviceResponseParser */
    private $responseParser;

    /** @var WebserviceXmlBuilder */
    private $xmlBuilder;

    /**
     * Constructor
     *
     * @param WebserviceRequestExecutor $requestExecutor Request executor
     * @param WebserviceResponseParser $responseParser Response parser
     * @param WebserviceXmlBuilder $xmlBuilder XML builder
     */
    public function __construct(
        WebserviceRequestExecutor $requestExecutor,
        WebserviceResponseParser $responseParser,
        WebserviceXmlBuilder $xmlBuilder,
    ) {
        $this->requestExecutor = $requestExecutor;
        $this->responseParser = $responseParser;
        $this->xmlBuilder = $xmlBuilder;
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
    public function getResourceList(
        string $resource,
        string $display,
        string $filter = '',
        string $sort = '',
        string $limit = '10',
        ?int $langId = null,
    ): array {
        $params = [];

        // Display is now required - no default value
        $params['display'] = $display;

        // Parse filter parameter to proper format
        $params['filter'] = $this->parseFilterString($filter);

        if ($sort !== '') {
            $params['sort'] = $sort;
        }
        if ($limit !== '') {
            $params['limit'] = $limit;
        }
        if ($langId !== null) {
            $params['language'] = $langId;
        }

        $result = $this->requestExecutor->executeWebserviceRequest('GET', $resource, $params);

        return $this->responseParser->parseWebserviceResponse($result);
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
    public function getResourceById(string $resource, int $resourceId, string $display, ?int $langId = null): array
    {
        $params = [];

        // Display is now required - no default value
        $params['display'] = $display;

        if ($langId !== null) {
            $params['language'] = $langId;
        }

        // Detect if we need to force XML format (required for images and some other resources)
        // For images, PrestaShop API only supports XML format
        $forceXml = $this->shouldForceXmlFormat($resource);

        if (!$forceXml) {
            // Use JSON format for better performance when supported
            $params['output_format'] = 'JSON';
        }
        // If forceXml is true, we let executeWebserviceRequest use its default (XML)

        $result = $this->requestExecutor->executeWebserviceRequest('GET', "{$resource}/{$resourceId}", $params);

        // parseWebserviceResponse handles both XML and JSON formats
        // and always returns an array (parsed from JSON structure)
        return $this->responseParser->parseWebserviceResponse($result);
    }

    /**
     * Execute POST request to create a new resource
     *
     * @param string $resource Resource name (e.g., 'categories', 'products')
     * @param array $data Resource data
     *
     * @return array Parsed response
     */
    public function createResource(string $resource, array $data): array
    {
        $resourceSingular = rtrim($resource, 's'); // Convert plural to singular (categories -> category)
        $xml = $this->xmlBuilder->buildEntityXml($resourceSingular, $data);
        $result = $this->requestExecutor->executeWebserviceRequest('POST', $resource, ['output_format' => 'JSON'], $xml);

        return $this->responseParser->parseWebserviceResponse($result);
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
    public function updateResource(string $resource, int $resourceId, array $data): array
    {
        $resourceSingular = rtrim($resource, 's');
        $xml = $this->xmlBuilder->buildEntityXml($resourceSingular, $data, $resourceId);
        $result = $this->requestExecutor->executeWebserviceRequest('PATCH', "{$resource}/{$resourceId}", ['output_format' => 'JSON'], $xml);

        return $this->responseParser->parseWebserviceResponse($result);
    }

    /**
     * Execute DELETE request to remove a resource
     *
     * @param string $resource Resource URL (e.g., 'products/1', 'images/products/1/2')
     *
     * @return array Parsed response
     */
    public function deleteResource(string $resource): array
    {
        $result = $this->requestExecutor->executeWebserviceRequest('DELETE', $resource, ['output_format' => 'JSON']);

        return $this->responseParser->parseWebserviceResponse($result);
    }

    /**
     * Parse filter string into associative array compatible with PrestaShop webservice
     *
     * Supports PrestaShop filter syntax:
     * - Literal: "active=1" => ['active' => '1']
     * - OR operator: "id=[1|5]" => ['id' => '[1|5]']
     * - Interval: "id=[1,10]" => ['id' => '[1,10]']
     * - Begin: "name=[John]%" => ['name' => '[John]%']
     * - End: "name=%[hn]" => ['name' => '%[hn]']
     * - Contains: "name=%[oh]%" => ['name' => '%[oh]%']
     * - Multiple: "active=1,id_parent=2" => ['active' => '1', 'id_parent' => '2']
     *
     * @param string $filter Filter string
     *
     * @return array Parsed filters as ['field' => 'value']
     */
    private function parseFilterString(string $filter): array
    {
        $result = [];
        $posisiton = 0;
        $length = strlen($filter);

        while ($posisiton < $length) {
            // Find field name (everything before '=')
            $equalPos = strpos($filter, '=', $posisiton);

            if ($equalPos === false) {
                break;
            }

            $field = trim(substr($filter, $posisiton, $equalPos - $posisiton));

            // Find value (until next comma, but skip commas inside brackets)
            $valueStart = $equalPos + 1;
            $valueEnd = $this->findFilterValueEnd($filter, $valueStart);
            $value = substr($filter, $valueStart, $valueEnd - $valueStart);

            $result[$field] = $value;

            // Move to next filter (skip comma)
            $posisiton = $valueEnd + 1;
        }

        return $result;
    }

    /**
     * Find the end position of a filter value, handling brackets correctly
     * Commas inside brackets should not terminate the value
     *
     * @param string $filter Full filter string
     * @param int $start Start position
     *
     * @return int End position
     */
    private function findFilterValueEnd(string $filter, int $start): int
    {
        $len = strlen($filter);
        $bracketDepth = 0;
        $inPercent = false;

        for ($i = $start; $i < $len; ++$i) {
            $char = $filter[$i];

            if ($char === '[') {
                ++$bracketDepth;
            } elseif ($char === ']') {
                --$bracketDepth;
            } elseif ($char === '%') {
                $inPercent = !$inPercent;
            } elseif ($char === ',' && $bracketDepth === 0 && !$inPercent) {
                // Found separator comma (not inside brackets or percent markers)
                return $i;
            }
        }

        return $len;
    }

    /**
     * Check if a resource requires XML format
     * Some PrestaShop resources (like images) only work with XML format
     *
     * @param string $resource Resource name
     *
     * @return bool True if XML format should be forced
     */
    private function shouldForceXmlFormat(string $resource): bool
    {
        // Resources that require XML format
        $xmlOnlyResources = [
            'images',
        ];

        // Check if resource starts with any XML-only resource
        foreach ($xmlOnlyResources as $xmlResource) {
            if (strpos($resource, $xmlResource) === 0) {
                return true;
            }
        }

        return false;
    }
}
