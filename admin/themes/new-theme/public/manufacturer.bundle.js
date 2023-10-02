(()=>{"use strict";var t={9567:t=>{t.exports=window.jQuery}},e={};function n(o){var i=e[o];if(void 0!==i)return i.exports;var r=e[o]={exports:{}};return t[o](r,r.exports,n),r.exports}n.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var o={};(()=>{n.r(o);
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
 */const t=".js-form-submit-btn",{$:e}=window;class i{constructor(){e(document).on("click",t,(t=>{t.preventDefault();const n=e(t.target);if(n.data("form-confirm-message")&&!1===window.confirm(n.data("form-confirm-message")))return;let o="POST",i=null;if(n.data("method")){const t=n.data("method"),r=["GET","POST"].includes(t);o=r?t:"POST",r||(i=e("<input>",{type:"_hidden",name:"_method",value:t}))}const r=e("<form>",{action:n.data("form-submit-url"),method:o});i&&r.append(i),n.data("form-csrf-token")&&r.append(e("<input>",{type:"_hidden",name:"_csrf_token",value:n.data("form-csrf-token")})),r.appendTo("body").submit()}))}}
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
 */const r={deleteCategories:".js-delete-categories-bulk-action",deleteCategoriesModal:t=>`#${t}_grid_delete_categories_modal`,checkedCheckbox:".js-bulk-action-checkbox:checked",deleteCustomers:".js-delete-customers-bulk-action",deleteCustomerModal:t=>`#${t}_grid_delete_customers_modal`,submitDeleteCategories:".js-submit-delete-categories",submitDeleteCustomers:".js-submit-delete-customers",categoriesToDelete:"#delete_categories_categories_to_delete",customersToDelete:"#delete_customers_customers_to_delete",actionSelectAll:".js-bulk-action-select-all",bulkActionCheckbox:".js-bulk-action-checkbox",bulkActionBtn:".js-bulk-actions-btn",openTabsBtn:".js-bulk-action-btn.open_tabs",tableChoiceOptions:"table.table .js-choice-options",choiceOptions:".js-choice-options",modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitAction:".js-bulk-action-submit-btn",ajaxAction:".js-bulk-action-ajax-btn",gridSubmitAction:".js-grid-action-submit-btn"},s={categoryDeleteAction:".js-delete-category-row-action",customerDeleteAction:".js-delete-customer-row-action",linkRowAction:".js-link-row-action",linkRowActionClickableFirst:".js-link-row-action[data-clickable-row=1]:first",clickableTd:"td.clickable"},a={showQuery:".js-common_show_query-grid-action",exportQuery:".js-common_export_sql_manager-grid-action",showModalForm:t=>`#${t}_common_show_query_modal_form`,showModalGrid:t=>`#${t}_grid_common_show_query_modal`,modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitModalFormBtn:".js-submit-modal-form-btn",bulkInputsBlock:t=>`#${t}`,tokenInput:t=>`input[name="${t}[_token]"]`,ajaxBulkActionConfirmModal:(t,e)=>`${t}-ajax-${e}-confirm-modal`,ajaxBulkActionProgressModal:(t,e)=>`${t}-ajax-${e}-progress-modal`},c=t=>`${t}-grid-confirm-modal`,l=".js-grid-table",d=t=>`#${t}_grid`,h=".js-grid-panel",u=".js-grid-header",m=".ps-togglable-row",f="table.table",b=".header-toolbar",p=".breadcrumb-item",g=".js-reset-search",w=".column-filters",v=".grid-search-button",_=".grid-reset-button",y="input:not(.js-bulk-action-select-all), select",k=".js-common_refresh_list-grid-action",C=t=>`#${t}_filter_form`,E=".btn-sql-submit",{$:O}=window;class B{constructor(t){this.id=t,this.$container=O(d(this.id))}getId(){return this.id}getContainer(){return this.$container}getHeaderContainer(){return this.$container.closest(h).find(u)}addExtension(t){t.extend(this)}}
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
const{$:x}=window;const T=class{constructor(t){var e;this.selector=".ps-sortable-column",this.idTable=null!=(e=t.attr("id"))?e:"",this.columns=t.find(this.selector)}attach(){this.columns.on("click",(t=>{const e=x(t.delegateTarget);this.sortByColumn(e,this.getToggledSortDirection(e))}))}sortBy(t,e){if(!this.columns.is(`[data-sort-col-name="${t}"]`))throw new Error(`Cannot sort by "${t}": invalid column`);this.sortByColumn(this.columns,e)}sortByColumn(t,e){window.location.href=this.getUrl(t.data("sortColName"),"desc"===e?"desc":"asc",t.data("sortPrefix"))}getToggledSortDirection(t){return"asc"===t.data("sortDirection")?"desc":"asc"}getUrl(t,e,n){const o=new URL(window.location.href),i=o.searchParams;return n?(i.set(`${n}[orderBy]`,t),i.set(`${n}[sortOrder]`,e)):(i.set("orderBy",t),i.set("sortOrder",e)),o.hash=this.idTable,o.toString()}};
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
class j{extend(t){const e=t.getContainer().find(f);new T(e).attach()}}
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
const{$:A}=window,M=function(t,e){A.post(t).then((()=>window.location.assign(e)))},{$:S}=window;class L{extend(t){t.getContainer().on("click",g,(t=>{M(S(t.currentTarget).data("url"),S(t.currentTarget).data("redirect"))}))}}
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
class ${extend(t){t.getHeaderContainer().on("click",k,(()=>{window.location.reload()}))}}
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
const{$:F}=window;class P{extend(t){t.getContainer().find(f).find(m).on("click",(t=>{t.preventDefault(),this.toggleValue(F(t.delegateTarget))}))}toggleValue(t){const e=t.data("toggleUrl");this.submitAsForm(e)}submitAsForm(t){F("<form>",{action:t,method:"POST"}).appendTo("body").submit()}}var I=n(9567),D=Object.defineProperty,R=Object.getOwnPropertySymbols,q=Object.prototype.hasOwnProperty,H=Object.prototype.propertyIsEnumerable,G=(t,e,n)=>e in t?D(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n,z=(t,e)=>{for(var n in e||(e={}))q.call(e,n)&&G(t,n,e[n]);if(R)for(var n of R(e))H.call(e,n)&&G(t,n,e[n]);return t};
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
class W{constructor(t){const e=z({id:"confirm-modal",closable:!1},t);this.buildModalContainer(e)}buildModalContainer(t){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=t.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),t.dialogStyle&&Object.keys(t.dialogStyle).forEach((e=>{this.dialog.style[e]=t.dialogStyle[e]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),t.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=t.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class N{constructor(t){const e=z({id:"confirm-modal",closable:!1,dialogStyle:{}},t);this.initContainer(e)}initContainer(t){this.modal||(this.modal=new W(t)),this.$modal=I(this.modal.container);const{id:e,closable:n}=t;this.$modal.modal({backdrop:!!n||"static",keyboard:void 0===n||n,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const n=document.querySelector(`#${e}`);n&&n.remove(),t.closeCallback&&t.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(t){return this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=t,this}render(t){return this.modal.message.innerHTML=t,this}show(){return this.$modal.modal("show"),this}hide(){return this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")})),this}}
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
function Q(t){return void 0===t}var V=Object.defineProperty,U=Object.getOwnPropertySymbols,J=Object.prototype.hasOwnProperty,K=Object.prototype.propertyIsEnumerable,X=(t,e,n)=>e in t?V(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n;
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
class Y extends W{constructor(t){super(t)}buildModalContainer(t){super.buildModalContainer(t),this.message.classList.add("confirm-message"),this.message.innerHTML=t.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=t.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",t.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=t.confirmButtonLabel,this.footer.append(this.closeButton,...t.customButtons,this.confirmButton),this.content.append(this.footer)}}class Z extends N{constructor(t,e,n){var o;let i;i=Q(t.confirmCallback)?Q(e)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:e:t.confirmCallback;super(((t,e)=>{for(var n in e||(e={}))J.call(e,n)&&X(t,n,e[n]);if(U)for(var n of U(e))K.call(e,n)&&X(t,n,e[n]);return t})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:t.confirmTitle,dialogStyle:{},confirmCallback:i,closeCallback:null!=(o=t.closeCallback)?o:n},t))}initContainer(t){this.modal=new Y(t),this.modal.confirmButton.addEventListener("click",t.confirmCallback),super.initContainer(t)}}var tt=function(){if("undefined"!=typeof Map)return Map;function t(t,e){var n=-1;return t.some((function(t,o){return t[0]===e&&(n=o,!0)})),n}return function(){function e(){this.__entries__=[]}return Object.defineProperty(e.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),e.prototype.get=function(e){var n=t(this.__entries__,e),o=this.__entries__[n];return o&&o[1]},e.prototype.set=function(e,n){var o=t(this.__entries__,e);~o?this.__entries__[o][1]=n:this.__entries__.push([e,n])},e.prototype.delete=function(e){var n=this.__entries__,o=t(n,e);~o&&n.splice(o,1)},e.prototype.has=function(e){return!!~t(this.__entries__,e)},e.prototype.clear=function(){this.__entries__.splice(0)},e.prototype.forEach=function(t,e){void 0===e&&(e=null);for(var n=0,o=this.__entries__;n<o.length;n++){var i=o[n];t.call(e,i[1],i[0])}},e}()}(),et="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,nt=void 0!==n.g&&n.g.Math===Math?n.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),ot="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(nt):function(t){return setTimeout((function(){return t(Date.now())}),1e3/60)};var it=["top","right","bottom","left","width","height","size","weight"],rt="undefined"!=typeof MutationObserver,st=function(){function t(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(t,e){var n=!1,o=!1,i=0;function r(){n&&(n=!1,t()),o&&a()}function s(){ot(r)}function a(){var t=Date.now();if(n){if(t-i<2)return;o=!0}else n=!0,o=!1,setTimeout(s,e);i=t}return a}(this.refresh.bind(this),20)}return t.prototype.addObserver=function(t){~this.observers_.indexOf(t)||this.observers_.push(t),this.connected_||this.connect_()},t.prototype.removeObserver=function(t){var e=this.observers_,n=e.indexOf(t);~n&&e.splice(n,1),!e.length&&this.connected_&&this.disconnect_()},t.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},t.prototype.updateObservers_=function(){var t=this.observers_.filter((function(t){return t.gatherActive(),t.hasActive()}));return t.forEach((function(t){return t.broadcastActive()})),t.length>0},t.prototype.connect_=function(){et&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),rt?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},t.prototype.disconnect_=function(){et&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},t.prototype.onTransitionEnd_=function(t){var e=t.propertyName,n=void 0===e?"":e;it.some((function(t){return!!~n.indexOf(t)}))&&this.refresh()},t.getInstance=function(){return this.instance_||(this.instance_=new t),this.instance_},t.instance_=null,t}(),at=function(t,e){for(var n=0,o=Object.keys(e);n<o.length;n++){var i=o[n];Object.defineProperty(t,i,{value:e[i],enumerable:!1,writable:!1,configurable:!0})}return t},ct=function(t){return t&&t.ownerDocument&&t.ownerDocument.defaultView||nt},lt=bt(0,0,0,0);function dt(t){return parseFloat(t)||0}function ht(t){for(var e=[],n=1;n<arguments.length;n++)e[n-1]=arguments[n];return e.reduce((function(e,n){return e+dt(t["border-"+n+"-width"])}),0)}function ut(t){var e=t.clientWidth,n=t.clientHeight;if(!e&&!n)return lt;var o=ct(t).getComputedStyle(t),i=function(t){for(var e={},n=0,o=["top","right","bottom","left"];n<o.length;n++){var i=o[n],r=t["padding-"+i];e[i]=dt(r)}return e}(o),r=i.left+i.right,s=i.top+i.bottom,a=dt(o.width),c=dt(o.height);if("border-box"===o.boxSizing&&(Math.round(a+r)!==e&&(a-=ht(o,"left","right")+r),Math.round(c+s)!==n&&(c-=ht(o,"top","bottom")+s)),!function(t){return t===ct(t).document.documentElement}(t)){var l=Math.round(a+r)-e,d=Math.round(c+s)-n;1!==Math.abs(l)&&(a-=l),1!==Math.abs(d)&&(c-=d)}return bt(i.left,i.top,a,c)}var mt="undefined"!=typeof SVGGraphicsElement?function(t){return t instanceof ct(t).SVGGraphicsElement}:function(t){return t instanceof ct(t).SVGElement&&"function"==typeof t.getBBox};function ft(t){return et?mt(t)?function(t){var e=t.getBBox();return bt(0,0,e.width,e.height)}(t):ut(t):lt}function bt(t,e,n,o){return{x:t,y:e,width:n,height:o}}var pt=function(){function t(t){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=bt(0,0,0,0),this.target=t}return t.prototype.isActive=function(){var t=ft(this.target);return this.contentRect_=t,t.width!==this.broadcastWidth||t.height!==this.broadcastHeight},t.prototype.broadcastRect=function(){var t=this.contentRect_;return this.broadcastWidth=t.width,this.broadcastHeight=t.height,t},t}(),gt=function(t,e){var n,o,i,r,s,a,c,l=(o=(n=e).x,i=n.y,r=n.width,s=n.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,c=Object.create(a.prototype),at(c,{x:o,y:i,width:r,height:s,top:i,right:o+r,bottom:s+i,left:o}),c);at(this,{target:t,contentRect:l})},wt=function(){function t(t,e,n){if(this.activeObservations_=[],this.observations_=new tt,"function"!=typeof t)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=t,this.controller_=e,this.callbackCtx_=n}return t.prototype.observe=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof ct(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)||(e.set(t,new pt(t)),this.controller_.addObserver(this),this.controller_.refresh())}},t.prototype.unobserve=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof ct(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)&&(e.delete(t),e.size||this.controller_.removeObserver(this))}},t.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},t.prototype.gatherActive=function(){var t=this;this.clearActive(),this.observations_.forEach((function(e){e.isActive()&&t.activeObservations_.push(e)}))},t.prototype.broadcastActive=function(){if(this.hasActive()){var t=this.callbackCtx_,e=this.activeObservations_.map((function(t){return new gt(t.target,t.broadcastRect())}));this.callback_.call(t,e,t),this.clearActive()}},t.prototype.clearActive=function(){this.activeObservations_.splice(0)},t.prototype.hasActive=function(){return this.activeObservations_.length>0},t}(),vt="undefined"!=typeof WeakMap?new WeakMap:new tt,_t=function t(e){if(!(this instanceof t))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var n=st.getInstance(),o=new wt(e,n,this);vt.set(this,o)};["observe","unobserve","disconnect"].forEach((function(t){_t.prototype[t]=function(){var e;return(e=vt.get(this))[t].apply(e,arguments)}}));void 0!==nt.ResizeObserver&&nt.ResizeObserver;const yt=class extends Event{constructor(t,e={}){super(yt.parentWindowEvent),this.eventName=t,this.eventParameters=e}get name(){return this.eventName}get parameters(){return this.eventParameters}};yt.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const kt=Z,{$:Ct}=window;class Et{extend(t){t.getContainer().on("click",".js-submit-row-action",(e=>{e.preventDefault();const n=Ct(e.currentTarget),o=n.data("confirmMessage"),i=n.data("title"),r=n.data("method");if(i)this.showConfirmModal(n,t,o,i,r);else{if(o.length&&!window.confirm(o))return;this.postForm(n,r)}}))}postForm(t,e){const n=["GET","POST"].includes(e),o=Ct("<form>",{action:t.data("url"),method:n?e:"POST"}).appendTo("body");n||o.append(Ct("<input>",{type:"hidden",name:"_method",value:e})),o.submit()}showConfirmModal(t,e,n,o,i){const r=t.data("confirmButtonLabel"),s=t.data("closeButtonLabel"),a=t.data("confirmButtonClass");new Z({id:c(e.getId()),confirmTitle:o,confirmMessage:n,confirmButtonLabel:r,closeButtonLabel:s,confirmButtonClass:a},(()=>this.postForm(t,i))).show()}}
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
const{$:Ot}=window;class Bt{extend(t){t.getContainer().on("click",r.submitAction,(e=>{this.submit(e,t)}))}submit(t,e){const n=Ot(t.currentTarget),o=n.data("confirm-message"),i=n.data("confirmTitle");void 0!==o&&o.length>0?void 0!==i?this.showConfirmModal(n,e,o,i):window.confirm(o)&&this.postForm(n,e):this.postForm(n,e)}showConfirmModal(t,e,n,o){const i=t.data("confirmButtonLabel"),r=t.data("closeButtonLabel"),s=t.data("confirmButtonClass");new kt({id:c(e.getId()),confirmTitle:o,confirmMessage:n,confirmButtonLabel:i,closeButtonLabel:r,confirmButtonClass:s},(()=>this.postForm(t,e))).show()}postForm(t,e){const n=Ot(C(e.getId()));n.attr("action",t.data("form-url")),n.attr("method",t.data("form-method")),n.submit()}}
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
const{$:xt}=window;class Tt{extend(t){this.handleBulkActionCheckboxSelect(t),this.handleBulkActionSelectAllCheckbox(t)}handleBulkActionSelectAllCheckbox(t){t.getContainer().on("change",r.actionSelectAll,(e=>{const n=xt(e.currentTarget).is(":checked");n?this.enableBulkActionsBtn(t):this.disableBulkActionsBtn(t),t.getContainer().find(r.bulkActionCheckbox).prop("checked",n)}))}handleBulkActionCheckboxSelect(t){t.getContainer().on("change",r.bulkActionCheckbox,(()=>{t.getContainer().find(r.checkedCheckbox).length>0?this.enableBulkActionsBtn(t):this.disableBulkActionsBtn(t)}))}enableBulkActionsBtn(t){t.getContainer().find(r.bulkActionBtn).prop("disabled",!1)}disableBulkActionsBtn(t){t.getContainer().find(r.bulkActionBtn).prop("disabled",!0)}}
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
const{$:jt}=window;class At{extend(t){t.getHeaderContainer().on("click",a.showQuery,(()=>this.onShowSqlQueryClick(t))),t.getHeaderContainer().on("click",a.exportQuery,(()=>this.onExportSqlManagerClick(t)))}onShowSqlQueryClick(t){const e=jt(a.showModalForm(t.getId()));this.fillExportForm(e,t);const n=jt(a.showModalGrid(t.getId()));n.modal("show"),n.on("click",E,(()=>e.submit()))}onExportSqlManagerClick(t){const e=jt(a.showModalForm(t.getId()));this.fillExportForm(e,t),e.submit()}fillExportForm(t,e){const n=e.getContainer().find(l).data("query");t.find('textarea[name="sql"]').val(n),t.find('input[name="name"]').val(this.getNameFromBreadcrumb())}getNameFromBreadcrumb(){const t=jt(b).find(p);let e="";return t.each(((t,n)=>{const o=jt(n),i=o.find("a").length>0?o.find("a").text():o.text();e.length>0&&(e=e.concat(" > ")),e=e.concat(i)})),e}}
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
class Mt{extend(t){const e=t.getContainer().find(w);e.find(v).prop("disabled",!0),e.find(y).on("input dp.change",(()=>{e.find(v).prop("disabled",!1),e.find(_).prop("hidden",!1)}))}}
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
const{$:St}=window;class Lt{constructor(t){this.onClick=t}extend(t){this.initRowLinks(t),this.initConfirmableActions(t)}initConfirmableActions(t){t.getContainer().on("click",s.linkRowAction,(t=>{const e=St(t.currentTarget).data("confirm-message");e.length&&!window.confirm(e)&&t.preventDefault()}))}initRowLinks(t){const e=this.onClick;St("tr",t.getContainer()).each((function(){const t=St(this);St(s.linkRowActionClickableFirst,t).each((function(){const n=St(this),o=n.closest("td"),i=St(s.clickableTd,t).not(o);let r=!1;i.addClass("cursor-pointer").mousedown((()=>{St(window).mousemove((()=>{r=!0,St(window).unbind("mousemove")}))})),i.mouseup((()=>{const t=r;if(r=!1,St(window).unbind("mousemove"),!t){const t=n.data("confirm-message");(!t.length||window.confirm(t)&&n.attr("href"))&&(Q(e)||Q(n.get(0))?document.location.href=n.attr("href"):e(n.get(0)))}}))}))}))}}
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
const{$:$t}=window;$t((()=>{["manufacturer","manufacturer_address"].forEach((t=>{const e=new B(t);e.addExtension(new At),e.addExtension(new $),e.addExtension(new j),e.addExtension(new L),e.addExtension(new P),e.addExtension(new Et),e.addExtension(new Bt),e.addExtension(new Tt),e.addExtension(new Mt),e.addExtension(new Lt)})),window.prestashop.component.initComponents(["TranslatableInput","TranslatableField","TinyMCEEditor"]),new window.prestashop.component.TaggableField({tokenFieldSelector:"input.js-taggable-field",options:{createTokensOnBlur:!0}}),new window.prestashop.component.ChoiceTree("#manufacturer_shop_association").enableAutoCheckChildren(),new i}))})(),window.manufacturer=o})();