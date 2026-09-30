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
 * Manages webservice keys (creation, retrieval, permissions)
 */
class WebserviceKeyManager
{
    /** @var string|null Cached webservice key */
    private static $cachedKey;

    /**
     * Get or create an internal webservice key for MCP tools
     *
     * @return string Webservice key
     *
     * @throws \PrestaShopException If webservices are not enabled or key cannot be created
     */
    public function getInternalWebserviceKey(): string
    {
        // Return cached key if available
        if (self::$cachedKey !== null) {
            return self::$cachedKey;
        }

        // Check if webservices are enabled
        if (!\Configuration::get('PS_WEBSERVICE')) {
            throw new \PrestaShopException('PrestaShop webservices are not enabled. Please enable them in Back Office > Advanced Parameters > Webservice');
        }

        // Try to find existing MCP internal key
        $existingKey = $this->findExistingMcpKey();

        if ($existingKey !== null) {
            self::$cachedKey = $existingKey;

            return $existingKey;
        }

        // No existing key found - create new internal key
        $key = $this->createInternalWebserviceKey();
        self::$cachedKey = $key;

        return $key;
    }

    /**
     * Create an internal webservice key with full permissions
     *
     * @return string Generated key
     *
     * @throws \PrestaShopException If key creation fails
     */
    private function createInternalWebserviceKey(): string
    {
        // Check if key already exists (race condition)
        $existingKey = $this->findExistingMcpKey();
        if ($existingKey !== null) {
            return $existingKey;
        }

        // Create new key
        $key = $this->generateWebserviceKey();
        $wsKey = $this->createWebserviceKeyObject($key);

        // Set permissions (wsKey->id is guaranteed to be non-null by createWebserviceKeyObject)
        $permissions = $this->buildFullPermissions();
        $this->setWebservicePermissions((int) $wsKey->id, $permissions);

        return $key;
    }

    /**
     * Find existing MCP internal key
     *
     * @return string|null Key if found, null otherwise
     */
    private function findExistingMcpKey(): ?string
    {
        $sql = 'SELECT k.`key`
                FROM `' . _DB_PREFIX_ . 'webservice_account` k
                WHERE k.`description` = "MCP Internal Key"
                AND k.`active` = 1';

        $existingKey = \Db::getInstance()->getValue($sql);

        return ($existingKey !== false && $existingKey !== null && $existingKey !== '') ? $existingKey : null;
    }

    /**
     * Generate a new webservice key
     *
     * @return string Generated key
     */
    private function generateWebserviceKey(): string
    {
        return strtoupper(bin2hex(random_bytes(16))); // 32 chars hex
    }

    /**
     * Create and save WebserviceKey object
     *
     * @param string $key Key to create
     *
     * @return \WebserviceKey Created object
     *
     * @throws \PrestaShopException If creation fails
     */
    private function createWebserviceKeyObject(string $key): \WebserviceKey
    {
        $wsKey = new \WebserviceKey();
        $wsKey->key = $key;
        $wsKey->active = true;
        $wsKey->description = 'MCP Internal Key';

        if (!$wsKey->add()) {
            $this->handleKeyCreationFailure($wsKey);
        }

        if (!$wsKey->id) {
            throw new \PrestaShopException('Webservice key created but ID is empty');
        }

        return $wsKey;
    }

    /**
     * Handle webservice key creation failure
     *
     * @param \WebserviceKey $wsKey Failed key object
     *
     * @throws \PrestaShopException Always throws
     */
    private function handleKeyCreationFailure(\WebserviceKey $wsKey): void
    {
        // Check one more time if key exists (maybe creation failed because it already exists)
        $existingKey = $this->findExistingMcpKey();
        if ($existingKey !== null) {
            throw new \PrestaShopException('Key already exists: ' . $existingKey);
        }

        // Get validation errors if available
        $errors = 'Unknown error';
        if (property_exists($wsKey, '_errors') && is_array($wsKey->_errors) && !empty($wsKey->_errors)) {
            $errors = implode(', ', $wsKey->_errors);
        }

        throw new \PrestaShopException('Failed to create internal webservice key: ' . $errors);
    }

    /**
     * Build full permissions array for all resources
     *
     * @return array Permissions array
     */
    private function buildFullPermissions(): array
    {
        $resources = \WebserviceRequest::getResources();
        $permissions = [];

        foreach (array_keys($resources) as $resource) {
            $permissions[$resource] = ['GET' => 1, 'POST' => 1, 'PUT' => 1, 'PATCH' => 1, 'DELETE' => 1, 'HEAD' => 1];
        }

        return $permissions;
    }

    /**
     * Set webservice permissions for a key
     *
     * @param int $keyId Key ID
     * @param array $permissions Permissions array
     */
    private function setWebservicePermissions(int $keyId, array $permissions): void
    {
        // Initialize shop context if needed
        $this->ensureShopContext();

        try {
            $result = \WebserviceKey::setPermissionForAccount($keyId, $permissions);
            if (!$result) {
                throw new \PrestaShopException('setPermissionForAccount returned false');
            }
        } catch (\Exception) {
            // Try direct SQL insert as fallback
            $this->setPermissionsDirectly($keyId, $permissions);
        }
    }

    /**
     * Ensure shop context is initialized
     */
    private function ensureShopContext(): void
    {
        $context = \Context::getContext();

        if ($context === null || ($context->shop && $context->shop->id)) {
            return;
        }

        $shopId = (int) \Configuration::get('PS_SHOP_DEFAULT') ?: 1;
        $context->shop = new \Shop($shopId);
    }

    /**
     * Set webservice permissions directly via SQL (fallback method)
     *
     * @param int $keyId Webservice key ID
     * @param array $permissions Permissions array
     */
    private function setPermissionsDirectly(int $keyId, array $permissions): void
    {
        $db = \Db::getInstance();

        foreach ($permissions as $resource => $methods) {
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'webservice_permission`
                    (`id_webservice_account`, `resource`, `method`, `id_shop`)
                    VALUES ';

            $values = [];
            foreach ($methods as $method => $allowed) {
                if ($allowed) {
                    $values[] = '(' . (int) $keyId . ', \'' . pSQL($resource) . '\', \'' . pSQL($method) . '\', 1)';
                }
            }

            if (!empty($values)) {
                $sql .= implode(',', $values);
                $db->execute($sql);
            }
        }
    }
}
