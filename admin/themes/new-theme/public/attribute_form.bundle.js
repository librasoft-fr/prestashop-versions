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
 */const t="#attribute_shop_association",e="#attribute_attribute_group",o=".js-attribute-type-color-form-row",n=".js-attribute-type-texture-form-row",a=".js-form-submit-btn",{$:r}=window;class d{constructor(){r(document).on("click",a,(t=>{t.preventDefault();const e=r(t.target);if(e.data("form-confirm-message")&&!1===window.confirm(e.data("form-confirm-message")))return;let o="POST",n=null;if(e.data("method")){const t=e.data("method"),a=["GET","POST"].includes(t);o=a?t:"POST",a||(n=r("<input>",{type:"_hidden",name:"_method",value:t}))}const a=r("<form>",{action:e.data("form-submit-url"),method:o});n&&a.append(n),e.data("form-csrf-token")&&a.append(r("<input>",{type:"_hidden",name:"_csrf_token",value:e.data("form-csrf-token")})),a.appendTo("body").submit()}))}}
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
const{$:s}=window;s((()=>{window.prestashop.component.initComponents(["TranslatableInput","TranslatableField"]),new window.prestashop.component.ChoiceTree(t).enableAutoCheckChildren(),new d})),document.addEventListener("DOMContentLoaded",(()=>{const t=document.querySelector(e),a=document.querySelector(o),r=document.querySelector(n),d=null==t?void 0:t.value,s=t=>{if(a&&r){const e="2"===t?"flex":"none";a.style.display=e,r.style.display=e}};d&&s(d),null==t||t.addEventListener("change",(()=>{const e=null==t?void 0:t.value;e&&s(e)}))})),window.attribute_form={}})();