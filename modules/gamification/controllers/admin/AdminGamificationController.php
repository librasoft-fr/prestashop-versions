<?php

include_once __DIR__ . '/../../classes/Condition.php';

class AdminGamificationController extends ModuleAdminController
{
    /**
     * @var gamification
     */
    public $module;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('Your Merchant Expertise');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gamification_bt.js');
        } else {
            $this->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/gamification.js');
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        unset($this->page_header_toolbar_btn['back']);
    }

    public function ajaxProcessGamificationTasks()
    {
        // Refresh data from API, if needed
        $this->processRefreshData();

        // Recalculate not validated conditions based on time
        $this->processMakeDailyCalculation();

        // Compute advices validtion/unvalidation based on conditions
        $this->processAdviceValidation();

        $return['advices_to_display'] = $this->processGetAdvicesToDisplay();
        //get only one random advice by tab
        if (count($return['advices_to_display']['advices']) > 1) {
            $rand = mt_rand(0, count($return['advices_to_display']['advices']) - 1);
            $return['advices_to_display']['advices'] = [$return['advices_to_display']['advices'][$rand]];
        }

        if (Tab::getIdFromClassName('AdminDashboard') == Tools::getValue('id_tab')) {
            $return['advices_premium_to_display'] = $this->processGetAdvicesToDisplay(true);

            if (count($return['advices_premium_to_display']['advices']) >= 2) {
                $weighted_advices_array = [];
                foreach ($return['advices_premium_to_display']['advices'] as $prem_advice) {
                    $loop_flag = (int) $prem_advice['weight'];
                    if ($loop_flag) {
                        for ($i = 0; $i != $loop_flag; ++$i) {
                            $weighted_advices_array[] = $prem_advice;
                        }
                    } else {
                        $weighted_advices_array[] = $prem_advice;
                    }
                }
                $rand = mt_rand(0, count($weighted_advices_array) - 1);
                do {
                    $rand2 = mt_rand(0, count($weighted_advices_array) - 1);
                } while ($rand == $rand2);

                $return['advices_premium_to_display']['advices'] = [$weighted_advices_array[$rand], $weighted_advices_array[$rand2]];
            } elseif (count($return['advices_premium_to_display']['advices']) > 0) {
                $addons = Advice::getValidatedAddonsOnlyByIdTab((int) Tools::getValue('id_tab'));
                $return['advices_premium_to_display']['advices'][] = array_shift($addons);
            }
        }
        echo json_encode($return);

        exit;
    }

    public function processRefreshData()
    {
        return $this->module->refreshDatas();
    }

    public function processGetAdvicesToDisplay($only_premium = false)
    {
        $return = ['advices' => []];

        $id_tab = (int) Tools::getValue('id_tab');

        if ($only_premium) {
            $advices = Advice::getValidatedPremiumOnlyByIdTab($id_tab);
        } else {
            $advices = Advice::getValidatedByIdTab($id_tab);
        }

        foreach ($advices as $advice) {
            $return['advices'][] = [
                'selector' => $advice['selector'],
                'html' => GamificationTools::parseMetaData($advice['html']),
                'location' => $advice['location'],
                'weight' => (int) $advice['weight'],
            ];
        }

        return $return;
    }

    public function processMakeDailyCalculation()
    {
        $return = true;
        $condition_ids = Condition::getIdsDailyCalculation();
        foreach ($condition_ids as $id) {
            $condition = new Condition((int) $id);
            $return &= $condition->processCalculation();
        }

        return $return;
    }

    public function processAdviceValidation()
    {
        $return = true;
        $advices_to_validate = Advice::getIdsAdviceToValidate();
        $advices_to_unvalidate = Advice::getIdsAdviceToUnvalidate();

        foreach ($advices_to_validate as $id) {
            $advice = new Advice((int) $id);
            $advice->validated = 1;
            $return &= $advice->save();
        }

        foreach ($advices_to_unvalidate as $id) {
            $advice = new Advice((int) $id);
            $advice->validated = 0;
            $return &= $advice->save();
        }

        return $return;
    }

    public function ajaxProcessSavePreactivationRequest()
    {
        $isoUser = Context::getContext()->language->iso_code;
        $isoCountry = Context::getContext()->country->iso_code;
        $employee = new Employee((int) Context::getContext()->cookie->id_employee);
        $firstname = $employee->firstname;
        $lastname = $employee->lastname;
        $email = $employee->email;
        $return = @Tools::file_get_contents('http://api.prestashop.com/partner/premium/set_request.php?iso_country=' . strtoupper($isoCountry) . '&iso_lang=' . strtolower($isoUser) . '&host=' . urlencode($_SERVER['HTTP_HOST']) . '&ps_version=' . _PS_VERSION_ . '&ps_creation=' . _PS_CREATION_DATE_ . '&partner=' . htmlentities(Tools::getValue('module')) . '&shop=' . urlencode(Configuration::get('PS_SHOP_NAME')) . '&email=' . urlencode($email) . '&firstname=' . urlencode($firstname) . '&lastname=' . urlencode($lastname) . '&type=home');
        exit($return);
    }

    public function ajaxProcessCloseAdvice()
    {
        $id_advice = Advice::getIdByIdPs((int) Tools::getValue('id_advice'));
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'advice` SET `hide` =  \'1\' WHERE  `id_advice` = ' . (int) $id_advice . ';');
        exit();
    }
}
