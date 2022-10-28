(()=>{"use strict";var t={9567:t=>{t.exports=window.jQuery}},e={};function n(o){var i=e[o];if(void 0!==i)return i.exports;var s=e[o]={exports:{}};return t[o](s,s.exports,n),s.exports}n.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var o={};(()=>{n.r(o);
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
 */const t={deleteCategories:".js-delete-categories-bulk-action",deleteCategoriesModal:t=>`#${t}_grid_delete_categories_modal`,checkedCheckbox:".js-bulk-action-checkbox:checked",deleteCustomers:".js-delete-customers-bulk-action",deleteCustomerModal:t=>`#${t}_grid_delete_customers_modal`,submitDeleteCategories:".js-submit-delete-categories",submitDeleteCustomers:".js-submit-delete-customers",categoriesToDelete:"#delete_categories_categories_to_delete",customersToDelete:"#delete_customers_customers_to_delete",actionSelectAll:".js-bulk-action-select-all",bulkActionCheckbox:".js-bulk-action-checkbox",bulkActionBtn:".js-bulk-actions-btn",openTabsBtn:".js-bulk-action-btn.open_tabs",tableChoiceOptions:"table.table .js-choice-options",choiceOptions:".js-choice-options",modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitAction:".js-bulk-action-submit-btn",ajaxAction:".js-bulk-action-ajax-btn",gridSubmitAction:".js-grid-action-submit-btn"},e={categoryDeleteAction:".js-delete-category-row-action",customerDeleteAction:".js-delete-customer-row-action",linkRowAction:".js-link-row-action",linkRowActionClickableFirst:".js-link-row-action[data-clickable-row=1]:first",clickableTd:"td.clickable"},i=t=>`${t}-grid-confirm-modal`,s=t=>`#${t}_grid`,r=".js-grid-panel",a=".js-grid-header",c=".column-filters",l=".grid-search-button",d=".grid-reset-button",h="input:not(.js-bulk-action-select-all), select",u=t=>`#${t}_filter_form`,{$:m}=window;class f{constructor(t){this.id=t,this.$container=m(s(this.id))}getId(){return this.id}getContainer(){return this.$container}getHeaderContainer(){return this.$container.closest(r).find(a)}addExtension(t){t.extend(this)}}
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
const{$:b}=window;class p{extend(t){this.handleBulkActionCheckboxSelect(t),this.handleBulkActionSelectAllCheckbox(t)}handleBulkActionSelectAllCheckbox(e){e.getContainer().on("change",t.actionSelectAll,(n=>{const o=b(n.currentTarget).is(":checked");o?this.enableBulkActionsBtn(e):this.disableBulkActionsBtn(e),e.getContainer().find(t.bulkActionCheckbox).prop("checked",o)}))}handleBulkActionCheckboxSelect(e){e.getContainer().on("change",t.bulkActionCheckbox,(()=>{e.getContainer().find(t.checkedCheckbox).length>0?this.enableBulkActionsBtn(e):this.disableBulkActionsBtn(e)}))}enableBulkActionsBtn(e){e.getContainer().find(t.bulkActionBtn).prop("disabled",!1)}disableBulkActionsBtn(e){e.getContainer().find(t.bulkActionBtn).prop("disabled",!0)}}var v=n(9567),g=Object.defineProperty,w=Object.getOwnPropertySymbols,_=Object.prototype.hasOwnProperty,y=Object.prototype.propertyIsEnumerable,k=(t,e,n)=>e in t?g(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n,C=(t,e)=>{for(var n in e||(e={}))_.call(e,n)&&k(t,n,e[n]);if(w)for(var n of w(e))y.call(e,n)&&k(t,n,e[n]);return t};
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
class O{constructor(t){const e=C({id:"confirm-modal",closable:!1},t);this.buildModalContainer(e)}buildModalContainer(t){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=t.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),t.dialogStyle&&Object.keys(t.dialogStyle).forEach((e=>{this.dialog.style[e]=t.dialogStyle[e]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),t.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=t.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class E{constructor(t){const e=C({id:"confirm-modal",closable:!1,dialogStyle:{}},t);this.initContainer(e)}initContainer(t){this.modal||(this.modal=new O(t)),this.$modal=v(this.modal.container);const{id:e,closable:n}=t;this.$modal.modal({backdrop:!!n||"static",keyboard:void 0===n||n,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const n=document.querySelector(`#${e}`);n&&n.remove(),t.closeCallback&&t.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(t){this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=t}render(t){this.modal.message.innerHTML=t}show(){this.$modal.modal("show")}hide(){this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")}))}}
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
function B(t){return void 0===t}var A=Object.defineProperty,j=Object.getOwnPropertySymbols,M=Object.prototype.hasOwnProperty,T=Object.prototype.propertyIsEnumerable,x=(t,e,n)=>e in t?A(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n;
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
class L extends O{constructor(t){super(t)}buildModalContainer(t){super.buildModalContainer(t),this.message.classList.add("confirm-message"),this.message.innerHTML=t.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=t.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",t.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=t.confirmButtonLabel,this.footer.append(this.closeButton,...t.customButtons,this.confirmButton),this.content.append(this.footer)}}class S extends E{constructor(t,e,n){var o;let i;i=B(t.confirmCallback)?B(e)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:e:t.confirmCallback;super(((t,e)=>{for(var n in e||(e={}))M.call(e,n)&&x(t,n,e[n]);if(j)for(var n of j(e))T.call(e,n)&&x(t,n,e[n]);return t})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:t.confirmTitle,dialogStyle:{},confirmCallback:i,closeCallback:null!=(o=t.closeCallback)?o:n},t))}initContainer(t){this.modal=new L(t),this.modal.confirmButton.addEventListener("click",t.confirmCallback),super.initContainer(t)}}var $=function(){if("undefined"!=typeof Map)return Map;function t(t,e){var n=-1;return t.some((function(t,o){return t[0]===e&&(n=o,!0)})),n}return function(){function e(){this.__entries__=[]}return Object.defineProperty(e.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),e.prototype.get=function(e){var n=t(this.__entries__,e),o=this.__entries__[n];return o&&o[1]},e.prototype.set=function(e,n){var o=t(this.__entries__,e);~o?this.__entries__[o][1]=n:this.__entries__.push([e,n])},e.prototype.delete=function(e){var n=this.__entries__,o=t(n,e);~o&&n.splice(o,1)},e.prototype.has=function(e){return!!~t(this.__entries__,e)},e.prototype.clear=function(){this.__entries__.splice(0)},e.prototype.forEach=function(t,e){void 0===e&&(e=null);for(var n=0,o=this.__entries__;n<o.length;n++){var i=o[n];t.call(e,i[1],i[0])}},e}()}(),P="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,I=void 0!==n.g&&n.g.Math===Math?n.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),R="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(I):function(t){return setTimeout((function(){return t(Date.now())}),1e3/60)};var D=["top","right","bottom","left","width","height","size","weight"],F="undefined"!=typeof MutationObserver,H=function(){function t(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(t,e){var n=!1,o=!1,i=0;function s(){n&&(n=!1,t()),o&&a()}function r(){R(s)}function a(){var t=Date.now();if(n){if(t-i<2)return;o=!0}else n=!0,o=!1,setTimeout(r,e);i=t}return a}(this.refresh.bind(this),20)}return t.prototype.addObserver=function(t){~this.observers_.indexOf(t)||this.observers_.push(t),this.connected_||this.connect_()},t.prototype.removeObserver=function(t){var e=this.observers_,n=e.indexOf(t);~n&&e.splice(n,1),!e.length&&this.connected_&&this.disconnect_()},t.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},t.prototype.updateObservers_=function(){var t=this.observers_.filter((function(t){return t.gatherActive(),t.hasActive()}));return t.forEach((function(t){return t.broadcastActive()})),t.length>0},t.prototype.connect_=function(){P&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),F?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},t.prototype.disconnect_=function(){P&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},t.prototype.onTransitionEnd_=function(t){var e=t.propertyName,n=void 0===e?"":e;D.some((function(t){return!!~n.indexOf(t)}))&&this.refresh()},t.getInstance=function(){return this.instance_||(this.instance_=new t),this.instance_},t.instance_=null,t}(),z=function(t,e){for(var n=0,o=Object.keys(e);n<o.length;n++){var i=o[n];Object.defineProperty(t,i,{value:e[i],enumerable:!1,writable:!1,configurable:!0})}return t},W=function(t){return t&&t.ownerDocument&&t.ownerDocument.defaultView||I},q=K(0,0,0,0);function G(t){return parseFloat(t)||0}function N(t){for(var e=[],n=1;n<arguments.length;n++)e[n-1]=arguments[n];return e.reduce((function(e,n){return e+G(t["border-"+n+"-width"])}),0)}function V(t){var e=t.clientWidth,n=t.clientHeight;if(!e&&!n)return q;var o=W(t).getComputedStyle(t),i=function(t){for(var e={},n=0,o=["top","right","bottom","left"];n<o.length;n++){var i=o[n],s=t["padding-"+i];e[i]=G(s)}return e}(o),s=i.left+i.right,r=i.top+i.bottom,a=G(o.width),c=G(o.height);if("border-box"===o.boxSizing&&(Math.round(a+s)!==e&&(a-=N(o,"left","right")+s),Math.round(c+r)!==n&&(c-=N(o,"top","bottom")+r)),!function(t){return t===W(t).document.documentElement}(t)){var l=Math.round(a+s)-e,d=Math.round(c+r)-n;1!==Math.abs(l)&&(a-=l),1!==Math.abs(d)&&(c-=d)}return K(i.left,i.top,a,c)}var Q="undefined"!=typeof SVGGraphicsElement?function(t){return t instanceof W(t).SVGGraphicsElement}:function(t){return t instanceof W(t).SVGElement&&"function"==typeof t.getBBox};function J(t){return P?Q(t)?function(t){var e=t.getBBox();return K(0,0,e.width,e.height)}(t):V(t):q}function K(t,e,n,o){return{x:t,y:e,width:n,height:o}}var U=function(){function t(t){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=K(0,0,0,0),this.target=t}return t.prototype.isActive=function(){var t=J(this.target);return this.contentRect_=t,t.width!==this.broadcastWidth||t.height!==this.broadcastHeight},t.prototype.broadcastRect=function(){var t=this.contentRect_;return this.broadcastWidth=t.width,this.broadcastHeight=t.height,t},t}(),X=function(t,e){var n,o,i,s,r,a,c,l=(o=(n=e).x,i=n.y,s=n.width,r=n.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,c=Object.create(a.prototype),z(c,{x:o,y:i,width:s,height:r,top:i,right:o+s,bottom:r+i,left:o}),c);z(this,{target:t,contentRect:l})},Y=function(){function t(t,e,n){if(this.activeObservations_=[],this.observations_=new $,"function"!=typeof t)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=t,this.controller_=e,this.callbackCtx_=n}return t.prototype.observe=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof W(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)||(e.set(t,new U(t)),this.controller_.addObserver(this),this.controller_.refresh())}},t.prototype.unobserve=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof W(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)&&(e.delete(t),e.size||this.controller_.removeObserver(this))}},t.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},t.prototype.gatherActive=function(){var t=this;this.clearActive(),this.observations_.forEach((function(e){e.isActive()&&t.activeObservations_.push(e)}))},t.prototype.broadcastActive=function(){if(this.hasActive()){var t=this.callbackCtx_,e=this.activeObservations_.map((function(t){return new X(t.target,t.broadcastRect())}));this.callback_.call(t,e,t),this.clearActive()}},t.prototype.clearActive=function(){this.activeObservations_.splice(0)},t.prototype.hasActive=function(){return this.activeObservations_.length>0},t}(),Z="undefined"!=typeof WeakMap?new WeakMap:new $,tt=function t(e){if(!(this instanceof t))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var n=H.getInstance(),o=new Y(e,n,this);Z.set(this,o)};["observe","unobserve","disconnect"].forEach((function(t){tt.prototype[t]=function(){var e;return(e=Z.get(this))[t].apply(e,arguments)}}));void 0!==I.ResizeObserver&&I.ResizeObserver;const et=class extends Event{constructor(t,e={}){super(et.parentWindowEvent),this.eventName=t,this.eventParameters=e}get name(){return this.eventName}get parameters(){return this.eventParameters}};et.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const nt=S,{$:ot}=window;class it{extend(e){e.getContainer().on("click",t.submitAction,(t=>{this.submit(t,e)}))}submit(t,e){const n=ot(t.currentTarget),o=n.data("confirm-message"),i=n.data("confirmTitle");void 0!==o&&o.length>0?void 0!==i?this.showConfirmModal(n,e,o,i):window.confirm(o)&&this.postForm(n,e):this.postForm(n,e)}showConfirmModal(t,e,n,o){const s=t.data("confirmButtonLabel"),r=t.data("closeButtonLabel"),a=t.data("confirmButtonClass");new nt({id:i(e.getId()),confirmTitle:o,confirmMessage:n,confirmButtonLabel:s,closeButtonLabel:r,confirmButtonClass:a},(()=>this.postForm(t,e))).show()}postForm(t,e){const n=ot(u(e.getId()));n.attr("action",t.data("form-url")),n.attr("method",t.data("form-method")),n.submit()}}
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
const{$:st}=window;class rt{extend(t){this.initRowLinks(t),this.initConfirmableActions(t)}initConfirmableActions(t){t.getContainer().on("click",e.linkRowAction,(t=>{const e=st(t.currentTarget).data("confirm-message");e.length&&!window.confirm(e)&&t.preventDefault()}))}initRowLinks(t){st("tr",t.getContainer()).each((function(){const t=st(this);st(e.linkRowActionClickableFirst,t).each((function(){const n=st(this),o=n.closest("td"),i=st(e.clickableTd,t).not(o);let s=!1;i.addClass("cursor-pointer").mousedown((()=>{st(window).mousemove((()=>{s=!0,st(window).unbind("mousemove")}))})),i.mouseup((()=>{const t=s;if(s=!1,st(window).unbind("mousemove"),!t){const t=n.data("confirm-message");(!t.length||window.confirm(t)&&n.attr("href"))&&(document.location.href=n.attr("href"))}}))}))}))}}
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
const{$:at}=window;class ct{extend(t){t.getContainer().on("click",".js-submit-row-action",(e=>{e.preventDefault();const n=at(e.currentTarget),o=n.data("confirmMessage"),i=n.data("title"),s=n.data("method");if(i)this.showConfirmModal(n,t,o,i,s);else{if(o.length&&!window.confirm(o))return;this.postForm(n,s)}}))}postForm(t,e){const n=["GET","POST"].includes(e),o=at("<form>",{action:t.data("url"),method:n?e:"POST"}).appendTo("body");n||o.append(at("<input>",{type:"_hidden",name:"_method",value:e})),o.submit()}showConfirmModal(t,e,n,o,s){const r=t.data("confirmButtonLabel"),a=t.data("closeButtonLabel"),c=t.data("confirmButtonClass");new S({id:i(e.getId()),confirmTitle:o,confirmMessage:n,confirmButtonLabel:r,closeButtonLabel:a,confirmButtonClass:c},(()=>this.postForm(t,s))).show()}}
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
class lt{extend(t){const e=t.getContainer().find(c);e.find(l).prop("disabled",!0),e.find(h).on("input dp.change",(()=>{e.find(l).prop("disabled",!1),e.find(d).prop("hidden",!1)}))}}
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
const{$:dt}=window;dt((()=>{const t=new f("backup");t.addExtension(new p),t.addExtension(new it),t.addExtension(new rt),t.addExtension(new ct),t.addExtension(new lt)}))})(),window.backup=o})();