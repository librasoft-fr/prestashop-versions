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

namespace KlaviyoPs\Classes\UtilServices;

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoPsModule;

class EnvService
{
    /**
     * @var KlaviyoPsModule
     */
    protected $module;

    /**
     * @var array|null
     */
    protected $data = null;

    public function __construct(
        KlaviyoPsModule $module
    ) {
        $this->module = $module;
    }

    /**
     * Get value from .env.json and .env.local.json from the module
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->load();

        if (!isset($this->data[$key])) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * @return void
     */
    protected function load()
    {
        if ($this->data !== null) {
            return;
        }

        $this->data = [];

        $path = $this->module->getLocalPath();

        $this->loadFile("{$path}.env.json");
        $this->loadFile("{$path}.env.local.json");
    }

    /**
     * @param string $file
     * @return void
     */
    protected function loadFile($file)
    {
        if (!file_exists($file)) {
            return;
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return;
        }

        $this->data = array_merge($this->data, $data);
    }
}
