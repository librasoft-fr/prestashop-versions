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

declare(strict_types=1);

namespace PrestaShop\Module\Psshipping\Handler;

use Raven_Client;

if (!defined('_PS_VERSION_')) {
    exit();
}

class ModuleFilteredRavenClient extends Raven_Client
{
    /**
     * @var string[]|null
     */
    protected $excluded_domains;

    /**
     * @param array<string, object>|null $data
     * @param string[]|null $stack
     * @param mixed[]|null $vars
     *
     * @return self|null
     */
    public function capture($data, $stack = null, $vars = null)
    {
        if (!isset($data['exception']['values'][0]['stacktrace']['frames'])) {
            return null;
        }

        if ($this->isErrorFilteredByContext()) {
            return null;
        }

        $allowCapture = false;
        foreach ($data['exception']['values'] as $errorValues) {
            $allowCapture = $allowCapture || $this->isErrorInApp($errorValues);
        }

        if (!$allowCapture) {
            return null;
        }

        return parent::capture($data, $stack, $vars);
    }

    /**
     * @param array<int, string> $domains
     *
     * @return self
     */
    public function setExcludedDomains(array $domains)
    {
        $this->excluded_domains = $domains;

        return $this;
    }

    /**
     * @param array<string, iterable<string>> $data
     *
     * @return bool
     */
    private function isErrorInApp(array $data)
    {
        $atLeastOneFileIsInApp = false;
        $stacktrace = $data['stacktrace'];

        if (!isset($stacktrace['frames'])) {
            return false;
        }

        foreach ($stacktrace['frames'] as $frame) {
            $atLeastOneFileIsInApp = $atLeastOneFileIsInApp || ((isset($frame['in_app']) && $frame['in_app']));
        }

        return $atLeastOneFileIsInApp;
    }

    /**
     * Check the conditions in which the error is thrown, so we can apply filters
     *
     * @return bool
     */
    private function isErrorFilteredByContext()
    {
        if ($this->excluded_domains && !empty($_SERVER['REMOTE_ADDR'])) {
            foreach ($this->excluded_domains as $domain) {
                if (strpos($_SERVER['REMOTE_ADDR'], $domain) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
