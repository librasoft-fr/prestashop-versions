(()=>{"use strict";var t={r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},e={};t.r(e);
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
const{$:s}=window;class o{constructor(t,e={}){this.map=e,this.$emailInput=s(t),this.$emailInput.on("change",(()=>this.change()))}change(){s.get({url:this.$emailInput.data("customer-information-url"),dataType:"json",data:{email:this.$emailInput.val()}}).then((t=>{Object.keys(this.map).forEach((e=>{void 0!==t[e]&&s(this.map[e]).val(t[e])}))})).catch((t=>{void 0!==t.responseJSON&&window.showErrorMessage(t.responseJSON.message)}))}}
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
const{$:n}=window;class r{constructor(t,e,s){this.$stateSelectionBlock=n(s),this.$countryStateSelector=n(e),this.$countryInput=n(t),this.$countryInput.on("change",(()=>this.change()))}change(){const t=this.$countryInput.val();""!==t&&n.get({url:this.$countryInput.data("states-url"),dataType:"json",data:{id_country:t}}).then((t=>{this.$countryStateSelector.empty(),Object.keys(t.states).forEach((e=>{this.$countryStateSelector.append(n("<option></option>").attr("value",t.states[e]).text(e))})),this.toggle()})).catch((t=>{void 0!==t.responseJSON&&window.showErrorMessage(t.responseJSON.message)}))}toggle(){this.$stateSelectionBlock.toggleClass("d-none",0===this.$countryStateSelector.find("option").length)}}
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
const{$:c}=window;class a{constructor(t,e,s){this.$countryDniInput=c(e),this.$countryDniInputLabel=c(s),this.$countryInput=c(t),this.countryInputSelectedSelector=`${t}>option:selected`,this.countryDniInputLabelDangerSelector=`${s}>span.text-danger`,this.$countryDniInput.attr("required")||(this.$countryInput.on("change",(()=>this.toggle())),this.toggle())}toggle(){c(this.countryDniInputLabelDangerSelector).remove(),this.$countryDniInput.prop("required",!1),1===parseInt(c(this.countryInputSelectedSelector).attr("need_dni"),10)&&(this.$countryDniInput.prop("required",!0),this.$countryDniInputLabel.prepend(c('<span class="text-danger">*</span>')))}}
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
const{$:i}=window;class u{constructor(t,e,s){this.$countryPostcodeInput=i(e),this.$countryPostcodeInputLabel=i(s),this.$countryInput=i(t),this.countryInputSelectedSelector=`${t}>option:selected`,this.countryPostcodeInputLabelDangerSelector=`${s}>span.text-danger`,this.$countryPostcodeInput.attr("required")||(this.$countryInput.on("change",(()=>this.toggle())),this.toggle())}toggle(){i(this.countryPostcodeInputLabelDangerSelector).remove(),this.$countryPostcodeInput.prop("required",!1),1===parseInt(i(this.countryInputSelectedSelector).attr("need_postcode"),10)&&(this.$countryPostcodeInput.prop("required",!0),this.$countryPostcodeInputLabel.prepend(i('<span class="text-danger">*</span>')))}}
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
 */const d="#customer_address_customer_email",p="#customer_address_first_name",l="#customer_address_last_name",h="#customer_address_company",y="#customer_address_id_country",g="#customer_address_id_state",m=".js-address-state-select",$="#customer_address_dni",I='label[for="customer_address_dni"]',_="#customer_address_postcode",S='label[for="customer_address_postcode"]',{$:w}=window;w(document).ready((()=>{new o(d,{firstName:p,lastName:l,company:h}),new r(y,g,m),new a(y,$,I),new u(y,_,S)})),window.customer_address_form=e})();