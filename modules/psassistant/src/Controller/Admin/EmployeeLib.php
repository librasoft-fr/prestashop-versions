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
namespace PrestaShop\Module\Assistant\Controller\Admin;

use Configuration;
use Employee;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;

if (!defined('_PS_VERSION_')) {
    exit;
}

class EmployeeLib
{
    const EMPLOYEE_INFOS = [
        'email' => 'ps-assistant-',
        'domain' => '@prestashop.com',
        'firstname' => 'Prestashop',
        'lastname' => 'Assistant',
    ];
    public static function getEmployee($active)
    {
        $sql = new DbQuery();
        $sql->select('e.id_employee')
            ->from('employee', 'e')
            ->where(sprintf('e.email = "%s"', self::EMPLOYEE_INFOS['email'] . self::getUuid() . self::EMPLOYEE_INFOS['domain']));
        if($active){
            $sql->where('e.active = 1');
        }
        $id_employee = Db::getInstance()->getValue($sql);
        if (empty($id_employee)) {
            return null;
        }
        return new Employee((int) $id_employee);
    }

    public static function addApiEmployee($id_lang)
    {
        $employee = self::getEmployee(false);
        if ($employee && !$employee->active) {
            $employee->active = 1;
        }
        else {
            $employee = new Employee();
            $employee->firstname = self::EMPLOYEE_INFOS['firstname'];
            $employee->lastname = self::EMPLOYEE_INFOS['lastname'];
            $employee->email = self::EMPLOYEE_INFOS['email'] . self::getUuid() . self::EMPLOYEE_INFOS['domain'];
            $employee->id_lang = $id_lang;
            $employee->id_profile = _PS_ADMIN_PROFILE_;
            $employee->active = true;
            $hash = new Hashing();
            $employee->passwd = $hash->hash(uniqid('', true));
        }
        if (!$employee->save()) {
            return false;
        }
        return true;
    }

    private static function getUuid()
    {
        $config_name = 'PSASSISTANT_EMPLOYEE_UUID';
        if($return = Configuration::get($config_name)) {
            return $return;
        }
        $new_uuid = self::generateUUID();
        Configuration::updateValue($config_name, $new_uuid);
        return $new_uuid;
    }

    private static function generateUUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function disableApiEmployee()
    {
        $employee = self::getEmployee(true);
        if ($employee) {
            $employee->active = 0;
            return $employee->save();
        }
        return false;
    }
}