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
 * Parses and normalizes webservice responses (XML/JSON)
 */
class WebserviceResponseParser
{
    /**
     * Parse webservice XML/JSON response to array
     *
     * @param array $result Webservice response (includes 'content' and optionally 'request')
     *
     * @return array Parsed response as array
     *
     * @throws \PrestaShopException If response is invalid or parsing fails
     */
    public function parseWebserviceResponse(array $result): array
    {
        if (!isset($result['content'])) {
            throw new \PrestaShopException('Invalid webservice response');
        }

        // Parse content (XML or JSON)
        $parsed = $this->parseResponseContent($result['content']);

        // Check for errors
        $this->checkForResponseErrors($parsed);

        // Fix PrestaShop bug with list format (if request object is available)
        if (isset($result['request']) && $result['request'] instanceof \WebserviceRequest) {
            $parsed = $this->fixPrestaShopListBug($parsed, $result['request']);
        }

        // Normalize and return
        return $this->normalizeResponse($parsed);
    }

    /**
     * Parse response content from XML or JSON
     *
     * @param mixed $content Response content
     *
     * @return array Parsed array
     *
     * @throws \PrestaShopException If parsing fails
     */
    private function parseResponseContent($content): array
    {
        if (is_string($content) && strpos($content, '<?xml') === 0) {
            return $this->parseXmlContent($content);
        }

        return json_decode($content, true) ?? [];
    }

    /**
     * Parse XML content to array
     *
     * @param string $content XML content
     *
     * @return array Parsed array
     *
     * @throws \PrestaShopException If parsing fails
     */
    private function parseXmlContent(string $content): array
    {
        try {
            $xml = new \SimpleXMLElement($content);

            $result = $this->xmlToArray($xml);
            // xmlToArray can return string for leaf nodes, wrap it in array
            if (is_string($result)) {
                return ['value' => $result];
            }

            return $result;
        } catch (\Exception $e) {
            throw new \PrestaShopException('Failed to parse XML response: ' . $e->getMessage());
        }
    }

    /**
     * Convert SimpleXMLElement to array recursively, preserving text values
     *
     * @param \SimpleXMLElement $xml XML element
     *
     * @return array|string Converted array or string value
     */
    private function xmlToArray(\SimpleXMLElement $xml)
    {
        $children = $this->extractChildren($xml);
        $attributes = $this->extractAttributes($xml);
        $text = trim((string) $xml);

        return $this->buildResult($children, $attributes, $text);
    }

    /**
     * Build the final result based on children, attributes, and text
     *
     * @param array $children Child elements
     * @param array $attributes XML attributes
     * @param string $text Text content
     *
     * @return array|string Final result
     */
    private function buildResult(array $children, array $attributes, string $text)
    {
        $hasChildren = !empty($children);
        $hasAttributes = !empty($attributes);
        $hasText = $text !== '';

        // Priority 1: Attributes only (self-closing tags with attributes)
        if ($hasAttributes && !$hasChildren && !$hasText) {
            return $attributes;
        }

        // Priority 2: Children (unwrap language if needed)
        if ($hasChildren) {
            return $this->unwrapLanguageElement($children);
        }

        // Priority 3: Text or empty string
        return $text;
    }

    /**
     * Extract attributes from XML element
     *
     * @param \SimpleXMLElement $xml XML element
     *
     * @return array Attributes as key-value pairs
     */
    private function extractAttributes(\SimpleXMLElement $xml): array
    {
        $attributes = [];
        foreach ($xml->attributes() as $key => $value) {
            $attributes[(string) $key] = (string) $value;
        }

        return $attributes;
    }

    /**
     * Extract and process child elements from XML
     *
     * @param \SimpleXMLElement $xml XML element
     *
     * @return array Array of child elements
     */
    private function extractChildren(\SimpleXMLElement $xml): array
    {
        $children = [];
        foreach ($xml->children() as $name => $child) {
            $childArray = $this->xmlToArray($child);

            // If this child name already exists, convert to array
            if (isset($children[$name])) {
                if (!is_array($children[$name]) || !isset($children[$name][0])) {
                    $children[$name] = [$children[$name]];
                }
                $children[$name][] = $childArray;
            } else {
                $children[$name] = $childArray;
            }
        }

        return $children;
    }

    /**
     * Unwrap language elements to simplify structure
     * If element only contains 'language' children, return the language values directly
     *
     * @param array $children Child elements
     *
     * @return array|string Unwrapped language value(s) or original children
     */
    private function unwrapLanguageElement(array $children)
    {
        // Check if this element only contains 'language' children
        if (count($children) !== 1 || !isset($children['language'])) {
            return $children;
        }

        // Return language values directly (single string or array of strings)
        return $children['language'];
    }

    /**
     * Check for errors in parsed response
     *
     * @param array $parsed Parsed response
     *
     * @throws \PrestaShopException If errors found
     */
    private function checkForResponseErrors(array $parsed): void
    {
        if (!isset($parsed['errors']) || !is_array($parsed['errors']) || empty($parsed['errors'])) {
            return;
        }

        $errorMessages = [];
        foreach ($parsed['errors'] as $error) {
            $code = $error['code'] ?? 'unknown';
            $message = $error['message'] ?? 'Unknown error';
            $errorMessages[] = "[Error {$code}] {$message}";
        }

        throw new \PrestaShopException(implode('; ', $errorMessages));
    }

    /**
     * Fix PrestaShop bug where list responses are returned in singular format
     *
     * @param array $parsed Parsed response
     * @param \WebserviceRequest $request Request object
     *
     * @return array Fixed response
     */
    private function fixPrestaShopListBug(array $parsed, \WebserviceRequest $request): array
    {
        // Get resource names
        $resourceNamePlural = $request->urlSegment[0] ?? null;
        $resourceNameSingular = $resourceNamePlural ? rtrim($resourceNamePlural, 's') : null;

        // Check if bug is present
        if (!$resourceNameSingular || !isset($parsed[$resourceNameSingular]) || isset($parsed[$resourceNamePlural])) {
            return $parsed;
        }

        // Get valid objects
        $validObjects = $this->getValidObjects($request->objects);

        // Only fix if we have multiple objects
        if (count($validObjects) <= 1) {
            return $parsed;
        }

        // Rebuild response
        return $this->rebuildListResponse($validObjects, $request->fieldsToDisplay, $resourceNamePlural);
    }

    /**
     * Filter and return only valid objects
     *
     * @param mixed $objects Objects to filter
     *
     * @return array Valid objects
     */
    private function getValidObjects($objects): array
    {
        if (!is_array($objects)) {
            return [];
        }

        return array_filter($objects, function ($obj) {
            return is_object($obj) && isset($obj->id) && $obj->id > 0;
        });
    }

    /**
     * Rebuild list response with proper format
     *
     * @param array $validObjects Valid objects
     * @param mixed $fieldsToDisplay Fields to display
     * @param string $resourceNamePlural Plural resource name
     *
     * @return array Rebuilt response
     */
    private function rebuildListResponse(array $validObjects, $fieldsToDisplay, string $resourceNamePlural): array
    {
        $items = [];
        foreach ($validObjects as $obj) {
            $items[] = $this->convertObjectToArray($obj, $fieldsToDisplay);
        }

        return [$resourceNamePlural => $items];
    }

    /**
     * Convert object to array based on display filter
     *
     * @param object $obj Object to convert
     * @param mixed $fieldsToDisplay Fields to display
     *
     * @return array Converted array
     */
    private function convertObjectToArray($obj, $fieldsToDisplay): array
    {
        // Check if we should include all fields
        if ($fieldsToDisplay === 'full' || $fieldsToDisplay === 'all' || $fieldsToDisplay === '') {
            return $this->extractAllFields($obj);
        }

        // Return only ID for minimum mode
        return ['id' => property_exists($obj, 'id') ? $obj->id : 0];
    }

    /**
     * Extract all fields from an object
     *
     * @param object $obj Object
     *
     * @return array Extracted fields
     */
    private function extractAllFields($obj): array
    {
        if (method_exists($obj, 'getFields')) {
            $itemData = [];
            $fields = $obj->getFields();
            foreach (array_keys($fields) as $field) {
                if (is_string($field) && property_exists($obj, $field)) {
                    $itemData[$field] = $obj->$field;
                }
            }

            return $itemData;
        }

        return get_object_vars($obj);
    }

    /**
     * Normalize PrestaShop response to ensure consistent array structure
     * PrestaShop returns single items as objects, but multiple items as arrays
     * This method ensures single items are wrapped in an array when appropriate
     *
     * @param array|null $data Parsed response data
     *
     * @return array Normalized response
     */
    public function normalizeResponse(?array $data): array
    {
        if ($data === null) {
            return [];
        }

        // Check if this is a list response (e.g., categories, products)
        // List responses have a plural resource name as the key
        foreach ($data as $key => $value) {
            if (is_array($value) && $this->isPluralResourceKey($key)) {
                // Get the singular form (e.g., 'categories' -> 'category')
                $singularKey = rtrim($key, 's');

                // If the singular key exists and is not an array of items, wrap it
                if (isset($value[$singularKey])) {
                    // Check if it's a single item (has 'id' key) or an array of items
                    $isSingle = $this->isSingleItem($value[$singularKey]);

                    if ($isSingle) {
                        // Single item - wrap it in an array
                        $data[$key][$singularKey] = [$value[$singularKey]];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Check if a key is a plural resource name
     *
     * @param string $key Key to check
     *
     * @return bool True if plural resource key
     */
    private function isPluralResourceKey(string $key): bool
    {
        $pluralResources = [
            'categories',
            'products',
            'customers',
            'orders',
            'manufacturers',
            'suppliers',
            'addresses',
            'countries',
            'states',
            'zones',
            'groups',
            'languages',
            'currencies',
            'images',
            'combinations',
            'product_options',
            'product_option_values',
        ];

        return in_array($key, $pluralResources);
    }

    /**
     * Check if data represents a single item (not an array of items)
     *
     * @param mixed $data Data to check
     *
     * @return bool True if single item
     */
    private function isSingleItem($data): bool
    {
        // Must be an array with 'id' key and not a list
        return is_array($data)
            && isset($data['id'])
            && !$this->isSequentialArray($data)
            && !$this->isListOfItems($data);
    }

    /**
     * Check if array is sequential (numeric keys starting from 0)
     *
     * @param array $data Array to check
     *
     * @return bool True if sequential
     */
    private function isSequentialArray(array $data): bool
    {
        $keys = array_keys($data);

        return isset($keys[0]) && is_int($keys[0]) && $keys[0] === 0;
    }

    /**
     * Check if data is a list of items (first element is array with id)
     *
     * @param array $data Array to check
     *
     * @return bool True if list
     */
    private function isListOfItems(array $data): bool
    {
        $firstElement = reset($data);

        return is_array($firstElement) && isset($firstElement['id']);
    }
}
