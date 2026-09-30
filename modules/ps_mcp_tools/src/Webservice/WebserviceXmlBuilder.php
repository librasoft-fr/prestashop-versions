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
 * Builds XML for webservice requests (create/update operations)
 */
class WebserviceXmlBuilder
{
    /**
     * Build XML for entity creation/update
     *
     * @param string $resourceName Resource name (e.g., 'category', 'product')
     * @param array $data Entity data
     * @param int|null $entityId Entity ID for update (null for creation)
     *
     * @return string XML string
     */
    public function buildEntityXml(string $resourceName, array $data, ?int $entityId = null): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><prestashop></prestashop>');
        $entity = $xml->addChild($resourceName);

        if ($entityId !== null) {
            $entity->addChild('id', (string) $entityId);
        }

        $this->addFieldsToXml($entity, $data);

        $xmlString = $xml->asXML();
        if ($xmlString === false) {
            throw new \PrestaShopException('Failed to generate XML');
        }

        return $xmlString;
    }

    /**
     * Add fields to XML entity
     *
     * @param \SimpleXMLElement $entity XML entity element
     * @param array $data Data to add
     */
    private function addFieldsToXml(\SimpleXMLElement $entity, array $data): void
    {
        foreach ($data as $field => $value) {
            // Check if this is a known multilang field passed as string
            if (!is_array($value) && $this->isKnownMultilangField($field)) {
                // Convert to multilang format with default language
                $defaultLangId = (int) \Configuration::get('PS_LANG_DEFAULT');
                $value = [$defaultLangId => $value];
            }

            if (is_array($value)) {
                // Handle multilang fields or associations
                $isMultilang = $this->isMultilangField($value);
                $isAssociation = $this->isAssociationField($value);

                if ($isMultilang) {
                    $this->addMultilangFieldToXml($entity, $field, $value);
                } elseif ($isAssociation) {
                    $this->addAssociationToXml($entity, $field, $value);
                } else {
                    // Regular array field
                    $fieldNode = $entity->addChild($field);
                    $this->addFieldsToXml($fieldNode, $value);
                }
            } else {
                $entity->addChild($field, htmlspecialchars((string) $value));
            }
        }
    }

    /**
     * Check if a field name is a known multilang field
     *
     * @param string $fieldName Field name to check
     *
     * @return bool True if known multilang field
     */
    private function isKnownMultilangField(string $fieldName): bool
    {
        $knownMultilangFields = [
            'name',
            'description',
            'description_short',
            'link_rewrite',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'available_now',
            'available_later',
            'delivery_in_stock',
            'delivery_out_stock',
            'additional_description',
        ];

        return in_array($fieldName, $knownMultilangFields);
    }

    /**
     * Check if a value is a multilang field
     *
     * @param array $value Field value
     *
     * @return bool True if multilang field
     */
    private function isMultilangField(array $value): bool
    {
        // Multilang fields have numeric keys (language IDs)
        if (empty($value)) {
            return false;
        }
        $keys = array_keys($value);

        return is_numeric($keys[0]);
    }

    /**
     * Check if a value is an association field
     *
     * @param array $value Field value
     *
     * @return bool True if association field
     */
    private function isAssociationField(array $value): bool
    {
        // Check if array contains associative arrays with 'id' key
        if (empty($value)) {
            return false;
        }
        $firstElement = reset($value);

        return is_array($firstElement) && isset($firstElement['id']);
    }

    /**
     * Add multilang field to XML
     *
     * @param \SimpleXMLElement $entity Parent XML element
     * @param string $field Field name
     * @param array $value Multilang values (language_id => value)
     */
    private function addMultilangFieldToXml(\SimpleXMLElement $entity, string $field, array $value): void
    {
        $langNode = $entity->addChild($field);
        foreach ($value as $langId => $langValue) {
            $lang = $langNode->addChild('language');
            $lang->addAttribute('id', (string) $langId);

            // Handle CDATA for values that might contain HTML or special characters
            $langValue = (string) $langValue;
            if ($langValue !== '') {
                // Check if value contains HTML or special characters that need CDATA
                if (preg_match('/<[^>]+>/', $langValue) || strpos($langValue, '&') !== false) {
                    // Use CDATA for HTML content
                    $dom = dom_import_simplexml($lang);
                    $domDocument = $dom->ownerDocument;
                    if ($domDocument !== null) {
                        $dom->appendChild($domDocument->createCDATASection($langValue));
                    }
                } else {
                    // Simple text - use regular node value
                    $lang[0] = $langValue;
                }
            }
        }
    }

    /**
     * Add association to XML
     *
     * @param \SimpleXMLElement $entity Parent XML element
     * @param string $field Association name
     * @param array $value Association data
     */
    private function addAssociationToXml(\SimpleXMLElement $entity, string $field, array $value): void
    {
        $associationNode = $entity->addChild('associations')->addChild($field);
        foreach ($value as $item) {
            $itemNode = $associationNode->addChild(rtrim($field, 's')); // singular form
            foreach ($item as $key => $val) {
                $itemNode->addChild($key, htmlspecialchars((string) $val));
            }
        }
    }
}
