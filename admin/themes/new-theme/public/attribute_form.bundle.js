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
 */const e="#attribute_shop_association",t="#attribute_attribute_group",o=".js-attribute-type-color-form-row",n=".js-attribute-type-texture-form-row",{$:r}=window;r((()=>{window.prestashop.component.initComponents(["TranslatableInput","TranslatableField"]),new window.prestashop.component.ChoiceTree(e).enableAutoCheckChildren()})),document.addEventListener("DOMContentLoaded",(()=>{const e=document.querySelector(t),r=document.querySelector(o),a=document.querySelector(n),i=null==e?void 0:e.value,l=e=>{if(r&&a){const t="2"===e?"flex":"none";r.style.display=t,a.style.display=t}};i&&l(i),null==e||e.addEventListener("change",(()=>{const t=null==e?void 0:e.value;t&&l(t)}))})),window.attribute_form={}})();