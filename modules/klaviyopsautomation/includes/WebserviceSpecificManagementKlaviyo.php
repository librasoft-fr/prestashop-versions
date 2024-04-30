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

use KlaviyoPs\Classes\Webservice\AbstractWebserviceSpecificManagementKlaviyo;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
    class WebserviceSpecificManagementKlaviyo extends AbstractWebserviceSpecificManagementKlaviyo
    {
        public function setObjectOutput(WebserviceOutputBuilder $obj)
        {
            return $this->callbackSetObjectOutput($obj);
        }

        public function setWsObject(WebserviceRequest $obj)
        {
            return $this->callbackSetWsObject($obj);
        }
    }
} else {
    class WebserviceSpecificManagementKlaviyo extends AbstractWebserviceSpecificManagementKlaviyo
    {
        public function setObjectOutput(WebserviceOutputBuilderCore $obj)
        {
            return $this->callbackSetObjectOutput($obj);
        }

        public function setWsObject(WebserviceRequestCore $obj)
        {
            return $this->callbackSetWsObject($obj);
        }
    }
}
