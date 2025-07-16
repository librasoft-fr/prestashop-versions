(()=>{"use strict";var n={428:n=>{n.exports=window.jQuery}},t={};var e=function e(o){var s=t[o];if(void 0!==s)return s.exports;var d=t[o]={exports:{}};return n[o](d,d.exports,e),d.exports}(428);
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class o{constructor(){const n=new window.prestashop.component.Grid("sql_request");n.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),n.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),n.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),n.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),e(document).on("change",".js-db-tables-select",(()=>this.reloadDbTableColumns())),e(document).on("click",".js-add-db-table-to-query-btn",(n=>this.addDbTableToQuery(n))),e(document).on("click",".js-add-db-table-column-to-query-btn",(n=>{this.addDbTableColumnToQuery(n)}))}reloadDbTableColumns(){const n=e(".js-db-tables-select").find("option:selected"),t=e(".js-table-columns");e.ajax(n.data("table-columns-url")).then((n=>{e(".js-table-alert").addClass("d-none");const{columns:o}=n;t.removeClass("d-none"),t.find("tbody").empty(),o.forEach((n=>{const o=e("<tr>").append(e("<td>").html(n.name)).append(e("<td>").html(n.type)).append(e("<td>").addClass("text-right").append(e("<button>").addClass("btn btn-sm btn-outline-secondary js-add-db-table-column-to-query-btn").attr("data-column",n.name).html(t.data("action-btn"))));t.find("tbody").append(o)}))}))}addDbTableToQuery(n){const t=e(".js-db-tables-select").find("option:selected");0!==t.length?this.addToQuery(t.val()):alert(e(n.target).data("choose-table-message"))}addDbTableColumnToQuery(n){this.addToQuery(e(n.target).data("column"))}addToQuery(n){const t=e("#sql_request_sql");t.val(`${t.val()} ${n}`)}}e((()=>{new o})),window.sql_manager={}})();