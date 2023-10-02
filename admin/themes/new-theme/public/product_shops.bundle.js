(()=>{"use strict";var o={9567:o=>{o.exports=window.jQuery}},e={};function t(s){var r=e[s];if(void 0!==r)return r.exports;var p=e[s]={exports:{}};return o[s](p,p.exports,t),p.exports}t.r=o=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(o,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(o,"__esModule",{value:!0})};var s={};(()=>{t.r(s);
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
const o="shop-preview-row",e={modalButtons:"a.product-shops-action",modalId:"modal-product-shops",form:'form[name="product_shops"]',modalSizeContainer:".product-shops-form",cancelButton:"#product_shops_buttons_cancel",editProductClass:"multi-shop-edit-product",selectorItem:".shop-selector-item",shopItemClass:"shop-selector-shop-item",groupShopItemClass:"shop-selector-group-item",shopListCell:".column-associated_shops .product-shop-list",contextWarning:".multi-shop-context-warning",shopPreviews:{toggleButtons:".product-shop-details-toggle",loadingRowClass:"loading-shop-row",expandedShopRowClass:"expanded-shop-row",shopPreviewRowClass:o,productPreviewsSelector:e=>`.${o}[data-product-id="${e}"]`}},r="cancelProductShops";
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
t(9567)((()=>{var o;window.prestashop.component.initComponents(["ShopSelector","IframeClient"]);const t=window.prestashop.instance.iframeClient;null==(o=document.querySelector(e.cancelButton))||o.addEventListener("click",(()=>{t.dispatchEvent(r)}))}))})(),window.product_shops=s})();