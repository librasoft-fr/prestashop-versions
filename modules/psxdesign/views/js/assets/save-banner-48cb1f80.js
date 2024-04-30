var a=Object.defineProperty;var i=(s,e,t)=>e in s?a(s,e,{enumerable:!0,configurable:!0,writable:!0,value:t}):s[e]=t;var n=(s,e,t)=>(i(s,typeof e!="symbol"?e+"":e,t),t);/**
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
 */class h{constructor(){this.autoDiscover()}autoDiscover(){const e=document.querySelector(".save-banner");new r({el:e})}}class r{constructor(e){n(this,"el");n(this,"cancelButton");n(this,"saveButton");n(this,"form");this.el=e.el,this.cancelButton=this.el.querySelector("#cancel-button"),this.saveButton=this.el.querySelector("#save-button"),this.form=this.saveButton.form,this.init()}init(){this.el==null||this.saveButton==null||this.addFormListener()}addFormListener(){var e,t;(e=this.form)==null||e.addEventListener("change",()=>{this.showSaveBanner()}),(t=this.form)==null||t.addEventListener("reset",()=>{this.hiddeSaveBanner()})}showSaveBanner(){this.el.classList.contains("save-banner--hidden")&&this.el.classList.remove("save-banner--hidden")}hiddeSaveBanner(){this.el.classList.contains("save-banner--hidden")||this.el.classList.add("save-banner--hidden")}}export{h as I};
