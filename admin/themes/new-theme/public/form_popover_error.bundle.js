(()=>{"use strict";var r={428:r=>{r.exports=window.jQuery}},o={};var t=function t(e){var n=o[e];if(void 0!==n)return n.exports;var s=o[e]={exports:{}};return r[e](s,s.exports,t),s.exports}(428);
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
t((()=>{t('[data-toggle="form-popover-error"]').popover({html:!0,content(){return o(this)}});const r=(r,o)=>{const t=r.offset(),e=o.offset();if(t&&e){return t.left-e.left}return null},o=r=>{const o=t(r).data("id");return t(`.js-popover-error-content[data-id="${o}"]`).html()};t(document).on("shown.bs.popover",'[data-toggle="form-popover-error"]',(o=>(o=>{const e=t(o.currentTarget).closest(".form-group"),n=e.find(".invalid-feedback-container"),s=e.find(".form-popover-error"),p=n.width();s.css("width",p);const f=r(n,s);s.css("left",`${f}px`)})(o)))})),window.form_popover_error={}})();