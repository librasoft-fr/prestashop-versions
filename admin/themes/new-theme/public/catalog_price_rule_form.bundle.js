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
class e{constructor(e,t){this.currencySymbolSelect=e,this.selectCurrency=document.querySelector(this.currencySymbolSelect),this.callbackChange=t,this.selectCurrency?this.init():console.error(`Could not find ${this.currencySymbolSelect}`)}init(){const e=document.querySelector(this.currencySymbolSelect);e&&(this.callbackChange(this.getSymbol()),e.addEventListener("change",(()=>this.callbackChange(this.getSymbol()))))}getSymbol(){var e,t;if(!this.selectCurrency)return"";const c=null!=(e=this.selectCurrency.dataset.defaultCurrencySymbol)?e:"",r=this.selectCurrency.item(this.selectCurrency.selectedIndex);return c||r||console.error("Could not find appropriate data attributes"),r&&null!=(t=r.getAttribute("symbol"))?t:c}}
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
const{$:t}=window;class c{constructor(c,r,n,o){this.reductionTypeSelector=c,this.$reductionTypeSelect=t(c),this.$taxInclusionInputs=t(r),this.currencySelect=n,this.reductionValueSymbolSelector=o,this.currencySymbolUpdater=new e(this.currencySelect,(e=>{""!==e&&this.updateSymbol(e)})),this.handle(),this.$reductionTypeSelect.on("change",(()=>this.handle()))}handle(){"percentage"===this.$reductionTypeSelect.val()?this.$taxInclusionInputs.fadeOut():this.$taxInclusionInputs.fadeIn(),this.updateSymbol(this.currencySymbolUpdater.getSymbol())}updateSymbol(e){const t=document.querySelector(this.reductionTypeSelector);if(t){for(let c=0;c<t.options.length;c+=1){const r=t.options[c];"amount"===r.value&&(r.innerHTML=e)}const c=t.options[t.selectedIndex].value,r=document.querySelectorAll(this.reductionValueSymbolSelector);if(0===r.length)return;r.forEach((t=>{t.innerHTML="amount"===c?e:"%"}))}}}
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
const{$:r}=window;class n{constructor(e,t){this.$sourceSelector=r(e),this.$targetSelector=r(t),this.handle(),this.$sourceSelector.on("change",(()=>this.handle()))}handle(){const e=this.$sourceSelector.is(":checked");this.$targetSelector.prop("disabled",e)}}
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
 */const o="#catalog_price_rule_leave_initial_price",i="#catalog_price_rule_price",l="#catalog_price_rule_id_currency",s="#catalog_price_rule_reduction_type",u=".price-reduction-value .input-group .input-group-append .input-group-text, .price-reduction-value .input-group .input-group-prepend .input-group-text",a=".js-include-tax-row",{$:h}=window;h((()=>{new n(o,i),new c(s,a,l,u)})),window.catalog_price_rule_form={}})();