<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once __DIR__ . '/classes/Advice.php';
include_once __DIR__ . '/classes/Condition.php';
include_once __DIR__ . '/classes/GamificationTools.php';

class gamification extends Module
{
    // We recommend to not set it to true in production environment.
    const TEST_MODE = false;

    private $url_data = 'https://gamification.prestashop.com/json/';

    private $cache_data;

    public function __construct()
    {
        $this->name = 'gamification';
        $this->tab = 'administration';
        $this->version = '3.0.4';
        $this->author = 'PrestaShop';
        $this->module_key = 'c1187d1672d2a2d33fbd7d5c29f0d42e';
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
        ];

        parent::__construct();

        $this->displayName = $this->l('Merchant Expertise');
        $this->description = $this->l('Become an e-commerce expert within the blink of an eye!');

        $this->cache_data = __DIR__ . '/data/';
        // @phpstan-ignore-next-line
        if (self::TEST_MODE === true) {
            $this->url_data .= 'test/';
        }
    }

    public function install()
    {
        if (Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE name =\'' . pSQL($this->name) . '\'')) {
            return true;
        }

        Tools::deleteDirectory($this->cache_data, false);

        return
            $this->installDb()
            && parent::install()
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayBackOfficeHeader')
       ;
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !$this->uninstallDb()) {
            return false;
        }

        return true;
    }

    public function installDb()
    {
        $return = true;
        $sql = include __DIR__ . '/sql_install.php';
        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }

        return $return;
    }

    public function uninstallDb()
    {
        $sql = include __DIR__ . '/sql_install.php';
        foreach ($sql as $name => $v) {
            Db::getInstance()->execute('DROP TABLE `' . $name . '`');
        }

        return true;
    }

    public function enable($force_all = false)
    {
        $enableResult = parent::enable($force_all) && Tab::enablingForModule($this->name);

        if (php_sapi_name() !== 'cli') {
            // If the module is installed/enabled tthrough CLI, we ignore the data refreshing
            // because we cannot guess the shop context
            $enableResult &= $this->refreshDatas();
        }

        return $enableResult;
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all) && Tab::disablingForModule($this->name);
    }

    public function __call($name, $arguments)
    {
        if (!empty(self::$_batch_mode)) {
            self::$_defered_func_call[__CLASS__ . '::__call_' . $name] = [[$this, '__call'], [$name, $arguments]];
        } else {
            if (!Validate::isHookName($name)) {
                return false;
            }

            $name = str_replace('hook', '', $name);

            if ($retro_name = Db::getInstance()->getValue('SELECT `name` FROM `' . _DB_PREFIX_ . 'hook_alias` WHERE `alias` = \'' . pSQL($name) . '\'')) {
                $name = $retro_name;
            }

            $condition_ids = Condition::getIdsByHookCalculation($name);
            foreach ($condition_ids as $id) {
                $cond = new Condition((int) $id);
                $cond->processCalculation();
            }
        }
    }

    public function isUpdating()
    {
        $db_version = Db::getInstance()->getValue('SELECT `version` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = \'' . pSQL($this->name) . '\'');

        return version_compare($this->version, $db_version, '>');
    }

    public function hookActionAdminControllerSetMedia()
    {
        if ($this->isUpdating() || !Module::isEnabled($this->name)) {
            return;
        }
        if (method_exists($this->context->controller, 'addJquery')) {
            $this->context->controller->addJs($this->_path . 'views/js/gamification_bt.js');

            $this->context->controller->addJqueryPlugin('fancybox');
        }
    }

    /**
     * Calls the server.
     *
     * @return bool|string
     *
     * @throws PrestaShopException
     */
    public function hookDisplayBackOfficeHeader()
    {
        if ($this->isUpdating() || !Module::isEnabled($this->name)) {
            return false;
        }

        return '<script>
            var admin_gamification_ajax_url = ' . (string) json_encode(
                $this->context->link->getAdminLink('AdminGamification')
            ) . ';
            var current_id_tab = ' . (int) $this->context->controller->id . ';
        </script>';
    }

    public function refreshDatas($iso_lang = null)
    {
        if (null === $iso_lang) {
            $iso_lang = $this->context->language->iso_code;
        }

        $default_iso_lang = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));
        $id_lang = Language::getIdByIso($iso_lang);

        $iso_country = $this->context->country->iso_code;
        $iso_currency = $this->context->currency->iso_code;

        if ($iso_lang != $default_iso_lang) {
            $this->refreshDatas($default_iso_lang);
        }

        $cache_file = $this->cache_data . 'data_' . strtoupper($iso_lang) . '_' . strtoupper($iso_currency) . '_' . strtoupper($iso_country) . '.json';

        if (!$this->isFresh($cache_file, 86400)) {
            if ($this->getData($iso_lang)) {
                $data = json_decode(Tools::file_get_contents($cache_file));
                if (json_last_error() !== JSON_ERROR_NONE || !isset($data->signature)) {
                    return false;
                }

                $this->processCleanAdvices();

                $public_key = file_get_contents(__DIR__ . '/prestashop.pub');

                if (isset($data->conditions)) {
                    $signature = isset($data->signature) ? base64_decode($data->signature) : '';
                    if (
                        function_exists('openssl_verify')
                        && self::TEST_MODE === false
                        && isset($data->advices_lang)
                        && !openssl_verify(json_encode([$data->conditions, $data->advices_lang]), $signature, $public_key)
                        ) {
                        return false;
                    }
                    $this->processImportConditions($data->conditions, $id_lang);
                }

                if (isset($data->advices) && isset($data->advices_lang)) {
                    $this->processImportAdvices($data->advices, $data->advices_lang, $id_lang);
                }

                if (isset($data->advices_lang_16)) {
                    $signature16 = isset($data->signature_16) ? base64_decode($data->signature_16) : '';
                    $sslCheck = openssl_verify(json_encode([$data->advices_lang_16]), $signature16, $public_key);
                    if (
                        function_exists('openssl_verify')
                        && self::TEST_MODE === false
                        && !$sslCheck
                    ) {
                        return false;
                    }

                    if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true && isset($data->advices_16)) {
                        $this->processImportAdvices($data->advices_16, $data->advices_lang_16, $id_lang);
                    }
                }
            }
        }

        return true;
    }

    public function getData($iso_lang = null)
    {
        if (null === $iso_lang) {
            $iso_lang = $this->context->language->iso_code;
        }
        $iso_country = $this->context->country->iso_code;
        $iso_currency = $this->context->currency->iso_code;
        $file_name = 'data_' . strtoupper($iso_lang) . '_' . strtoupper($iso_currency) . '_' . strtoupper($iso_country) . '.json';
        $versioning = '?v=' . $this->version . '&ps_version=' . _PS_VERSION_;
        $data = GamificationTools::retrieveJsonApiFile($this->url_data . $file_name . $versioning);

        return (bool) file_put_contents($this->cache_data . 'data_' . strtoupper($iso_lang) . '_' . strtoupper($iso_currency) . '_' . strtoupper($iso_country) . '.json', $data, FILE_USE_INCLUDE_PATH);
    }

    public function processCleanAdvices()
    {
        $current_advices = [];
        $result = Db::getInstance()->ExecuteS('SELECT `id_advice`, `id_ps_advice` FROM `' . _DB_PREFIX_ . 'advice`');
        foreach ($result as $row) {
            $current_advices[(int) $row['id_ps_advice']] = (int) $row['id_advice'];
        }

        // Delete advices that are not in the file anymore
        foreach ($current_advices as $id_advice) {
            // Check that the advice is used in this language
            $html = Db::getInstance()->getValue('SELECT `html` FROM `' . _DB_PREFIX_ . 'advice_lang` WHERE id_advice = ' . (int) $id_advice . ' AND id_lang = ' . (int) $this->context->language->id);
            if (!$html) {
                continue;
            }
            $adv = new Advice($id_advice);
            $adv->delete();
        }
    }

    public function processImportConditions($conditions, $id_lang)
    {
        $current_conditions = [];
        $result = Db::getInstance()->ExecuteS('SELECT `id_ps_condition` FROM `' . _DB_PREFIX_ . 'condition`');

        foreach ($result as $row) {
            $current_conditions[] = (int) $row['id_ps_condition'];
        }

        if (is_array($conditions) || is_object($conditions)) {
            foreach ($conditions as $condition) {
                if (isset($condition->id)) {
                    unset($condition->id);
                }
                try {
                    $cond = new Condition();
                    if (in_array($condition->id_ps_condition, $current_conditions)) {
                        $cond = new Condition(Condition::getIdByIdPs($condition->id_ps_condition));
                        unset($current_conditions[(int) array_search($condition->id_ps_condition, $current_conditions)]);
                    }

                    $cond->hydrate((array) $condition, (int) $id_lang);

                    $cond->date_upd = date('Y-m-d H:i:s', strtotime('-' . (int) $cond->calculation_detail . 'DAY'));
                    $cond->date_add = date('Y-m-d H:i:s');
                    $condition->calculation_detail = trim($condition->calculation_detail);
                    $cond->save(false, false);

                    if ($condition->calculation_type == 'hook' && !$this->isRegisteredInHook($condition->calculation_detail) && Validate::isHookName($condition->calculation_detail)) {
                        $this->registerHook($condition->calculation_detail);
                    }
                    unset($cond);
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        // Delete conditions that are not in the file anymore
        foreach ($current_conditions as $id_ps_condition) {
            $cond = new Condition(Condition::getIdByIdPs((int) $id_ps_condition));
            $cond->delete();
        }
    }

    public function processImportAdvices($advices, $advices_lang, $id_lang)
    {
        $formated_advices_lang = [];
        foreach ($advices_lang as $lang) {
            $formated_advices_lang[$lang->id_ps_advice] = ['html' => [$id_lang => $lang->html]];
        }

        $current_advices = [];
        $result = Db::getInstance()->ExecuteS('SELECT `id_advice`, `id_ps_advice` FROM `' . _DB_PREFIX_ . 'advice`');
        foreach ($result as $row) {
            $current_advices[(int) $row['id_ps_advice']] = (int) $row['id_advice'];
        }

        $cond_ids = $this->getFormatedConditionsIds();
        foreach ($advices as $advice) {
            try {
                //if advice already exist we update language data
                if (isset($current_advices[$advice->id_ps_advice])) {
                    $adv = new Advice($current_advices[$advice->id_ps_advice]);
                    $adv->html[$id_lang] = $formated_advices_lang[$advice->id_ps_advice]['html'][$id_lang];
                    $adv->update();
                    $this->processAdviceAsso($adv->id, $advice->display_conditions, $advice->hide_conditions, $advice->tabs, $cond_ids);
                    unset($current_advices[$advice->id_ps_advice]);
                } else {
                    $advice_data = array_merge((array) $advice, $formated_advices_lang[$advice->id_ps_advice]);
                    $adv = new Advice();
                    $adv->hydrate($advice_data, (int) $id_lang);
                    $adv->id_tab = (int) Tab::getIdFromClassName($advice->tab);

                    $adv->add();

                    $this->processAdviceAsso($adv->id, $advice->display_conditions, $advice->hide_conditions, $advice->tabs, $cond_ids);
                }
                unset($adv);
            } catch (Exception $e) {
                continue;
            }
        }
    }

    public function processAdviceAsso($id_advice, $display_conditions, $hide_conditions, $tabs, $cond_ids)
    {
        Db::getInstance()->delete('condition_advice', 'id_advice=' . (int) $id_advice);
        if (is_array($display_conditions)) {
            foreach ($display_conditions as $cond) {
                Db::getInstance()->insert(
                    'condition_advice',
                    [
                    'id_condition' => (int) $cond_ids[$cond], 'id_advice' => (int) $id_advice, 'display' => 1, ]
                );
            }
        }

        if (is_array($hide_conditions)) {
            foreach ($hide_conditions as $cond) {
                Db::getInstance()->insert(
                    'condition_advice',
                    [
                    'id_condition' => (int) $cond_ids[$cond], 'id_advice' => (int) $id_advice, 'display' => 0, ]
                );
            }
        }

        Db::getInstance()->delete('tab_advice', 'id_advice=' . (int) $id_advice);
        if (isset($tabs) && is_array($tabs) && count($tabs)) {
            foreach ($tabs as $tab) {
                Db::getInstance()->insert(
                    'tab_advice',
                    [
                    'id_tab' => (int) Tab::getIdFromClassName($tab), 'id_advice' => (int) $id_advice, ]
                );
            }
        }
    }

    public function getFormatedConditionsIds()
    {
        $cond_ids = [];
        $result = Db::getInstance()->executeS('SELECT `id_condition`, `id_ps_condition` FROM `' . _DB_PREFIX_ . 'condition`');

        foreach ($result as $res) {
            $cond_ids[$res['id_ps_condition']] = $res['id_condition'];
        }

        return $cond_ids;
    }

    public function isFresh($file, $timeout = 86400000)
    {
        if (!file_exists($file)) {
            return false;
        }

        $lastFileUpdate = filemtime($file) + $timeout;
        $now = time();

        return $now < $lastFileUpdate;
    }
}
