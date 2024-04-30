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
use PrestaShop\Module\Assistant\Api\MakeCurl;
use PrestaShop\Module\Assistant\Controller\Admin\AccessBoProcess;
use PrestaShop\Module\Assistant\Controller\Admin\EmployeeLib;
use PrestaShop\Module\Assistant\Security\Verify;

if(version_compare(_PS_VERSION_, '1.7.5', '<')) {
    require_once _PS_MODULE_DIR_ . 'psassistant/src/Api/MakeCurl.php';
    require_once _PS_MODULE_DIR_ . 'psassistant/src/Controller/Admin/AccessBoProcess.php';
    require_once _PS_MODULE_DIR_ . 'psassistant/src/Controller/Admin/EmployeeLib.php';
    require_once _PS_MODULE_DIR_ . 'psassistant/src/Security/Verify.php';
}

if (!defined('_PS_VERSION_')) {
    exit;
}

class PsAssistant extends Module
{
    /**
     * Default hook to install
     *
     * @var array
     */
    const HOOK_LIST = [
        'actionDispatcher',
        'actionDispatcherBefore'
    ];
    /**
     * Names of ModuleAdminController used
     *
     * @var array
     */
    const ADMIN_CONTROLLERS = [
        'AdminPsAssistantConnect',
        'AdminPsAssistantSettings',
    ];

    /**
     * Default entrypoint for API employee
     *
     * @var string
     */
    const ADMIN_LOGIN_CONTROLLER = 'AdminPsAssistantConnect';

    public function __construct()
    {
        $this->name = 'psassistant';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'PrestaShop';
        $this->module_key = '2fad63a468a76d9b448ab5507689eaf6';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('Assistance By PrestaShop', [], 'Modules.PsAssistant.Admin');
        $this->description = $this->trans('Allow Prestashop support to access some parts of your store.', [], 'Modules.PsAssistant.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.0.6', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install()
            && $this->installTab()
            && $this->registerHook(self::HOOK_LIST)
        ;
    }

    public function installTab()
    {
        $install = true;
        foreach (self::ADMIN_CONTROLLERS as $controller_name) {
            if (Tab::getIdFromClassName($controller_name)) {
                continue;
            }
            $tab = new Tab();
            $tab->name = array_fill_keys(
                Language::getIDs(false),
                $this->displayName
            );
            $tab->class_name = $controller_name;
            //display module configuration tab in configure tab menu
            if($controller_name === 'AdminPsAssistantSettings') {
                $tab->id_parent = Tab::getIdFromClassName('CONFIGURE');
            } else {
                $tab->id_parent = -1;
            }
            $tab->module = $this->name;
            $install = $install && $tab->add();
        }
        return $install;
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallTab()
        ;
    }

    public function uninstallTab()
    {
        $uninstall = true;
        foreach (self::ADMIN_CONTROLLERS as $controller_name) {
            $tab = new Tab((int) Tab::getIdFromClassName($controller_name));
            $uninstall = $uninstall && $tab->delete();
        }
        return $uninstall;
    }

    public function getContent()
    {
        //redirect to admin controller
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPsAssistantSettings'));
    }

    public function hookActionDispatcherBefore($params)
    {
        //only connect controller is managed
        $controller_name = Tools::getValue('controller');
        if($controller_name !== self::ADMIN_LOGIN_CONTROLLER) {
            return;
        }
        if(!(bool)Configuration::get("PSASSISTANT_ISBOACCESSIBLE")) {
            return;
        }
        //verify payload and signature presence
        $payload = base64_decode(Tools::getValue('payload'));
        $signature = Tools::getValue('signature');
        $decoded_payload = json_decode($payload, true);
        if(!$payload || !$signature || $decoded_payload['action'] !== 'access_bo') {
            Tools::redirect('page_not_found');
        }
        //get public key from API
        $public_key = MakeCurl::getPublicKey($decoded_payload['shop_uuid']);
        //verify signature
        if(!Verify::verifyPayload($payload, $signature, $public_key)) {
            Tools::redirect('page_not_found');
        }
        //if everything good generate employee session
        $access_bo = new AccessBoProcess();
        $user = EmployeeLib::getEmployee(true);
        $cookie = $access_bo->processLogin($user);
        if(!$cookie) {
            return;
        }
        $this->context->employee = $user;
        $this->context->cookie = $cookie;
        Context::getContext()->cookie = $cookie;
    }

    public function hookActionDispatcher($params)
    {
       $this->hookActionDispatcherBefore($params);
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }
}
