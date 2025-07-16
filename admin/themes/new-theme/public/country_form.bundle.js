(()=>{"use strict";
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
function t(t){return void 0===t}var e=Object.defineProperty,a=Object.getOwnPropertySymbols,n=Object.prototype.hasOwnProperty,o=Object.prototype.propertyIsEnumerable,i=(t,a,n)=>a in t?e(t,a,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[a]=n;
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
const{$:r}=window;var l=(t=>(t.availability="availability",t.visibility="visibility",t))(l||{});class s{constructor(t){this.params=((t,e)=>{for(var r in e||(e={}))n.call(e,r)&&i(t,r,e[r]);if(a)for(var r of a(e))o.call(e,r)&&i(t,r,e[r]);return t})({matchingValue:"0",disableOnMatch:!0,targetSelector:null,switchEvent:null,toggleType:"availability"},t),this.init()}init(){document.querySelectorAll(this.params.disablingInputSelector).forEach((t=>{this.updateTargetState(t),r(t).on("change",(()=>{this.updateTargetState(t)}))}))}updateTargetState(e){var a,n,o;const i=this.getInputValue(e);if(t(i))return;const r=null!=(a=e.dataset.matchingValue)?a:this.params.matchingValue,l=null!=(n=e.dataset.targetSelector)?n:this.params.targetSelector,s=null!=(o=e.dataset.switchEvent)?o:this.params.switchEvent;let c,{disableOnMatch:d}=this.params;t(e.dataset)||t(e.dataset.disableOnMatch)||(d="1"===e.dataset.disableOnMatch),null!==r?null!==l?(c=i===r?d:!d,this.toggle(l,c,s)):console.error("No target selector defined for inputElement",e):console.error("No matching value defined for inputElement",e)}getInputValue(t){switch(t.type){case"radio":{let e;return document.querySelectorAll(`[name="${t.name}"]`).forEach((t=>{t.checked&&(e=t.value)})),e}case"checkbox":return t.checked?t.value:void 0;default:return t.value}}toggle(t,e,a){if(a){const{eventEmitter:n}=window.prestashop.instance;if(n){const o={targetSelector:t,disable:e};n.emit(a,o)}else console.error("Trying to use EventEmitter without having initialised the component before.")}const n=document.querySelectorAll(t);0!==n.length?n.forEach((t=>{const a="availability"===this.params.toggleType;a?(t.classList.toggle("disabled",e),t.toggleAttribute("disabled",e)):t.classList.toggle("d-none",e);const n=t.querySelectorAll("input, select, textarea, button, option, fieldset");0!==n.length&&n.forEach((t=>{a&&t.toggleAttribute("disabled",e)}))})):console.error(`Could not find target ${t}`)}}
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
 */const c='input[name="country[need_zip_code]"]',d="#country_zip_code_format";
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
class u{constructor(){this.initZipCodeToggler()}initZipCodeToggler(){new s({disablingInputSelector:c,targetSelector:d,toggleType:l.availability})}}
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
 */const p=".js-form-submit-btn",{$:m}=window;class g{constructor(){m(document).on("click",p,(t=>{t.preventDefault();const e=m(t.target);if(e.data("form-confirm-message")&&!1===window.confirm(e.data("form-confirm-message")))return;let a="POST",n=null;if(e.data("method")){const t=e.data("method"),o=["GET","POST"].includes(t);a=o?t:"POST",o||(n=m("<input>",{type:"_hidden",name:"_method",value:t}))}const o=m("<form>",{action:e.data("form-submit-url"),method:a});n&&o.append(n),e.data("form-csrf-token")&&o.append(m("<input>",{type:"_hidden",name:"_csrf_token",value:e.data("form-csrf-token")})),o.appendTo("body").submit()}))}}
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
const{$:h}=window;h((()=>{window.prestashop.component.initComponents(["TranslatableInput"]),new g,new u})),window.country_form={}})();