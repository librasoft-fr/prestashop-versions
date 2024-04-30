var l=Object.defineProperty;var u=(i,t,e)=>t in i?l(i,t,{enumerable:!0,configurable:!0,writable:!0,value:e}):i[t]=e;var o=(i,t,e)=>(u(i,typeof t!="symbol"?t+"":t,e),e);import{d as a}from"./assets/dom-utils-d77254b8.js";import{i as p,t as d,g as h,h as c}from"./assets/sentry-96f4e833.js";import{I as m}from"./assets/save-banner-48cb1f80.js";/**
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
 */const v=new Event("change");class C{constructor(){this.autoDiscover()}autoDiscover(){document.querySelectorAll(".color-input").forEach(t=>{new y({el:t})})}}class y{constructor(t){o(this,"el");o(this,"inputColor");o(this,"inputHex");o(this,"buttonCopy");o(this,"updatedTitle");o(this,"form");o(this,"deleteTooltipTimeOut");this.el=t.el,this.inputColor=this.el.querySelector(".color-input__color"),this.inputHex=this.el.querySelector(".color-input__hex"),this.buttonCopy=this.el.querySelector(".input_color__copy"),this.updatedTitle=this.buttonCopy.dataset.updatedTitle,this.form=this.inputColor.form,this.deleteTooltipTimeOut=null,this.init()}init(){this.el==null||this.inputColor==null||this.inputHex==null||(this.buttonCopy!=null&&this.addButtonCopyListener(),this.inputHex.value=this.inputColor.value,this.addInputColorListener(),this.addInputHexListener(),this.addFormListener())}dispatchChangeEventInForm(){this.form!=null&&this.form.dispatchEvent(v)}addInputColorListener(){this.inputColor.addEventListener("input",t=>{this.updateInputHexValue(t.target.value),this.removeError(),this.dispatchChangeEventInForm()})}updateInputColorValue(t){this.inputColor.value=t}addInputHexListener(){this.inputHex.addEventListener("input",t=>{this.updateInputColorValue(t.target.value),this.inputHex.validity.patternMismatch||(this.removeError(),this.dispatchChangeEventInForm())}),this.inputHex.addEventListener("focusout",t=>{this.inputHex.validity.patternMismatch&&this.addError()})}updateInputHexValue(t){this.inputHex.value=t}addError(){this.el.classList.add("is-invalid"),this.inputHex.setAttribute("aria-invalid","true")}removeError(){this.el.classList.remove("is-invalid"),this.inputHex.setAttribute("aria-invalid","false")}addButtonCopyListener(){this.buttonCopy.addEventListener("click",()=>{this.copyHex(),this.changeCopyMessageToCopied()})}changeCopyMessageToCopied(){const t=this.buttonCopy.getAttribute("aria-describedby"),e=document.getElementById(t),r=e.offsetWidth,n=e.querySelector(".tooltip-inner");if(n!=null){n.innerHTML=this.updatedTitle;const s=e.offsetWidth;e.style.left=`${Number(e.style.left.replace("px",""))+(r-s)}px`}this.deleteTooltipTimeOut!==null&&clearTimeout(this.deleteTooltipTimeOut),this.deleteTooltipTimeOut=setTimeout(()=>{e.classList.remove("show"),e.classList.add("hide"),this.buttonCopy.blur()},2e3)}copyHex(){navigator.clipboard.writeText(this.inputColor.value)}addFormListener(){var t;(t=this.form)==null||t.addEventListener("reset",()=>{this.removeError()})}}/**
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
 */a(()=>{p(),d("Colors");const i=h("current_color_palette");i!=null&&c(i),new C,new m});
