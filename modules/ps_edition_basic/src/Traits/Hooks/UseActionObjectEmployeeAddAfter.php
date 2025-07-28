<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Traits\Hooks;

trait UseActionObjectEmployeeAddAfter
{
    public function hookActionObjectEmployeeAddAfter(array &$params): bool
    {
        try {
            $employee = $params['object'];
            $id_profile = $employee->id_profile;
            $slug = 'ROLE_MOD_MODULE_' . strtoupper($this->name) . '_READ';
            $sql = 'SELECT id_authorization_role FROM `' . _DB_PREFIX_ . 'authorization_role` WHERE slug = "' . $slug . '";';
            $responseAuthorizationRole = \Db::getInstance()->getRow($sql);
            $idAuthorizationRole = $responseAuthorizationRole['id_authorization_role'];
            // remove entry before
            \Db::getInstance()->execute('
                DELETE FROM `' . _DB_PREFIX_ . 'module_access`
                WHERE `id_profile` = ' . (int) $id_profile . ' 
                AND `id_authorization_role` = ' . (int) $idAuthorizationRole . ';
            ');
            \Db::getInstance()->execute('
                INSERT INTO `' . _DB_PREFIX_ . 'module_access` (`id_profile`, `id_authorization_role`)
                VALUES (' . (int) $id_profile . ', ' . (int) $idAuthorizationRole . ');
            ');

            return true;
        } catch (\Exception $e) {
            // Gérer l'erreur, par exemple : loguer l'erreur ou envoyer un e-mail à l'administrateur
            return false;
        }
    }
}
