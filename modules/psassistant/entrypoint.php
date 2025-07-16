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
use PrestaShop\Module\Assistant\Api\Dispatch;

require_once __DIR__.'/vendor/autoload.php';
require_once dirname(dirname(__DIR__)).'/config/config.inc.php';
header('Content-Type: application/json');
//request should be in POST mode
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    sendError('Wrong method');
}

//module should be active
if(!Module::isEnabled('psassistant')){
    sendError('Module is not enabled');
}

//POST data
$data = file_get_contents('php://input');

$dispatch = new Dispatch($data);
$dispatch->dispatchAction();

function sendError($message)
{
    echo json_encode(['status' => false, 'error_message' => $message]);
    die;
}