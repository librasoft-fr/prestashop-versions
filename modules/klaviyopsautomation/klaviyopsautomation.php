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

/**
 * Necessary to access namespaced module classes in the main module file.
 */
require_once(__DIR__ . '/vendor/autoload.php');

class KlaviyoPsAutomation extends KlaviyoPsModule
{
    public function __construct()
    {
        $this->module_key = '11f15f4ff8f9102505780531ab77ee27';
        $this->name = 'klaviyopsautomation';
        $this->author = 'PrestaShop Partners';
        $this->version = '1.11.1';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Automation with Klaviyo');
        $this->description = $this->l('Klaviyo module to integrate PrestaShop with Klaviyo.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function registerControllersAndHooks()
    {
        return
            parent::registerControllersAndHooks() &&
            $this->registerHook('displayAdminAfterHeader')
        ;
    }

    public function hookDisplayAdminAfterHeader($params)
    {
        $controller = $this->context->controller;

        if (
            $controller === null ||
            $this->getConfigurationValueOrNull('KLAVIYO_PUBLIC_API')
        ) {
            return;
        }

        $infoText = null;
        $headingText = null;
        $subHeadingText = null;
        $klaviyoLink = 'https://www.klaviyo.com/uk/grow/integrations/prestashop-automation';
        $configLink = $this->context->link->getAdminLink('AdminKlaviyoPsConfig');

        if ($controller->controller_name === 'AdminMailTheme') {
            $headingText = $this->l('Drive more revenue — automatically');
            $subHeadingText = $this->l('Intelligent email marketing automations help you scale growth');
            $infoText = $this->l('Elevate your email marketing with Klaviyo. Learn how to create personalised and automated email campaigns to generate more revenue from your customers.');
        } elseif ($controller->controller_name === 'AdminStats') {
            $module = Tools::getValue('module', '');

            if ($module === 'statsnewsletter') {
                $headingText = $this->l('Personalise your marketing');
                $subHeadingText = $this->l('Prestashop users drive up to 70% more revenue with Klaviyo');
                $infoText = $this->l('Drive more revenue from your customers by leveraging Klaviyo to create personalised marketing messages. Prestashop customers who use Klaviyo have increased revenue up to 70% year over year.');
            }
        } elseif ($controller->controller_name === 'AdminCarts') {
            $headingText = $this->l('Turn lost sales into revenue');
            $subHeadingText = $this->l('Use intelligent marketing automation to bring people back to your website');
            $infoText = $this->l('Nearly 70% of online shoppers leave items in their cart without making a purchase. Turn lost sales into revenue with intelligent automation, reminding visitors of the products in their cart and encouraging them to buy with Klaviyo!');
        }

        if ($infoText === null) {
            return;
        }

        $this->context->smarty->assign([
            'klaviyo_edition_logo' => $this->getDistPathUri('img/klaviyo-edition-logo.svg'),
            'klaviyo_link' => $klaviyoLink,
            'klaviyo_module_link' => $configLink,
            'heading_text' => $headingText,
            'sub_heading_text' => $subHeadingText,
            'info_text' => $infoText,
        ]);
        return $this->display(__FILE__, 'admin_prompt.tpl');
    }
}
