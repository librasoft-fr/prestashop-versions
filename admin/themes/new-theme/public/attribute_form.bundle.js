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
 */const t="#attribute_shop_association",e="#attribute_attribute_group",o=".js-attribute-type-color-form-row",n=".js-attribute-type-texture-form-row",r=".js-form-submit-btn",{$:a}=window;class s{constructor(){a(document).on("click",r,(t=>{t.preventDefault();const e=a(t.target);if(e.data("form-confirm-message")&&!1===window.confirm(e.data("form-confirm-message")))return;let o="POST",n=null;if(e.data("method")){const t=e.data("method"),r=["GET","POST"].includes(t);o=r?t:"POST",r||(n=a("<input>",{type:"_hidden",name:"_method",value:t}))}const r=a("<form>",{action:e.data("form-submit-url"),method:o});n&&r.append(n),e.data("form-csrf-token")&&r.append(a("<input>",{type:"_hidden",name:"_csrf_token",value:e.data("form-csrf-token")})),r.appendTo("body").submit()}))}}
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
const{$:d}=window;d((()=>{window.prestashop.component.initComponents(["TranslatableInput","TranslatableField"]),new window.prestashop.component.ChoiceTree(t).enableAutoCheckChildren(),new s})),document.addEventListener("DOMContentLoaded",(()=>{const t=document.querySelector(e),r=document.querySelector(o),a=document.querySelector(n);if(!t||!r||!a)return;const s=()=>{const e=null==t?void 0:t.selectedOptions[0],o=(null==e?void 0:e.dataset.iscolorgroup)?"flex":"none";r.style.display=o,a.style.display=o};s(),null==t||t.addEventListener("change",s)})),window.attribute_form={}})();