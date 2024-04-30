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
namespace PrestaShop\Module\Assistant\Api;

use AdminPsAssistantSettingsController;
use Configuration;
use PrestaShop\Module\Assistant\Script\AdminProvider;
use PrestaShop\Module\Assistant\Script\Script;
use PrestaShop\Module\Assistant\Security\Antispam;
use PrestaShop\Module\Assistant\Security\Verify;
use PrestaShop\PrestaShop\Core\Foundation\IoC\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Dispatch
{
    /**
     * POST data
     *
     * @var mixed
     */
    private $data;
    /**
     * decoded json
     *
     * @var array
     */
    private $decoded_payload;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function dispatchAction()
    {
        $this->data = json_decode($this->data, true);
        //verify all informations are send
        if(!isset($this->data['payload']) || !isset($this->data['signature'])) {
            var_dump($this->data);
            SendError::sendError('Incomplete request');
        }
        $this->decoded_payload = json_decode($this->data['payload'], true);
        //get public key from API
        $public_key = MakeCurl::getPublicKey($this->decoded_payload['shop_uuid']);
        //verify payload
        if(!Verify::verifyPayload($this->data['payload'], $this->data['signature'], $public_key)) {
            SendError::sendError('Payload not valid');
        }
        //verify antispam
        if(!Antispam::verifySpam()) {
            SendError::sendError('Previous action too close');
        }
        //verify POST informations
        if(!isset($this->decoded_payload['action'])) {
            SendError::sendError('Action not set in payload');
        }
        //dispatch between admin and script actions
        switch ($this->decoded_payload['action']) {
            case 'delete_files':
                $this->deleteScriptFile();
            case 'delete_bo':
                $this->removeBoAccess();
            case 'get_adminlink':
                $this->dispatchAdmin($this->data['payload'], $this->data['signature'], $public_key);
                break;
            default:
                $this->dispatchScript($this->data['payload'], $this->data['signature'], $public_key);
        }
    }

    private function dispatchScript($payload, $signature, $public_key)
    {
        try {
            //initiate script file
            $this->verifyScriptFile('Script', $this->decoded_payload['shop_uuid']);
            //instance script class
            $script_class = Script::getInstance($payload, $signature, $public_key);
            switch ($this->decoded_payload['action']) {
                case 'retrieve_infos':
                    $this->sendResponse($script_class->retrieveShopInfos());
                    break;
                case 'clear_cache':
                    $this->sendResponse($script_class->clearCache());
                    break;
                case 'rename_module':
                    if(!isset($this->decoded_payload['module_name'])) {
                        SendError::sendError('Incomplete payload');
                    }
                    $this->sendResponse($script_class->renameModule($this->decoded_payload['module_name']));
                    break;
                case 'restore_module':
                    if(!isset($this->decoded_payload['module_name'])) {
                        SendError::sendError('Incomplete payload');
                    }
                    $this->sendResponse($script_class->restoreModule($this->decoded_payload['module_name']));
                    break;
                case 'rename_override':
                    if(!isset($this->decoded_payload['override_path'])) {
                        SendError::sendError('Incomplete payload');
                    }
                    $this->sendResponse($script_class->renameOverride($this->decoded_payload['override_path']));
                    break;
                case 'restore_override':
                    if(!isset($this->decoded_payload['override_path'])) {
                        SendError::sendError('Incomplete payload');
                    }
                    $this->sendResponse($script_class->restoreOverride($this->decoded_payload['override_path']));
                    break;
                case 'update_debug':
                    $this->sendResponse($script_class->updateConfig('_PS_MODE_DEV_'));
                    break;
                case 'update_profile':
                    $this->sendResponse($script_class->updateConfig('_PS_DEBUG_PROFILING_'));
                    break;
                case 'get_log':
                    if(!isset($this->decoded_payload['mode'])) {
                        SendError::sendError('Incomplete payload');
                    }
                    $this->sendResponse($script_class->getLogFile($this->decoded_payload['mode']));
                    break;
                case 'get_htaccess':
                    $this->sendResponse($script_class->getHtaccessFile());
                    break;
                case 'get_robot':
                    $this->sendResponse($script_class->getRobotFile());
                    break;
                case 'get_phppsinfo':
                    $this->sendResponse($script_class->getPhppsinfo());
                    break;
                default :
                    SendError::sendError('Action not defined');
            }
        }
        catch (Exception $e) {
            sendError('Cannot instance script class');
        }
    }

    private function dispatchAdmin($payload, $signature, $public_key)
    {
        try {
            //initiate admin script file
            $this->verifyScriptFile('AdminProvider', $this->decoded_payload['shop_uuid']);
            //instance script class
            $admin_class = AdminProvider::getInstance($payload, $signature, $public_key);
            $this->sendResponse($admin_class->getAdminLink());
        }
        catch (Exception $e) {
            SendError::sendError('Cannot instance admin class');
        }
    }

    /**
     * Initiate script files
     * @param string $script_host
     * @param string $file
     * @param string $uuid
     * @return void
     */
    private function verifyScriptFile($file, $uuid)
    {
        if (!file_exists(dirname(__DIR__) . '/Script/' . $file . '.php') && $file_content = $this->retrieveScriptClass($file, $uuid)) {
            if(file_put_contents(dirname(__DIR__) . '/Script/' . $file . '.php', $file_content)
                && rename(dirname(__DIR__) . '/Template/' . $file . '.php', dirname(__DIR__) . '/Template/' . $file . '.lock')
            ) {
                return;
            }
            SendError::sendError('Cannot initiate Admin class');
        }
    }

    private function deleteScriptFile()
    {
        $script_path = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Script') . DIRECTORY_SEPARATOR;
        $template_path = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Template') . DIRECTORY_SEPARATOR;
        $script_files = ['Script.', 'AdminProvider.'];
        foreach ($script_files as $file) {
            if(file_exists($template_path . $file . 'lock')) {
                rename($template_path . $file . 'lock', $template_path . $file . 'php');
            }
            if(file_exists($script_path . $file . 'php')) {
                unlink($script_path . $file . 'php');
            }
        }
        $this->sendResponse(['status' => true]);
    }

    private function removeBoAccess()
    {
        Configuration::updateValue(AdminPsAssistantSettingsController::PSASSISTANTISBOACCESSIBLE, false);
        $this->sendResponse(['status' => true]);

    }

    private function retrieveScriptClass($mode, $uuid)
    {
        if($data = MakeCurl::makeCurl('get-file-script', ['file' => $mode, 'shop_uuid' => $uuid])) {
            return base64_decode($data['file_content']);
        }
        SendError::sendError('Cannot retrieve script class file');
        return false;
    }

    private function sendResponse(array $response)
    {
        echo json_encode($response);
        die;
    }
}