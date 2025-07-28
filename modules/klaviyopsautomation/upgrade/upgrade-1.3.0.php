<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0($module)
{
    $module->registerHook('actionAdminControllerSetMedia');
    $module->registerHook('actionEmailSendBefore');
    $module->registerHook('actionOrderStatusPostUpdate');
    $module->registerHook('displayAdminAfterHeader');

    Configuration::updateValue('KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE', 0);
    Configuration::updateValue('KLAVIYO_REAL_TIME_EVENT_ENABLE', 0);

    return true;
}
