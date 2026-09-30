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

namespace PrestaShop\Module\PsMcpServer\Services;

use PrestaShop\Module\PsMcpServer\Repository\McpAllowedUsersRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class McpAllowedUsersService
{
    private $repository;

    public function __construct(McpAllowedUsersRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllAllowedUsers(): array
    {
        $allowedUsers = $this->repository->getAllAllowedUsers();

        foreach ($allowedUsers as &$user) {
            $user['has_token'] = (bool) $user['has_token'];
        }

        return $allowedUsers;
    }

    public function getUserByEmail(string $email): array
    {
        return $this->repository->getUserByEmail($email);
    }

    public function addAllowedUser(string $email, string $role, bool $generateToken = true): array|bool
    {
        if ($this->getUserByEmail($email)) {
            return false;
        }

        $tokenResult = $generateToken ? $this->generateTokenForUser($email) : null;
        $tokenHash = $tokenResult ? $tokenResult['token_hash'] : null;
        $result = $this->repository->addAllowedUser($email, $role, $tokenHash);

        if (!is_array($result) || empty($result)) {
            return false;
        }

        $userData = [
            'email' => $email,
            'role' => $result['role'],
            'has_token' => $generateToken,
            'created_at' => $result['created_at'],
        ];

        if ($tokenResult !== null) {
            $userData['token'] = $tokenResult['token'];
        }

        return $userData;
    }

    public function regenerateTokenForUser(string $email): array|bool
    {
        if (!$this->getUserByEmail($email)) {
            return false;
        }

        $tokenResult = $this->generateTokenForUser($email);

        $result = $this->repository->updateTokenForUser($email, $tokenResult['token_hash']);

        if ($result) {
            return [
                'email' => $email,
                'token' => $tokenResult['token'],
            ];
        }

        return false;
    }

    public function changeRoleForUser(string $email, string $role): array|bool
    {
        if (!$this->getUserByEmail($email)) {
            return false;
        }

        $result = $this->repository->updateRoleForUser($email, $role);

        if ($result) {
            return [
                'email' => $email,
                'role' => $role,
            ];
        }

        return false;
    }

    public function deleteAllowedUser(string $email): bool
    {
        return $this->repository->deleteAllowedUser($email);
    }

    private function generateTokenForUser(string $email): array
    {
        $token = bin2hex(random_bytes(16));
        $tokenHash = password_hash($token, PASSWORD_BCRYPT);

        return [
            'email' => $email,
            'token' => base64_encode($email . ':' . $token),
            'token_hash' => $tokenHash,
        ];
    }
}
