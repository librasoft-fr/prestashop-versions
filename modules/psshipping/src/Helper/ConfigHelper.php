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

namespace PrestaShop\Module\Psshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit();
}

class ConfigHelper
{
    /** @var string static content URL for the front-end */
    public $cdn_url;
    /** @var string shipping APIs URL */
    public $api_url;
    /** @var string vue app dev mode activation */
    public $vue_app_dev_mode;
    /** @var string vue app dev local */
    public $use_local_vue_app;
    /** @var string mbe tracking url */
    public $mbe_tracking_url;
    /** @var string segment_key */
    public $segment_key;
    /** @var string segment_key */
    public $sentry_dsn;
    /** @var string segment_key */
    public $sentry_env;

    public function __construct(
        string $api_url,
        string $cdn_url,
        string $vue_app_dev_mode,
        string $use_local_vue_app,
        string $mbe_tracking_url,
        string $segment_key,
        string $sentry_dsn,
        string $sentry_env
    ) {
        $this->api_url = $api_url;
        $this->cdn_url = $cdn_url;
        $this->vue_app_dev_mode = $vue_app_dev_mode;
        $this->use_local_vue_app = $use_local_vue_app;
        $this->mbe_tracking_url = $mbe_tracking_url;
        $this->segment_key = $segment_key;
        $this->sentry_dsn = $sentry_dsn;
        $this->sentry_env = $sentry_env;
    }
}
