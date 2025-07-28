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

namespace PrestaShop\Module\Psshipping\Domain\Segment;

use Context;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsIsNotInstalledException;
use PrestaShop\Module\Psshipping\Domain\Accounts\AccountsService;
use Psshipping;

class Segment
{
    /**
     * @var string
     */
    private $message = '';

    /**
     * @var array<string, mixed>
     */
    private $options = [];

    /**
     * @var Context|null
     */
    private $context;

    /**
     * @var Psshipping
     */
    private $module;

    /**
     * @var array<string, string|boolean|null>
     */
    private $psAccountsContext;

    /**
     * Segment constructor.
     */
    public function __construct(Psshipping $module, bool $onInstall = false)
    {
        $this->context = Context::getContext();

        try {
            $this->psAccountsContext = (new AccountsService())->getAccountsContext($module, $onInstall);
        } catch (AccountsIsNotInstalledException $e) {
            $this->psAccountsContext = [];
        }

        $this->module = $module;
        $this->init();
    }

    /**
     * Init segment client with the api key
     */
    private function init(): void
    {
        \Segment::init($this->module->getSegmentKey());
    }

    /**
     * Track event on segment
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    public function track()
    {
        if (empty($this->message)) {
            throw new \PrestaShopException('Message cannot be empty. Need to set it with setMessage() method.');
        }

        $this->segmentTrack();

        return true;
    }

    private function segmentTrack(): void
    {
        $userAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER) === true ? $_SERVER['HTTP_USER_AGENT'] : '';
        $ip = array_key_exists('REMOTE_ADDR', $_SERVER) === true ? $_SERVER['REMOTE_ADDR'] : '';
        $referer = array_key_exists('HTTP_REFERER', $_SERVER) === true ? $_SERVER['HTTP_REFERER'] : '';
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $isoCode = !empty($this->context->language) ? $this->context->language->iso_code : 'en';

        $segmentTrack = [
            'event' => $this->message,
            'channel' => 'browser',
            'context' => [
                'ip' => $ip,
                'userAgent' => $userAgent,
                'locale' => $isoCode,
                'page' => [
                    'referrer' => $referer,
                    'url' => $url,
                ],
            ],
            'properties' => array_merge([
                'module' => 'psshipping',
            ], $this->options),
        ];

        if (!empty($this->psAccountsContext['user']) && !empty($this->psAccountsContext['user']['uuid'])) {
            $segmentTrack['userId'] = $this->psAccountsContext['user']['uuid'];
        } else {
            if (!empty($this->context->shop)) {
                $segmentTrack['anonymousId'] = hash('sha256', $this->context->shop->domain);
            }
        }

        if (!empty($this->psAccountsContext['currentShop']) && !empty($this->psAccountsContext['currentShop']['uuid'])) {
            $segmentTrack['properties']['shopId'] = $this->psAccountsContext['currentShop']['uuid'];
        }

        \Segment::track($segmentTrack);

        \Segment::flush();
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions($options): void
    {
        $this->options = $options;
    }
}
