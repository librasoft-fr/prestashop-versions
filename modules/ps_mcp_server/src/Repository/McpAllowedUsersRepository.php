<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpAllowedUsersRepository
{
    public const TABLE_NAME = 'mcp_server_allowed_users';
    private const DATETIME_FORMAT = 'Y-m-d H:i:s';

    private $db;

    public function __construct()
    {
        $this->db = \Db::getInstance();
    }

    public function getAllAllowedUsers(): array
    {
        $query = new \DbQuery();
        $query->select('mcp_au.email, mcp_au.role, mcp_au.created_at, mcp_au.updated_at, IF(mcp_au.token_hash != "", 1, 0) AS has_token')
            ->from(self::TABLE_NAME, 'mcp_au')
            ->orderBy('mcp_au.created_at DESC');

        $result = $this->db->executeS($query);

        return is_array($result) ? $result : [];
    }

    public function getUserByEmail(string $email): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME, 'mcp_au')
            ->where("mcp_au.email = '" . pSQL($email) . "'");

        $result = $this->db->getRow($query);

        return is_array($result) ? $result : [];
    }

    public function addAllowedUser(string $email, string $role, ?string $tokenHash): array|bool
    {
        $data = [
            'email' => pSQL($email),
            'role' => pSQL($role),
            'token_hash' => $tokenHash !== null ? pSQL($tokenHash) : null,
            'created_at' => date(self::DATETIME_FORMAT),
            'updated_at' => date(self::DATETIME_FORMAT),
        ];

        $this->db->insert(self::TABLE_NAME, $data);

        return $data;
    }

    public function updateTokenForUser(string $email, string $tokenHash): array|bool
    {
        $result = $this->db->update(self::TABLE_NAME, [
            'token_hash' => pSQL($tokenHash),
            'updated_at' => date(self::DATETIME_FORMAT),
        ], 'email = "' . pSQL($email) . '"');

        return $result ? $result : false;
    }

    public function updateRoleForUser(string $email, string $role): array|bool
    {
        $result = $this->db->update(self::TABLE_NAME, [
            'role' => pSQL($role),
            'updated_at' => date(self::DATETIME_FORMAT),
        ], 'email = "' . pSQL($email) . '"');

        return $result ? $result : false;
    }

    public function deleteAllowedUser(string $email): bool
    {
        return $this->db->delete(self::TABLE_NAME, 'email = "' . pSQL($email) . '"');
    }
}
