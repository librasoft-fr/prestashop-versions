(()=>{"use strict";var e={r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},o={};e.r(o);
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
 */const t=".js-form-submit-btn",{$:n}=window;class r{constructor(){n(document).on("click",t,(e=>{e.preventDefault();const o=n(e.target);if(o.data("form-confirm-message")&&!1===window.confirm(o.data("form-confirm-message")))return;let t="POST",r=null;if(o.data("method")){const e=o.data("method"),i=["GET","POST"].includes(e);t=i?e:"POST",i||(r=n("<input>",{type:"_hidden",name:"_method",value:e}))}const i=n("<form>",{action:o.data("form-submit-url"),method:t});r&&i.append(r),o.data("form-csrf-token")&&i.append(n("<input>",{type:"_hidden",name:"_csrf_token",value:o.data("form-csrf-token")})),i.appendTo("body").submit()}))}}
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
 */const i="#supplier_id_country",a="#supplier_id_state",s=".js-supplier-state",d="#supplier_dni",p='label[for="supplier_dni"]',{$:l}=window;l(document).ready((()=>{new window.prestashop.component.ChoiceTree("#supplier_shop_association").enableAutoCheckChildren(),new window.prestashop.component.CountryStateSelectionToggler(i,a,s),new window.prestashop.component.CountryDniRequiredToggler(i,d,p),window.prestashop.component.initComponents(["TinyMCEEditor","TranslatableInput","TranslatableField"]),new window.prestashop.component.TaggableField({tokenFieldSelector:"input.js-taggable-field",options:{createTokensOnBlur:!0}}),new r})),window.supplier_form=o})();