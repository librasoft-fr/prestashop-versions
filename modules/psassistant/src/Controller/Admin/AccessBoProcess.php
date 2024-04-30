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

use Cache;
use Cookie;
use Employee;
use EmployeeSession;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AccessBoProcess
{
    /**
     * Save employee session in cookies
     * @param Employee|null $user
     * @return Cookie|false
     * @throws \PrestaShop\PrestaShop\Core\Exception\CoreException
     */
    public function processLogin($user)
    {
        if(!$user){
            return false;
        }
        $cookie = new Cookie('psAdmin');
        $cookie->id_employee = (int) $user->id;
        $cookie->email = $user->email;
        $cookie->profile = $user->id_profile;
        $cookie->passwd = $user->passwd;
        $cookie->remote_addr = ip2long(Tools::getRemoteAddr());
        if(version_compare(_PS_VERSION_, '1.7.6', '<')) {
            return $this->processLoginLegacy($user, $cookie);
        }
        $cookie->registerSession(new EmployeeSession());
        if (!Tools::getValue('stay_logged_in')) {
            $cookie->last_activity = time();
        }
        $cookie->write();
        Cache::clean('isLoggedBack' . $user->id);
        return $cookie;
    }

    private function processLoginLegacy($user, $cookie)
    {
        $this->context = new \StdClass;
        $this->context->employee = new \StdClass;

        $this->context->employee = new Employee();
        $is_employee_loaded = $this->context->employee->getByEmail($user->email, $user->passwd);
        $employee_associated_shop = $this->context->employee->getAssociatedShops();
        if ($is_employee_loaded && (!empty($employee_associated_shop) && $this->context->employee->isSuperAdmin())) {
            $this->context->employee->remote_addr = (int)ip2long(Tools::getRemoteAddr());
            if (!Tools::getValue('stay_logged_in')) {
                $cookie->last_activity = time();
            }
            $cookie->write();
        }

        return $cookie;
    }
}