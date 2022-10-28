(()=>{"use strict";var e={r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},t={};e.r(t);
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
const{$:n}=window;class o{constructor(e,t,o,r){this.$reductionTypeSelector=n(e),this.$taxInclusionInputs=n(t),this.currencySymbolSelect=o,this.reductionAmountSymbolSelector=r,this.handle(),this.$reductionTypeSelector.on("change",(()=>this.handle()))}handle(){const e="percentage"===this.$reductionTypeSelector.val();if(e?this.$taxInclusionInputs.fadeOut():this.$taxInclusionInputs.fadeIn(),""!==this.reductionAmountSymbolSelector){const t=document.querySelectorAll(this.reductionAmountSymbolSelector);t.length&&t.forEach((t=>{t.innerHTML=e?"%":this.getSymbol(t.innerHTML)}))}}getSymbol(e){var t,n;const o=document.querySelector(this.currencySymbolSelect);if(!o)return e;const r=null!=(t=o.dataset.defaultCurrencySymbol)?t:"",c=o.item(o.selectedIndex);return c&&null!=(n=c.getAttribute("symbol"))?n:r}}
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
class r{constructor(e,t){this.currencySymbolSelect=e,this.callbackChange=t,this.init()}init(){const e=document.querySelector(this.currencySymbolSelect);e&&(this.callbackChange(this.getSymbol(e)),e.addEventListener("change",(()=>this.callbackChange(this.getSymbol(e)))))}getSymbol(e){var t,n;const o=null!=(t=e.dataset.defaultCurrencySymbol)?t:"",r=e.item(e.selectedIndex);return r&&null!=(n=r.getAttribute("symbol"))?n:o}}
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
const{$:c}=window;class l{constructor(e,t){this.$sourceSelector=c(e),this.$targetSelector=c(t),this.handle(),this.$sourceSelector.on("change",(()=>this.handle()))}handle(){const e=this.$sourceSelector.is(":checked");this.$targetSelector.prop("disabled",e)}}
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
 */const i="#catalog_price_rule_leave_initial_price",u="#catalog_price_rule_price",s="#catalog_price_rule_id_currency",a="#catalog_price_rule_reduction_type",d=".price-reduction-value .input-group .input-group-append .input-group-text, .price-reduction-value .input-group .input-group-prepend .input-group-text",h=".js-include-tax-row",{$:p}=window;p((()=>{new r(s,(e=>{if(""===e)return;const t=document.querySelector(a);if(t){for(let n=0;n<t.options.length;n+=1){const o=t.options[n];"amount"===o.value&&(o.innerHTML=e)}if("amount"===t.options[t.selectedIndex].value){const t=document.querySelectorAll(d);t.length&&t.forEach((t=>{t.innerHTML=e}))}}})),new l(i,u),new o(a,h,s,d)})),window.catalog_price_rule_form=t})();