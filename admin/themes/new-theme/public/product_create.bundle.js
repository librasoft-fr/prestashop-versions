(()=>{"use strict";var e={428:e=>{e.exports=window.jQuery}},t={};
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
const o={headerSelector:"#product_header_type",headerPreviewButton:".product-type-preview",switchModalId:"switch-product-type-modal",switchModalSelector:"#switch-product-type-modal .header-product-type-selector",switchModalContent:"#product-type-selector-modal-content",switchModalButton:"#switch-product-type-modal .btn-confirm-submit",productTypeSelector:{choicesContainer:".product-type-choices",typeChoices:".product-type-choice",defaultChoiceClass:"btn-outline-secondary",selectedChoiceClass:"btn-primary",typeDescription:".product-type-description-content"}},s={newProductButton:".new-product-button",createModalSelector:"#create_product_type",modalId:"modal-create-product",form:"form.product-form",createFieldId:"#create_product",modalSizeContainer:".create-product-form"};var c=function o(s){var c=t[s];if(void 0!==c)return c.exports;var i=t[s]={exports:{}};return e[s](i,i.exports,o),i.exports}(428);
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
 */const i=o.productTypeSelector;class r{constructor(e,t=void 0){this.$typeSelector=c(e),this.$descriptionContainer=c(i.typeDescription),this.initialType=t,this.init()}init(){if(c(i.choicesContainer).on("click",i.typeChoices,(e=>{const t=c(e.currentTarget);this.selectChoice(t.data("value"))})),c(i.choicesContainer).on("mouseenter",i.typeChoices,(e=>{const t=c(e.currentTarget);this.displayDescription(t.data("description"))})),c(i.choicesContainer).on("mouseleave",i.typeChoices,(()=>{this.displaySelectedDescription()})),this.selectChoice(this.$typeSelector.find(":selected").val()),this.initialType){c(`${i.typeChoices}[data-value=${this.initialType}]`).prop("disabled",!0)}}selectChoice(e){const t=c(`${i.typeChoices}[data-value=${e}]`);c(i.typeChoices).removeClass(i.selectedChoiceClass),c(i.typeChoices).addClass(i.defaultChoiceClass),t.removeClass(i.defaultChoiceClass),t.addClass(i.selectedChoiceClass),this.$typeSelector.val(t.data("value")).trigger("change"),this.displaySelectedDescription()}displayDescription(e){this.$descriptionContainer.html(e)}displaySelectedDescription(){this.displayDescription(this.$typeSelector.find(":selected").data("description"))}}
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
 */const a={container:".shop-selector",selectInput:".shop-selector-input",searchInput:".js-shop-selector-search",shopItem:".shop-selector-shop-item",selectedClass:"selected-shop",currentClass:"current-shop",shopStatus:".shop-selector-status"},{$:n}=window;n((()=>{window.prestashop.component.initComponents(["ShopSelector"]);const e=document.querySelector(a.selectInput),t=null==e?void 0:e.closest(".form-group");if(t){const o=document.querySelectorAll(`${s.createFieldId} > .form-group`);o.forEach((e=>{e.classList.add("d-none")})),t.classList.remove("d-none"),null==e||e.addEventListener("change",(()=>{o.forEach((e=>{e.classList.remove("d-none")})),t.classList.add("d-none")}))}new r(s.createModalSelector)})),window.product_create={}})();