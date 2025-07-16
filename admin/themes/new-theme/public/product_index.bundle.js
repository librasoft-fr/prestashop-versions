(()=>{"use strict";var e={428:e=>{e.exports=window.jQuery}},t={};function o(n){var s=t[n];if(void 0!==s)return s.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,o),i.exports}o.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}();
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
const n="shop-preview-row",s={modalButtons:"a.product-shops-action",modalId:"modal-product-shops",form:'form[name="product_shops"]',modalSizeContainer:".product-shops-form",cancelButton:"#product_shops_buttons_cancel",editProductClass:"multi-shop-edit-product",selectorItem:".shop-selector-item",shopItemClass:"shop-selector-shop-item",groupShopItemClass:"shop-selector-group-item",shopListCell:".column-associated_shops .product-shop-list",contextWarning:".multi-shop-context-warning",shopPreviews:{toggleButtons:".product-shop-details-toggle",loadingRowClass:"loading-shop-row",expandedShopRowClass:"expanded-shop-row",shopPreviewRowClass:n,productPreviewsSelector:e=>`.${n}[data-product-id="${e}"]`}},i={categoriesContainer:"#product_description_categories",categoriesModalTemplate:"#categories-modal-template",modalContentContainer:"#categories-modal-content",categoriesModalId:"categories-modal",applyCategoriesBtn:".js-apply-categories-btn",cancelCategoriesBtn:".js-cancel-categories-btn",categoryTree:".js-category-tree-list",treeElement:".category-tree-element",treeElementInputs:".category-tree-inputs",treeCheckboxInput:".tree-checkbox-input",checkboxInput:"[type=checkbox]",checkedCheckboxInputs:"[type=checkbox]:checked",checkboxName:e=>`product[description][categories][product_categories][${e}][is_associated]`,inputByValue:e=>`input[value="${e}"]`,defaultCategorySelectInput:"#product_description_categories_default_category_id",materialCheckbox:".md-checkbox",radioInput:"[type=radio]",defaultRadioInput:"[type=radio]:checked",radioName:e=>`product[description][categories][product_categories][${e}][is_default]`,tagsContainer:".pstaggerTagsWrapper",tagRemoveBtn:".pstaggerClosingCross",tagCategoryIdInput:".category-id-input",tagItem:".tag-item",categoryNamePreview:".category-name-preview",namePreviewInput:".category-name-preview-input",categoryNameInput:".category-name-input",searchInput:"#ps-select-product-category",fieldset:".tree-fieldset",loader:".categories-tree-loader",childrenList:".children-list",addCategoriesBtn:".add-categories-btn",categoryFilter:{container:".product_list_category_filter",categoryRadio:".category-label input:radio",filterForm:"#product_filter_form",positionInput:'input[name="product[position]"]',expandedClass:"less",collapsedClass:"more",categoryChildren:".category-children",categoryLabel:".category-label",categoryLabelClass:"category-label",categoryNode:".category-node",expandAll:".category_tree_filter_expand",collapseAll:".category_tree_filter_collapse",resetFilter:".category_tree_filter_reset"}},{$:r}=window,a=i.categoryFilter;class c{constructor(){this.$categoryTree=r(a.container),this.$filterForm=this.$categoryTree.parent("form"),this.init()}init(){this.$categoryTree.on("click",a.categoryLabel,(e=>{e.target instanceof HTMLInputElement?this.$filterForm.submit():e.target.classList.contains(a.categoryLabelClass)&&this.toggleCategory(r(e.currentTarget).parent(a.categoryNode))})),this.$categoryTree.on("click",a.expandAll,(()=>{this.expandAll()})),this.$categoryTree.on("click",a.collapseAll,(()=>{this.collapseAll()})),r(a.resetFilter).on("click",(()=>{this.resetFilter()})),this.collapseAll()}toggleCategory(e){const t=e.find(a.categoryChildren).first();if(!t.length)return;const o=e.hasClass(a.expandedClass);t.toggleClass("d-none",o),e.toggleClass(a.expandedClass,!o),e.toggleClass(a.collapsedClass,o)}resetFilter(){this.$categoryTree.find(a.categoryRadio).prop("checked",!1),this.$filterForm.submit()}expandAll(){this.$categoryTree.find(a.categoryChildren).removeClass("d-none"),this.$categoryTree.find(a.categoryChildren).parent(a.categoryNode).removeClass(a.collapsedClass).addClass(a.expandedClass)}collapseAll(){this.$categoryTree.find(a.categoryChildren).addClass("d-none"),this.$categoryTree.find(a.categoryChildren).parent(a.categoryNode).removeClass(a.expandedClass).addClass(a.collapsedClass)}}var d=o(428),l=Object.defineProperty,h=Object.getOwnPropertySymbols,p=Object.prototype.hasOwnProperty,u=Object.prototype.propertyIsEnumerable,m=(e,t,o)=>t in e?l(e,t,{enumerable:!0,configurable:!0,writable:!0,value:o}):e[t]=o,f=(e,t)=>{for(var o in t||(t={}))p.call(t,o)&&m(e,o,t[o]);if(h)for(var o of h(t))u.call(t,o)&&m(e,o,t[o]);return e};
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
class g{constructor(e){const t=f({id:"confirm-modal",closable:!1},e);this.buildModalContainer(t)}buildModalContainer(e){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=e.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),e.dialogStyle&&Object.keys(e.dialogStyle).forEach((t=>{this.dialog.style[t]=e.dialogStyle[t]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),e.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=e.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class y{constructor(e){const t=f({id:"confirm-modal",closable:!1,dialogStyle:{}},e);this.initContainer(t)}initContainer(e){this.modal||(this.modal=new g(e)),this.$modal=d(this.modal.container);const{id:t,closable:o}=e;this.$modal.modal({backdrop:!!o||"static",keyboard:void 0===o||o}),this.$modal.modal("hide"),this.$modal.on("hidden.bs.modal",(()=>{const o=document.querySelector(`#${t}`);o&&o.remove(),e.closeCallback&&e.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(e){return this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=e,this}render(e){return this.modal.message.innerHTML=e,this}show(){return this.$modal.modal("show"),this}hide(){return this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")})),this}}
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
function v(e){return void 0===e}Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;var b=function(){if("undefined"!=typeof Map)return Map;function e(e,t){var o=-1;return e.some((function(e,n){return e[0]===t&&(o=n,!0)})),o}return function(){function t(){this.__entries__=[]}return Object.defineProperty(t.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),t.prototype.get=function(t){var o=e(this.__entries__,t),n=this.__entries__[o];return n&&n[1]},t.prototype.set=function(t,o){var n=e(this.__entries__,t);~n?this.__entries__[n][1]=o:this.__entries__.push([t,o])},t.prototype.delete=function(t){var o=this.__entries__,n=e(o,t);~n&&o.splice(n,1)},t.prototype.has=function(t){return!!~e(this.__entries__,t)},t.prototype.clear=function(){this.__entries__.splice(0)},t.prototype.forEach=function(e,t){void 0===t&&(t=null);for(var o=0,n=this.__entries__;o<n.length;o++){var s=n[o];e.call(t,s[1],s[0])}},t}()}(),w="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,_=void 0!==o.g&&o.g.Math===Math?o.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),E="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(_):function(e){return setTimeout((function(){return e(Date.now())}),1e3/60)};var x=["top","right","bottom","left","width","height","size","weight"],C="undefined"!=typeof MutationObserver,O=function(){function e(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(e,t){var o=!1,n=!1,s=0;function i(){o&&(o=!1,e()),n&&a()}function r(){E(i)}function a(){var e=Date.now();if(o){if(e-s<2)return;n=!0}else o=!0,n=!1,setTimeout(r,t);s=e}return a}(this.refresh.bind(this),20)}return e.prototype.addObserver=function(e){~this.observers_.indexOf(e)||this.observers_.push(e),this.connected_||this.connect_()},e.prototype.removeObserver=function(e){var t=this.observers_,o=t.indexOf(e);~o&&t.splice(o,1),!t.length&&this.connected_&&this.disconnect_()},e.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},e.prototype.updateObservers_=function(){var e=this.observers_.filter((function(e){return e.gatherActive(),e.hasActive()}));return e.forEach((function(e){return e.broadcastActive()})),e.length>0},e.prototype.connect_=function(){w&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),C?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},e.prototype.disconnect_=function(){w&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},e.prototype.onTransitionEnd_=function(e){var t=e.propertyName,o=void 0===t?"":t;x.some((function(e){return!!~o.indexOf(e)}))&&this.refresh()},e.getInstance=function(){return this.instance_||(this.instance_=new e),this.instance_},e.instance_=null,e}(),L=function(e,t){for(var o=0,n=Object.keys(t);o<n.length;o++){var s=n[o];Object.defineProperty(e,s,{value:t[s],enumerable:!1,writable:!1,configurable:!0})}return e},I=function(e){return e&&e.ownerDocument&&e.ownerDocument.defaultView||_},A=$(0,0,0,0);function T(e){return parseFloat(e)||0}function S(e){for(var t=[],o=1;o<arguments.length;o++)t[o-1]=arguments[o];return t.reduce((function(t,o){return t+T(e["border-"+o+"-width"])}),0)}function k(e){var t=e.clientWidth,o=e.clientHeight;if(!t&&!o)return A;var n=I(e).getComputedStyle(e),s=function(e){for(var t={},o=0,n=["top","right","bottom","left"];o<n.length;o++){var s=n[o],i=e["padding-"+s];t[s]=T(i)}return t}(n),i=s.left+s.right,r=s.top+s.bottom,a=T(n.width),c=T(n.height);if("border-box"===n.boxSizing&&(Math.round(a+i)!==t&&(a-=S(n,"left","right")+i),Math.round(c+r)!==o&&(c-=S(n,"top","bottom")+r)),!function(e){return e===I(e).document.documentElement}(e)){var d=Math.round(a+i)-t,l=Math.round(c+r)-o;1!==Math.abs(d)&&(a-=d),1!==Math.abs(l)&&(c-=l)}return $(s.left,s.top,a,c)}var P="undefined"!=typeof SVGGraphicsElement?function(e){return e instanceof I(e).SVGGraphicsElement}:function(e){return e instanceof I(e).SVGElement&&"function"==typeof e.getBBox};function M(e){return w?P(e)?function(e){var t=e.getBBox();return $(0,0,t.width,t.height)}(e):k(e):A}function $(e,t,o,n){return{x:e,y:t,width:o,height:n}}var j=function(){function e(e){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=$(0,0,0,0),this.target=e}return e.prototype.isActive=function(){var e=M(this.target);return this.contentRect_=e,e.width!==this.broadcastWidth||e.height!==this.broadcastHeight},e.prototype.broadcastRect=function(){var e=this.contentRect_;return this.broadcastWidth=e.width,this.broadcastHeight=e.height,e},e}(),R=function(e,t){var o,n,s,i,r,a,c,d=(n=(o=t).x,s=o.y,i=o.width,r=o.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,c=Object.create(a.prototype),L(c,{x:n,y:s,width:i,height:r,top:s,right:n+i,bottom:r+s,left:n}),c);L(this,{target:e,contentRect:d})},B=function(){function e(e,t,o){if(this.activeObservations_=[],this.observations_=new b,"function"!=typeof e)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=e,this.controller_=t,this.callbackCtx_=o}return e.prototype.observe=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof I(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)||(t.set(e,new j(e)),this.controller_.addObserver(this),this.controller_.refresh())}},e.prototype.unobserve=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof I(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)&&(t.delete(e),t.size||this.controller_.removeObserver(this))}},e.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},e.prototype.gatherActive=function(){var e=this;this.clearActive(),this.observations_.forEach((function(t){t.isActive()&&e.activeObservations_.push(t)}))},e.prototype.broadcastActive=function(){if(this.hasActive()){var e=this.callbackCtx_,t=this.activeObservations_.map((function(e){return new R(e.target,e.broadcastRect())}));this.callback_.call(e,t,e),this.clearActive()}},e.prototype.clearActive=function(){this.activeObservations_.splice(0)},e.prototype.hasActive=function(){return this.activeObservations_.length>0},e}(),G="undefined"!=typeof WeakMap?new WeakMap:new b,F=function e(t){if(!(this instanceof e))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var o=O.getInstance(),n=new B(t,o,this);G.set(this,n)};["observe","unobserve","disconnect"].forEach((function(e){F.prototype[e]=function(){var t;return(t=G.get(this))[e].apply(t,arguments)}}));void 0!==_.ResizeObserver&&_.ResizeObserver;const q=class extends Event{constructor(e,t={}){super(q.parentWindowEvent),this.eventName=e,this.eventParameters=t}get name(){return this.eventName}get parameters(){return this.eventParameters}};let N=q;N.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
function D(e,t){if(v(e.dataset.modalTitle)||v(e.dataset.shopSelector))return;const o=new y({id:"select-shop-for-edition-modal",modalTitle:e.dataset.modalTitle,closable:!0});o.render(e.dataset.shopSelector),o.modal.container.querySelectorAll(`.${s.shopItemClass}`).forEach((o=>{v(o.dataset.shopId)||(-1===t.indexOf(o.dataset.shopId)?o.classList.add("d-none"):o.addEventListener("click",(()=>{document.location.href=`${e.getAttribute("href")}&setShopContext=s-${o.dataset.shopId}`})))}));let n=null,i=!0;const r=o.modal.container.querySelectorAll(s.selectorItem);r.forEach(((e,t)=>{e.classList.contains(s.groupShopItemClass)?(n&&i&&n.classList.add("d-none"),i=!0,n=e):e.classList.contains(s.shopItemClass)&&!e.classList.contains("d-none")&&(i=!1),t===r.length-1&&n&&i&&n.classList.add("d-none")})),o.show()}
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
function H(){document.querySelectorAll(s.shopPreviews.toggleButtons).forEach((e=>{e.addEventListener("click",(()=>{const t=e.closest("tr");t&&!t.classList.contains(s.shopPreviews.loadingRowClass)&&(t.classList.contains(s.shopPreviews.expandedShopRowClass)?function(e,t){e.classList.remove(s.shopPreviews.expandedShopRowClass),document.querySelectorAll(s.shopPreviews.productPreviewsSelector(t.dataset.productId)).forEach((e=>{e.remove()}))}(t,e):function(e,t){return o=this,n=null,i=function*(){if(!t.dataset.shopPreviewsUrl||!t.dataset.productId)return;e.classList.add(s.shopPreviews.loadingRowClass);const o=yield fetch(t.dataset.shopPreviewsUrl);if(!o.ok)return;e.classList.remove(s.shopPreviews.loadingRowClass),e.classList.add(s.shopPreviews.expandedShopRowClass),e.setAttribute("data-product-id",t.dataset.productId);const n=yield o.text();e.insertAdjacentHTML("afterend",n)},new Promise(((e,t)=>{var s=e=>{try{a(i.next(e))}catch(e){t(e)}},r=e=>{try{a(i.throw(e))}catch(e){t(e)}},a=t=>t.done?e(t.value):Promise.resolve(t.value).then(s,r);a((i=i.apply(o,n)).next())}));var o,n,i}(t,e))}))}))}
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
const{$:W}=window;W((()=>{const e=new window.prestashop.component.Grid("product");e.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),e.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),e.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),e.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.AjaxBulkActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),e.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),e.addExtension(new window.prestashop.component.GridExtensions.AsyncToggleColumnExtension),e.addExtension(new window.prestashop.component.GridExtensions.PositionExtension(e)),e.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension((e=>{var t,o,n,i,r;if(e.classList.contains(s.editProductClass)){const a=null!=(r=null==(i=null==(n=null==(o=null==(t=e.closest("tr"))?void 0:t.querySelector(s.shopListCell))?void 0:o.dataset)?void 0:n.shopIds)?void 0:i.split(","))?r:[];D(e,a)}else document.location.href=e.getAttribute("href")}))),document.querySelectorAll(`.${s.editProductClass}`).forEach((e=>{e.addEventListener("click",(t=>{var o,n,i,r,a;if(t.preventDefault(),e.classList.contains(s.editProductClass)){const t=null!=(a=null==(r=null==(i=null==(n=null==(o=e.closest("tr"))?void 0:o.querySelector(s.shopListCell))?void 0:n.dataset)?void 0:i.shopIds)?void 0:r.split(","))?a:[];D(e,t)}else document.location.href=e.getAttribute("href")}))})),H(),new c})),window.product_index={}})();