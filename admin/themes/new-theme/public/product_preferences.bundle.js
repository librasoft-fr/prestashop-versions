(()=>{"use strict";var e={d:(t,a)=>{for(var o in a)e.o(a,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:a[o]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},t={};e.r(t),e.d(t,{default:()=>p});
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
const{$:a}=window;const o=class{constructor(){this.handle(),a('input[name="stock[stock_management]"]').on("change",(()=>this.handle()))}handle(){const e=a('input[name="stock[stock_management]"]:checked').val(),t=parseInt(e,10);this.handleAllowOrderingOutOfStockOption(t),this.handleDisplayAvailableQuantitiesOption(t)}handleAllowOrderingOutOfStockOption(e){const t=a('input[name="stock[allow_ordering_oos]"]');e?t.removeAttr("disabled"):(t.val(["1"]),t.attr("disabled","disabled"))}handleDisplayAvailableQuantitiesOption(e){const t=a('input[name="page[display_quantities]"]');e?t.removeAttr("disabled"):(t.val(["0"]),t.attr("disabled","disabled"))}};var n=Object.defineProperty,l=Object.getOwnPropertySymbols,i=Object.prototype.hasOwnProperty,d=Object.prototype.propertyIsEnumerable,r=(e,t,a)=>t in e?n(e,t,{enumerable:!0,configurable:!0,writable:!0,value:a}):e[t]=a;
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
const{$:s}=window;const c=class{constructor(e){this.pageMap=((e,t)=>{for(var a in t||(t={}))i.call(t,a)&&r(e,a,t[a]);if(l)for(var a of l(t))d.call(t,a)&&r(e,a,t[a]);return e})({catalogModeField:'input[name="general[catalog_mode]"]',selectedCatalogModeField:'input[name="general[catalog_mode]"]:checked',catalogModeOptions:".catalog-mode-option"},e),this.handle(0),s(this.pageMap.catalogModeField).on("change",(()=>this.handle(600)))}handle(e){const t=s(this.pageMap.selectedCatalogModeField).val(),a=parseInt(t,10),o=s(this.pageMap.catalogModeOptions);a?o.show(e):o.hide(e/2)}},p={catalogModeField:'input[name="general[catalog_mode]"]',selectedCatalogModeField:'input[name="general[catalog_mode]"]:checked',catalogModeOptions:".catalog-mode-option"},{$:g}=window;g((()=>{window.prestashop.component.initComponents(["TranslatableInput"]),new o,new c(t)})),window.product_preferences={}})();