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

function upgrade_module_1_12_2($module)
{
    $envService = $module->getService('klaviyops.util_services.env');

    if (!$envService->get('IS_EDITION_MODE')) {
        return true;
    }

    return $module->installPsAccounts();
}
