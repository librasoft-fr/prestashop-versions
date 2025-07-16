(()=>{"use strict";var n={428:n=>{n.exports=window.jQuery}},o={};var t=function t(e){var s=o[e];if(void 0!==s)return s.exports;var i=o[e]={exports:{}};return n[e](i,i.exports,t),i.exports}(428);
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
t((()=>{const n=new window.prestashop.component.Grid("attribute");n.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),n.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),n.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),n.addExtension(new window.prestashop.component.GridExtensions.PositionExtension(n)),n.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension)})),window.attribute={}})();