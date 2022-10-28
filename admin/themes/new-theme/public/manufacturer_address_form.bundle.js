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
const{$:n}=window;class r{constructor(t,e,r){this.$stateSelectionBlock=n(r),this.$countryStateSelector=n(e),this.$countryInput=n(t),this.$countryInput.on("change",(()=>this.change()))}change(){const t=this.$countryInput.val();""!==t&&n.get({url:this.$countryInput.data("states-url"),dataType:"json",data:{id_country:t}}).then((t=>{this.$countryStateSelector.empty(),Object.keys(t.states).forEach((e=>{this.$countryStateSelector.append(n("<option></option>").attr("value",t.states[e]).text(e))})),this.toggle()})).catch((t=>{void 0!==t.responseJSON&&window.showErrorMessage(t.responseJSON.message)}))}toggle(){this.$stateSelectionBlock.toggleClass("d-none",0===this.$countryStateSelector.find("option").length)}}
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
 */const o="#manufacturer_address_id_country",s="#manufacturer_address_id_state",a=".js-manufacturer-address-state",c="#manufacturer_address_dni",u='label[for="manufacturer_address_dni"]',{$:i}=window;class d{constructor(t,e,n){this.$countryDniInput=i(e),this.$countryDniInputLabel=i(n),this.$countryInput=i(t),this.countryInputSelectedSelector=`${t}>option:selected`,this.countryDniInputLabelDangerSelector=`${n}>span.text-danger`,this.$countryDniInput.attr("required")||(this.$countryInput.on("change",(()=>this.toggle())),this.toggle())}toggle(){i(this.countryDniInputLabelDangerSelector).remove(),this.$countryDniInput.prop("required",!1),1===parseInt(i(this.countryInputSelectedSelector).attr("need_dni"),10)&&(this.$countryDniInput.prop("required",!0),this.$countryDniInputLabel.prepend(i('<span class="text-danger">*</span>')))}}
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
const{$:l}=window;l(document).ready((()=>{new r(o,s,a),new d(o,c,u)})),window.manufacturer_address_form=e})();