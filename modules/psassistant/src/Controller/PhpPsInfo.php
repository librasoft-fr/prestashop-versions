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
namespace PrestaShop\Module\Assistant\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhpPsInfo
{
    protected $requirements = [
        'directories' => [
            'cache_dir' => 'var/cache',
            'log_dir' => 'var/logs',
            'img_dir' => 'img',
            'mails_dir' => 'mails',
            'module_dir' => 'modules',
            'translations_dir' => 'translations',
            'customizable_products_dir' => 'upload',
            'virtual_products_dir' => 'download',
            'config_sf2_dir' => 'app/config',
            'translations_sf2' => 'app/Resources/translations',
        ],
        'apache_modules' => [
            'mod_rewrite',
            'mod_security',
        ],
    ];
    /**
     * Get versions data
     *
     * @return array
     */
    protected function getVersions()
    {
        $data = [
            'prestashop_version' => _PS_VERSION_,
            'web_server' => $this->getWebServer(),
            'php_type' =>
                strpos(PHP_SAPI, 'cgi') !== false ?
                'CGI with Apache Worker or another webserver' :
                'Apache Module (low performance)',
            'php_version' => PHP_VERSION
        ];
        if (!extension_loaded('mysqli') || !is_callable('mysqli_connect')) {
            $data['mysqli_extension'] = false;
        } else {
            $data['mysqli_extension'] = mysqli_get_client_info();
        }
        $data['internet_connectivity'] = gethostbyname('www.prestashop.com') !== 'www.prestashop.com';
        return $data;
    }

    /**
     * Get php extensions data
     *
     * @return array
     */
    protected function getPhpExtensions()
    {
        $data = [];
        $vars = [
            'BCMath Arbitrary Precision Mathematics' => 'bcmath',
            'Client URL Library (Curl)' => 'curl',
            'Image Processing and GD' => 'gd',
            'Image Processing (ImageMagick)' => 'imagick',
            'Internationalization Functions (Intl)' => 'intl',
            'Memcache' => 'memcache',
            'Memcached' => 'memcached',
            'Multibyte String (Mbstring)' => 'mbstring',
            'OpenSSL' => 'openssl',
            'File Information (Fileinfo)' => 'fileinfo',
            'JavaScript Object Notation (Json)' => 'json',
            'PDO and MySQL Functions' => 'pdo_mysql',
        ];
        foreach ($vars as $var) {
            $value = extension_loaded($var);
            $data[$var] = $value;
        }
        $vars = [
            'PHP-DOM and PHP-XML' => ['dom', 'DomDocument'],
            'Zip' => ['zip', 'ZipArchive'],
        ];
        foreach ($vars as $var) {
            $value = class_exists($var[1]);
            $data[$var[0]] = $value;
        }
        return $data;
    }

    /**
     * Get php config data
     *
     * @return array
     */
    protected function getPhpConfig()
    {
        $data = [];
        $vars = [
            'allow_url_fopen',
            'expose_php',
            'file_uploads',
            'register_argc_argv',
            'short_open_tag',
        ];
        foreach ($vars as $var) {
            $value = (bool) ini_get($var);
            $data[$var] = $value;
        }
        $vars = [
            'max_input_vars',
            'memory_limit',
            'post_max_size',
            'upload_max_filesize',
        ];
        foreach ($vars as $var) {
            $value = ini_get($var);
            $data[$var] = $value;
        }
        $vars = [
            'set_time_limit',
        ];
        foreach ($vars as $var) {
            $value = is_callable($var);
            $data[$var] = $value;
        }
        return $data;
    }

    /**
     * Check if directories are writable
     *
     * @return array
     */
    protected function getDirectories()
    {
        $data = [];
        foreach ($this->requirements['directories'] as $directory) {
            $directoryPath = realpath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . trim($directory, '\\/'));
            $data[$directory] = file_exists($directoryPath) && is_writable($directoryPath);
        }
        return $data;
    }

    protected function getServerModules()
    {
        $data = [];
        if ($this->getWebServer() !== 'Apache' || !function_exists('apache_get_modules')) {
            return $data;
        }
        $modules = apache_get_modules();
        foreach ($this->requirements['apache_modules'] as $var) {
            $data[$var] = in_array($var, $modules);
        }
        return $data;
    }

    /**
     * Transform value to string
     *
     * @param mixed $value Value
     *
     * @return string
     */
    protected function toString($value)
    {
        if ($value === true) {
            return 'Yes';
        } elseif ($value === false) {
            return 'No';
        } elseif ($value === null) {
            return 'N/A';
        }
        return strval($value);
    }

    /**
     * Detect Web server
     *
     * @return string
     */
    protected function getWebServer()
    {
        if (stristr($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            return 'Apache';
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false) {
            return 'Lite Speed';
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'Nginx') !== false) {
            return 'Nginx';
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false) {
            return 'lighttpd';
        } elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false) {
            return 'Microsoft IIS';
        }

        return 'Not detected';
    }

    protected function buildData()
    {
        return json_encode([
            'version' => self::getVersions(),
            'php_config' => self::getPhpConfig(),
            'php_extension' => self::getPhpExtensions(),
            'directory' => self::getDirectories(),
            'server_module' => self::getServerModules()
        ]);
    }
}
