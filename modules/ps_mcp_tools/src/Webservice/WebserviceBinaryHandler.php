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
 * Handles binary file uploads (images) via webservice
 */
class WebserviceBinaryHandler
{
    /** @var WebserviceKeyManager */
    private $keyManager;

    /** @var WebserviceRequestExecutor */
    private $requestExecutor;

    /**
     * Constructor
     *
     * @param WebserviceKeyManager $keyManager Key manager
     * @param WebserviceRequestExecutor $requestExecutor Request executor
     */
    public function __construct(
        WebserviceKeyManager $keyManager,
        WebserviceRequestExecutor $requestExecutor,
    ) {
        $this->keyManager = $keyManager;
        $this->requestExecutor = $requestExecutor;
    }

    /**
     * Upload a binary file (like an image) via webservice
     * Uses multipart/form-data with 'image' parameter
     *
     * @param string $url Resource URL (e.g., 'images/products/1')
     * @param string $filePath Path to the file to upload
     *
     * @return array Parsed response
     *
     * @throws \PrestaShopException If upload fails
     */
    public function uploadBinaryFile(string $url, string $filePath): array
    {
        // Get internal webservice key
        $key = $this->keyManager->getInternalWebserviceKey();

        // Build full URL (use XML format for better response parsing)
        $baseUrl = $this->requestExecutor->getWebserviceBaseUrl();
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $fullUrl = $baseUrl . '/' . $url . $separator . 'ws_key=' . $key;

        // Get the MIME type and extension
        $mimeType = mime_content_type($filePath);
        $extension = $this->getExtensionFromMimeType($mimeType);

        // At this point, $mimeType is guaranteed to be a string (exception would have been thrown otherwise)
        if (!is_string($mimeType)) {
            throw new \PrestaShopException('MIME type detection failed');
        }

        // Create CURLFile for multipart upload
        // Use a filename with proper extension so PrestaShop can detect the image format
        $filename = 'image.' . $extension;
        $cfile = new \CURLFile($filePath, $mimeType, $filename);

        // Prepare cURL for multipart upload
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set POST fields with 'image' parameter (required by PrestaShop)
        $postFields = ['image' => $cfile];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || is_bool($response)) {
            throw new \PrestaShopException('cURL error: ' . $error);
        }

        // Check for errors in response
        if ($httpCode >= 400) {
            throw new \PrestaShopException('HTTP error ' . $httpCode . ': ' . $response);
        }

        // Parse the XML response and extract only the <image> node, not <content>
        // PrestaShop returns: <prestashop><image>...</image><content encode="base64">...</content></prestashop>
        // We only need the <image> part which contains the image ID and metadata
        $cleanedResponse = $this->extractImageNodeFromResponse($response);

        return [
            'content' => $cleanedResponse,
            'code' => $httpCode,
        ];
    }

    /**
     * Extract the <image> node from the webservice response
     * Removes the <content> node which contains the base64-encoded image data
     *
     * @param string $response Full XML response
     *
     * @return string XML with only the <image> node
     */
    private function extractImageNodeFromResponse(string $response): string
    {
        try {
            // Parse the full XML response
            $xml = new \SimpleXMLElement($response);

            // Check if <image> node exists
            if (!isset($xml->image)) {
                // If no <image> node, return the full response (might be an error message)
                return $response;
            }

            // Create a new XML document with only the <prestashop><image>...</image></prestashop> structure
            $cleanXml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><prestashop xmlns:xlink="http://www.w3.org/1999/xlink"></prestashop>');

            // Copy the <image> node to the new XML
            $this->copyXmlNode($xml->image, $cleanXml);

            $result = $cleanXml->asXML();
            // asXML() can return false on error, handle that case
            if ($result === false) {
                return $response;
            }

            return $result;
        } catch (\Exception) {
            // If parsing fails, return the original response
            return $response;
        }
    }

    /**
     * Copy an XML node and all its children to a parent node
     *
     * @param \SimpleXMLElement $source Source node to copy
     * @param \SimpleXMLElement $target Parent node to copy to
     */
    private function copyXmlNode(\SimpleXMLElement $source, \SimpleXMLElement $target): void
    {
        // Add the source node as a child of target
        $newNode = $target->addChild($source->getName());

        // Copy attributes
        foreach ($source->attributes() as $attrName => $attrValue) {
            $newNode->addAttribute($attrName, (string) $attrValue);
        }

        // Copy text content (if it's a leaf node)
        if (count($source->children()) === 0) {
            // Use dom_import_simplexml to properly handle CDATA
            $targetDom = dom_import_simplexml($newNode);
            $sourceDom = dom_import_simplexml($source);
            // dom_import_simplexml returns DOMElement which is always truthy, but check ownerDocument
            if ($targetDom->ownerDocument !== null) {
                $targetDom->nodeValue = $sourceDom->nodeValue;
            }
        }

        // Recursively copy children
        foreach ($source->children() as $child) {
            $this->copyXmlNode($child, $newNode);
        }

        // Copy namespace attributes (like xlink:href)
        foreach ($source->attributes('xlink', true) as $attrName => $attrValue) {
            $newNode->addAttribute('xlink:' . $attrName, (string) $attrValue, 'http://www.w3.org/1999/xlink');
        }
    }

    /**
     * Get file extension from MIME type
     * Maps common image MIME types to their file extensions
     *
     * @param string|false $mimeType MIME type (e.g., 'image/jpeg')
     *
     * @return string File extension (e.g., 'jpg')
     *
     * @throws \PrestaShopException If MIME type is not supported
     */
    private function getExtensionFromMimeType($mimeType): string
    {
        // Handle false return from mime_content_type()
        if ($mimeType === false) {
            throw new \PrestaShopException('Unable to detect MIME type');
        }

        // Map MIME types to extensions (PrestaShop allowed formats: gif, jpg, jpeg, jpe, png, webp)
        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (isset($mimeToExtension[$mimeType])) {
            return $mimeToExtension[$mimeType];
        }

        throw new \PrestaShopException("Unsupported image MIME type: {$mimeType}. Allowed formats: gif, jpg, jpeg, jpe, png, webp");
    }
}
