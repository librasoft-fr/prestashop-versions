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

namespace KlaviyoPs;

if (!defined('_PS_VERSION_')) {
    exit;
}

use ModuleFrontController;
use Tools;

abstract class KlaviyoPsAjaxModuleFrontController extends ModuleFrontController
{
    /** @var array Error information. */
    public $errors;

    /** @var array|null Data returned from the AJAX request. */
    public $returnData;

    /**
     * @see Controller::run()
     * This method gets called by default for FrontControllers.
     */
    public function postProcess()
    {
        if ($this->isValidRequest()) {
            $this->ajaxProcess();
        }
        $this->returnAjaxResponse();
    }

    /** Abstract method to be defined for handling AJAX requests. */
    abstract protected function ajaxProcess();

    /**
     * Build and return AJAX response.
     */
    private function returnAjaxResponse()
    {
        header('Content-Type: application/json');
        return die(json_encode(array(
            'success' => (bool) !$this->errors,
            'data' => $this->returnData,
            'errors' => $this->errors
        )));
    }

    /**
     * Validate incoming AJAX request.
     *
     * @return bool
     */
    protected function isValidRequest()
    {
        Tools::safePostVars();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->errors[] = 'Invalid request method, must be a POST request.';
        }
        if (!$this->ajax) {
            $this->errors[] = 'Invalid request, expecting ajax request.';
        }
        return (bool) !$this->errors;
    }
}
