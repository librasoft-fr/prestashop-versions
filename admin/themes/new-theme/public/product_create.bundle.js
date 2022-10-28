(()=>{"use strict";var e={9567:e=>{e.exports=window.jQuery}},t={};function o(i){var c=t[i];if(void 0!==c)return c.exports;var s=t[i]={exports:{}};return e[i](s,s.exports,o),s.exports}o.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var i={};(()=>{o.r(i);
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
const e={headerSelector:"#product_header_type",headerPreviewButton:".product-type-preview",switchModalId:"switch-product-type-modal",switchModalSelector:"#switch-product-type-modal .header-product-type-selector",switchModalContent:"#product-type-selector-modal-content",switchModalButton:"#switch-product-type-modal .btn-confirm-submit",productTypeSelector:{choicesContainer:".product-type-choices",typeChoices:".product-type-choice",defaultChoiceClass:"btn-outline-secondary",selectedChoiceClass:"btn-primary",typeDescription:".product-type-description-content"}},t={newProductButton:".new-product-button",createModalSelector:"#create_product_type"};var c=o(9567);
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
 */const s=e.productTypeSelector;class r{constructor(e,t){this.$typeSelector=c(e),this.$descriptionContainer=c(s.typeDescription),this.initialType=t,this.init()}init(){if(c(s.choicesContainer).on("click",s.typeChoices,(e=>{const t=c(e.currentTarget);this.selectChoice(t.data("value"))})),c(s.choicesContainer).on("mouseenter",s.typeChoices,(e=>{const t=c(e.currentTarget);this.displayDescription(t.data("description"))})),c(s.choicesContainer).on("mouseleave",s.typeChoices,(()=>{this.displaySelectedDescription()})),this.selectChoice(this.$typeSelector.find(":selected").val()),this.initialType){c(`${s.typeChoices}[data-value=${this.initialType}]`).prop("disabled",!0)}}selectChoice(e){const t=c(`${s.typeChoices}[data-value=${e}]`);c(s.typeChoices).removeClass(s.selectedChoiceClass),c(s.typeChoices).addClass(s.defaultChoiceClass),t.removeClass(s.defaultChoiceClass),t.addClass(s.selectedChoiceClass),this.$typeSelector.val(t.data("value")).trigger("change"),this.displaySelectedDescription()}displayDescription(e){this.$descriptionContainer.html(e)}displaySelectedDescription(){this.displayDescription(this.$typeSelector.find(":selected").data("description"))}}
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
const{$:a}=window;a((()=>{new r(t.createModalSelector)}))})(),window.product_create=i})();