<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class statsnewsletter extends ModuleGraph
{
    private $_html = '';
    private $_query = '';
    private $_query2 = '';
    private $_option = '';

    private $table_name;
    private $newsletter_module_name;
    private $newsletter_module_human_readable_name;

    public function __construct()
    {
        $this->name = 'statsnewsletter';
        $this->tab = 'administration';
        $this->version = '2.1.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->table_name = _DB_PREFIX_ . 'emailsubscription';
        $this->newsletter_module_name = 'ps_emailsubscription';
        $this->newsletter_module_human_readable_name = 'Email subscription';

        parent::__construct();

        $this->displayName = $this->trans('Newsletter', [], 'Admin.Global');
        $this->description = $this->trans('Enrich your stats, display a graph showing newsletter registrations.', [], 'Modules.Statsnewsletter.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayAdminStatsModules');
    }

    public function hookDisplayAdminStatsModules($params)
    {
        if (Module::isInstalled($this->newsletter_module_name)) {
            $totals = $this->getTotals();
            if (Tools::getValue('export')) {
                $this->csvExport(['type' => 'line', 'layers' => 3]);
            }
            $this->_html = '
			<div class="panel-heading">
				' . $this->displayName . '
			</div>
			<div class="row row-margin-bottom">
				<div class="col-lg-12">
					<div class="col-lg-8">
						' . $this->engine(['type' => 'line', 'layers' => 3]) . '
					</div>
					<div class="col-lg-4">
						<ul class="list-unstyled">
							<li>' . $this->trans('Customer registrations:', [], 'Modules.Statsnewsletter.Admin') . ' ' . (int) $totals['customers'] . '</li>
							<li>' . $this->trans('Visitor registrations: ', [], 'Modules.Statsnewsletter.Admin') . ' ' . (int) $totals['visitors'] . '</li>
							<li>' . $this->trans('Both:', [], 'Modules.Statsnewsletter.Admin') . ' ' . (int) $totals['both'] . '</li>
						</ul>
						<hr/>
						<a class="btn btn-default export-csv" href="' . Tools::safeOutput($_SERVER['REQUEST_URI'] . '&export=1') . '">
							<i class="icon-cloud-upload"></i> ' . $this->trans('CSV Export', [], 'Modules.Statsnewsletter.Admin') . '
						</a>
					</div>
				</div>
			</div>';
        } else {
            $this->_html = '<p>' . $this->trans('The %s module must be installed.', [$this->newsletter_module_human_readable_name], 'Modules.Statsnewsletter.Admin') . '</p>';
        }

        return $this->_html;
    }

    private function getTotals()
    {
        $sql = 'SELECT COUNT(*) as customers
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE 1
					' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
					AND `newsletter_date_add` BETWEEN ' . ModuleGraph::getDateBetween();
        $result1 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getRow($sql);

        $sql = 'SELECT COUNT(*) as visitors
				FROM ' . $this->table_name . '
				WHERE 1
				   ' . Shop::addSqlRestriction() . '
					AND `newsletter_date_add` BETWEEN ' . ModuleGraph::getDateBetween();
        $result2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getRow($sql);

        return ['customers' => $result1['customers'], 'visitors' => $result2['visitors'], 'both' => $result1['customers'] + $result2['visitors']];
    }

    protected function getData($layers)
    {
        $this->_titles['main'][0] = $this->trans('Newsletter statistics', [], 'Modules.Statsnewsletter.Admin');
        $this->_titles['main'][1] = $this->trans('customers', [], 'Admin.Global');
        $this->_titles['main'][2] = $this->trans('Visitors', [], 'Admin.Shopparameters.Feature');
        $this->_titles['main'][3] = $this->trans('Both', [], 'Admin.Advparameters.Feature');

        $this->_query = 'SELECT newsletter_date_add
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE 1
					' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
					AND `newsletter_date_add` BETWEEN ';

        $this->_query2 = 'SELECT newsletter_date_add
				FROM ' . $this->table_name . '
				WHERE 1
					' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
					AND `newsletter_date_add` BETWEEN ';
        $this->setDateGraph($layers, true);
    }

    protected function setAllTimeValues($layers)
    {
        $result1 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query . $this->getDate());
        $result2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query2 . $this->getDate());
        foreach ($result1 as $row) {
            ++$this->_values[0][(int) substr($row['newsletter_date_add'], 0, 4)];
        }
        if ($result2) {
            foreach ($result2 as $row) {
                ++$this->_values[1][(int) substr($row['newsletter_date_add'], 0, 4)];
            }
        }
        foreach ($this->_values[2] as $key => $zerofill) {
            $this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
        }
    }

    protected function setYearValues($layers)
    {
        $result1 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query . $this->getDate());
        $result2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query2 . $this->getDate());
        foreach ($result1 as $row) {
            ++$this->_values[0][(int) substr($row['newsletter_date_add'], 5, 2)];
        }
        if ($result2) {
            foreach ($result2 as $row) {
                ++$this->_values[1][(int) substr($row['newsletter_date_add'], 5, 2)];
            }
        }
        foreach ($this->_values[2] as $key => $zerofill) {
            $this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
        }
    }

    protected function setMonthValues($layers)
    {
        $result1 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query . $this->getDate());
        $result2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query2 . $this->getDate());
        foreach ($result1 as $row) {
            ++$this->_values[0][(int) substr($row['newsletter_date_add'], 8, 2)];
        }
        if ($result2) {
            foreach ($result2 as $row) {
                ++$this->_values[1][(int) substr($row['newsletter_date_add'], 8, 2)];
            }
        }
        foreach ($this->_values[2] as $key => $zerofill) {
            $this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
        }
    }

    protected function setDayValues($layers)
    {
        $result1 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query . $this->getDate());
        $result2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($this->_query2 . $this->getDate());
        foreach ($result1 as $row) {
            ++$this->_values[0][(int) substr($row['newsletter_date_add'], 11, 2)];
        }
        if ($result2) {
            foreach ($result2 as $row) {
                ++$this->_values[1][(int) substr($row['newsletter_date_add'], 11, 2)];
            }
        }
        foreach ($this->_values[2] as $key => $zerofill) {
            $this->_values[2][$key] = $this->_values[0][$key] + $this->_values[1][$key];
        }
    }
}
