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
const{$:t}=window;class e{constructor(e,s={}){this.map=s,this.$emailInput=t(e),this.$emailInput.on("change",(()=>this.change()))}change(){t.get({url:this.$emailInput.data("customer-information-url"),dataType:"json",data:{email:this.$emailInput.val()}}).then((e=>{Object.keys(this.map).forEach((s=>{void 0!==e[s]&&t(this.map[s]).val(e[s])}))})).catch((t=>{void 0!==t.responseJSON&&window.showErrorMessage(t.responseJSON.message)}))}}
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
const{$:s}=window;class o{constructor(t,e,o){this.$countryPostcodeInput=s(e),this.$countryPostcodeInputLabel=s(o),this.$countryInput=s(t),this.countryInputSelectedSelector=`${t}>option:selected`,this.countryPostcodeInputLabelDangerSelector=`${o}>span.text-danger`,this.$countryPostcodeInput.attr("required")||(this.$countryInput.on("change",(()=>this.toggle())),this.toggle())}toggle(){s(this.countryPostcodeInputLabelDangerSelector).remove(),this.$countryPostcodeInput.prop("required",!1),1===parseInt(s(this.countryInputSelectedSelector).attr("need_postcode"),10)&&(this.$countryPostcodeInput.prop("required",!0),this.$countryPostcodeInputLabel.prepend(s('<span class="text-danger">*</span>')))}}
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
 */const r="#customer_address_customer_email",n="#customer_address_first_name",a="#customer_address_last_name",c="#customer_address_company",d="#customer_address_id_country",i="#customer_address_id_state",u=".js-address-state-select",p="#customer_address_dni",l='label[for="customer_address_dni"]',m="#customer_address_postcode",h='label[for="customer_address_postcode"]',{$:_}=window;_((()=>{new e(r,{firstName:n,lastName:a,company:c}),new window.prestashop.component.CountryStateSelectionToggler(d,i,u),new window.prestashop.component.CountryDniRequiredToggler(d,p,l),new o(d,m,h)})),window.customer_address_form={}})();