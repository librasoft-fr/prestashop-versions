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
 */const e=".js-form-submit-btn",{$:n}=window;class o{constructor(){n(document).on("click",e,(e=>{e.preventDefault();const o=n(e.target);if(o.data("form-confirm-message")&&!1===window.confirm(o.data("form-confirm-message")))return;let t="POST",i=null;if(o.data("method")){const e=o.data("method"),s=["GET","POST"].includes(e);t=s?e:"POST",s||(i=n("<input>",{type:"_hidden",name:"_method",value:e}))}const s=n("<form>",{action:o.data("form-submit-url"),method:t});i&&s.append(i),o.data("form-csrf-token")&&s.append(n("<input>",{type:"_hidden",name:"_csrf_token",value:o.data("form-csrf-token")})),s.appendTo("body").submit()}))}}
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
 */const t="#supplier_id_country",i="#supplier_id_state",s=".js-supplier-state",a="#supplier_dni",r='label[for="supplier_dni"]',{$:p}=window;p((()=>{new window.prestashop.component.ChoiceTree("#supplier_shop_association").enableAutoCheckChildren(),new window.prestashop.component.CountryStateSelectionToggler(t,i,s),new window.prestashop.component.CountryDniRequiredToggler(t,a,r),window.prestashop.component.initComponents(["TinyMCEEditor","TranslatableInput","TranslatableField"]),new window.prestashop.component.TaggableField({tokenFieldSelector:"input.js-taggable-field",options:{createTokensOnBlur:!0}}),new o})),window.supplier_form={}})();