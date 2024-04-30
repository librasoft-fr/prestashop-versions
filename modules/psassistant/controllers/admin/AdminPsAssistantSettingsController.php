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
require_once dirname(dirname(__DIR__)) . '/src/Controller/Admin/EmployeeLib.php';

class AdminPsAssistantSettingsController extends ModuleAdminController
{
    const PSASSISTANTISBOACCESSIBLE = 'PSASSISTANT_ISBOACCESSIBLE';
    const PSASSISTANTBONAME = 'PSASSISTANT_BONAME';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        parent::__construct();
        $this->employeeLib = new PrestaShop\Module\Assistant\Controller\Admin\EmployeeLib();
    }

    public function renderList()
    {
        return $this->renderFormSettings();
    }

    public function renderFormSettings()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form = [
            'tinymce' => true,
            'legend' => [
                'title' => $this->trans('Configuration', [], 'Modules.PsAssistant.Admin'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'html',
                    'name' => 'legal_text',
                    'html_content' => $this->getLegalTemplate(),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Enable Back Office access', [], 'Modules.Psassistant.Admin'),
                    'name' => self::PSASSISTANTISBOACCESSIBLE,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                        ],
                     ],
                ],
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Modules.Psassistant.Admin'),
                'name' => 'submit_settings',
            ],
        ];

        $helper = new HelperForm();
        $helper->token = Tools::getValue('token');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->default_form_language = $default_lang;
        $helper->submit_action = 'submit_settings';
        $helper->fields_value = $this->getFieldsValues();

        return $helper->generateForm([['form' => $fields_form]]);
    }

    public function getFieldsValues()
    {
        return [self::PSASSISTANTISBOACCESSIBLE => Configuration::get(self::PSASSISTANTISBOACCESSIBLE)];
    }

    public function getLegalTemplate()
    {
        $cgu_href = $this->trans('https://prestashop.com/prestashop-account-terms-conditions/', [], 'Modules.Psassistant.Admin');
        $privacy_href = $this->trans('https://prestashop.com/prestashop-account-privacy/', [], 'Modules.Psassistant.Admin');
        if(version_compare(_PS_VERSION_, '1.7.5', '<')) {
            if(class_exists(PrestaShop\PrestaShop\Adapter\SymfonyContainer::class)) {
                return PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('twig')->render(dirname(dirname(__DIR__)).'/views/templates/admin/legal.html.twig', ['cgu_href' => $cgu_href, 'privacy_href' => $privacy_href]);
            } else {
                $this->context->smarty->assign('cgu_href', $cgu_href);
                $this->context->smarty->assign('privacy_href', $privacy_href);

                return $this->context->smarty->fetch(dirname(dirname(__DIR__)).'/views/templates/admin/_legacy/legal.tpl');
            }
        }
        return PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance()->get('twig')->render('@Modules/psassistant/views/templates/admin/legal.html.twig', ['cgu_href' => $cgu_href, 'privacy_href' => $privacy_href]);
    }

    public function postProcess()
    {
        if(Tools::isSubmit('submit_settings')) {
            $is_bo_accessible = Tools::getValue(self::PSASSISTANTISBOACCESSIBLE);
            Configuration::updateValue(self::PSASSISTANTISBOACCESSIBLE, $is_bo_accessible);
            $is_bo_accessible ?
                Configuration::updateValue(self::PSASSISTANTBONAME, basename(_PS_ADMIN_DIR_)) && $this->employeeLib->addApiEmployee($this->context->language->id)
                :
                Configuration::deleteByName(self::PSASSISTANTBONAME) && $this->employeeLib->disableApiEmployee();
            $this->confirmations[] = $this->trans('Configuration updated', [], 'Modules.Psassistant.Admin');
        }
        return parent::postProcess();
    }
}
