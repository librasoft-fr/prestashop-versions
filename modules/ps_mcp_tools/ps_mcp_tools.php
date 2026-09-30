<?php
/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Tools module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Tools module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

// DONT'T USE "use" STATEMENT HERE, IT'S NOT COMPAT WITH 1.6
// PREFER "use" IN CLASS DIRECTLY
class Ps_mcp_tools extends Module
{
    /**
     * @var string
     */
    public $version;

    /**
     * @var int Defines the multistore compatibility level of the module
     */
    public $multistoreCompatibility;

    /**
     * @var string contact email of the maintainers (please consider using github issues)
     */
    public $emailSupport;

    /**
     * @var string available terms of services
     */
    public $termsOfServiceUrl;

    /**
     * __construct.
     */
    public function __construct()
    {
        // @see https://devdocs.prestashop-project.org/8/modules/concepts/module-class/
        $this->name = 'ps_mcp_tools';
        $this->tab = 'administration';
        $this->author = 'PrestaShop SA';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->version = '1.0.2';
        $this->module_key = '986b05ecb504f95774d3df413cb6542a';

        parent::__construct();

        $this->emailSupport = 'cloudsync-support@prestashop.com';
        $this->termsOfServiceUrl = 'https://www.prestashop.com/en/prestashop-account-privacy';
        $this->displayName = $this->l('PrestaShop MCP Tools');
        $this->description = $this->l('Add some tools for managing your PrestaShop store, through ps_mcp module.');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '8.0', 'max' => _PS_VERSION_];
    }

    public function getMultistoreCompatibility(): int
    {
        return (int) true;
    }

    public function isMcpCompliant(): bool
    {
        return true;
    }
}
