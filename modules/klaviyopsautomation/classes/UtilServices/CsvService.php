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

class CsvService
{
    /**
     * Convert data into CSV format. You can configure the serializing with following options :
     * has_headers : True|False to include headers or not. True by default
     * headers : List of names for headers. Array keys by default.
     * separator : Separator char for fields. Coma by default.
     * enclosure : enclosure char for fields. Double quote by default.
     * eol : end of line char. LF by default.
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    public function csvSerialize($data, $options = [])
    {
        $res = '';
        $options = $this->initOption($options);

        if ($options['headers'] === null) {
            if (empty($data)) {
                return $res;
            }

            $options['headers'] = array_keys($data[0]);
        }

        if ($options['has_headers']) {
            $res .= $this->serializeLine($options['headers'], $options);
        }

        foreach ($data as $e) {
            $line = [];

            foreach ($options['headers'] as $key) {
                $line[] = $e[$key];
            }

            $res .= $this->serializeLine($line, $options);
        }

        return $res;
    }

    /**
     * @param array $line
     * @param array $options
     * @return string
     */
    protected function serializeLine($line, $options)
    {
        $enclosure = $options['enclosure'];

        if (!empty($enclosure)) {
            foreach ($line as &$value) {
                $value = str_replace(
                    $enclosure,
                    $enclosure . $enclosure,
                    $value
                );
                $value = "{$enclosure}{$value}{$enclosure}";
            }
            unset($value);
        }

        $res = implode($options['separator'], $line);
        $res .= $options['eol'];

        return $res;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function initOption($options)
    {
        if (!isset($options['has_headers'])) {
            $options['has_headers'] = true;
        }

        if (!isset($options['headers'])) {
            $options['headers'] = null;
        }

        if (!isset($options['separator'])) {
            $options['separator'] = ',';
        }

        if (!isset($options['enclosure'])) {
            $options['enclosure'] = '"';
        }

        if (!isset($options['eol'])) {
            $options['eol'] = "\n";
        }

        return $options;
    }
}
