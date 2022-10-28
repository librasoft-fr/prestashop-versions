(()=>{"use strict";var e={7721:(e,t,s)=>{s.r(t);
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
 */const o={deleteCategories:".js-delete-categories-bulk-action",deleteCategoriesModal:e=>`#${e}_grid_delete_categories_modal`,checkedCheckbox:".js-bulk-action-checkbox:checked",deleteCustomers:".js-delete-customers-bulk-action",deleteCustomerModal:e=>`#${e}_grid_delete_customers_modal`,submitDeleteCategories:".js-submit-delete-categories",submitDeleteCustomers:".js-submit-delete-customers",categoriesToDelete:"#delete_categories_categories_to_delete",customersToDelete:"#delete_customers_customers_to_delete",actionSelectAll:".js-bulk-action-select-all",bulkActionCheckbox:".js-bulk-action-checkbox",bulkActionBtn:".js-bulk-actions-btn",openTabsBtn:".js-bulk-action-btn.open_tabs",tableChoiceOptions:"table.table .js-choice-options",choiceOptions:".js-choice-options",modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitAction:".js-bulk-action-submit-btn",ajaxAction:".js-bulk-action-ajax-btn",gridSubmitAction:".js-grid-action-submit-btn"},n={categoryDeleteAction:".js-delete-category-row-action",customerDeleteAction:".js-delete-customer-row-action",linkRowAction:".js-link-row-action",linkRowActionClickableFirst:".js-link-row-action[data-clickable-row=1]:first",clickableTd:"td.clickable"},r={showQuery:".js-common_show_query-grid-action",exportQuery:".js-common_export_sql_manager-grid-action",showModalForm:e=>`#${e}_common_show_query_modal_form`,showModalGrid:e=>`#${e}_grid_common_show_query_modal`,modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitModalFormBtn:".js-submit-modal-form-btn",bulkInputsBlock:e=>`#${e}`,tokenInput:e=>`input[name="${e}[_token]"]`,ajaxBulkActionConfirmModal:(e,t)=>`${e}-ajax-${t}-confirm-modal`,ajaxBulkActionProgressModal:(e,t)=>`${e}-ajax-${t}-progress-modal`},i=e=>`${e}-grid-confirm-modal`,a=".js-grid-table",d=e=>`#${e}_grid`,c=".js-grid-panel",l=".js-grid-header",u=".js-dropdown-item",h="table.table",m=".header-toolbar",p=".breadcrumb-item",f=".js-reset-search",b=".js-expand",_=".js-collapse",v=".column-filters",g=".grid-search-button",k=".grid-reset-button",w="input:not(.js-bulk-action-select-all), select",x=".preview-toggle",y=".preview-row",I=".grid-table tbody",T="tr:not(.preview-row)",E=".js-common_refresh_list-grid-action",O=e=>`#${e}_filter_form`,C=".btn-sql-submit",{$:S}=window;class q{constructor(e){this.id=e,this.$container=S(d(this.id))}getId(){return this.id}getContainer(){return this.$container}getHeaderContainer(){return this.$container.closest(c).find(l)}addExtension(e){e.extend(this)}}
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
const{$:P}=window,j=function(e,t){P.post(e).then((()=>window.location.assign(t)))},{$:B}=window;class A{extend(e){e.getContainer().on("click",f,(e=>{j(B(e.currentTarget).data("url"),B(e.currentTarget).data("redirect"))}))}}
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
class M{extend(e){e.getHeaderContainer().on("click",E,(()=>{window.location.reload()}))}}
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
const{$}=window;class L{extend(e){e.getHeaderContainer().on("click",r.showQuery,(()=>this.onShowSqlQueryClick(e))),e.getHeaderContainer().on("click",r.exportQuery,(()=>this.onExportSqlManagerClick(e)))}onShowSqlQueryClick(e){const t=$(r.showModalForm(e.getId()));this.fillExportForm(t,e);const s=$(r.showModalGrid(e.getId()));s.modal("show"),s.on("click",C,(()=>t.submit()))}onExportSqlManagerClick(e){const t=$(r.showModalForm(e.getId()));this.fillExportForm(t,e),t.submit()}fillExportForm(e,t){const s=t.getContainer().find(a).data("query");e.find('textarea[name="sql"]').val(s),e.find('input[name="name"]').val(this.getNameFromBreadcrumb())}getNameFromBreadcrumb(){const e=$(m).find(p);let t="";return e.each(((e,s)=>{const o=$(s),n=o.find("a").length>0?o.find("a").text():o.text();t.length>0&&(t=t.concat(" > ")),t=t.concat(n)})),t}}
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
const{$:R}=window;const G=class{constructor(e){this.selector=".ps-sortable-column",this.columns=R(e).find(this.selector)}attach(){this.columns.on("click",(e=>{const t=R(e.delegateTarget);this.sortByColumn(t,this.getToggledSortDirection(t))}))}sortBy(e,t){if(!this.columns.is(`[data-sort-col-name="${e}"]`))throw new Error(`Cannot sort by "${e}": invalid column`);this.sortByColumn(this.columns,t)}sortByColumn(e,t){window.location.href=this.getUrl(e.data("sortColName"),"desc"===t?"desc":"asc",e.data("sortPrefix"))}getToggledSortDirection(e){return"asc"===e.data("sortDirection")?"desc":"asc"}getUrl(e,t,s){const o=new URL(window.location.href),n=o.searchParams;return s?(n.set(`${s}[orderBy]`,e),n.set(`${s}[sortOrder]`,t)):(n.set("orderBy",e),n.set("sortOrder",t)),o.toString()}};
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
class D{extend(e){const t=e.getContainer().find(h);new G(t).attach()}}
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
const{$:F}=window;class H{extend(e){this.initRowLinks(e),this.initConfirmableActions(e)}initConfirmableActions(e){e.getContainer().on("click",n.linkRowAction,(e=>{const t=F(e.currentTarget).data("confirm-message");t.length&&!window.confirm(t)&&e.preventDefault()}))}initRowLinks(e){F("tr",e.getContainer()).each((function(){const e=F(this);F(n.linkRowActionClickableFirst,e).each((function(){const t=F(this),s=t.closest("td"),o=F(n.clickableTd,e).not(s);let r=!1;o.addClass("cursor-pointer").mousedown((()=>{F(window).mousemove((()=>{r=!0,F(window).unbind("mousemove")}))})),o.mouseup((()=>{const e=r;if(r=!1,F(window).unbind("mousemove"),!e){const e=t.data("confirm-message");(!e.length||window.confirm(e)&&t.attr("href"))&&(document.location.href=t.attr("href"))}}))}))}))}}
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
const{$:z}=window;class Q{extend(e){e.getHeaderContainer().on("click",o.gridSubmitAction,(t=>{this.handleSubmit(t,e)}))}handleSubmit(e,t){const s=z(e.currentTarget),o=s.data("confirm-message");if(void 0!==o&&o.length>0&&!window.confirm(o))return;const n=z(O(t.getId()));n.attr("action",s.data("url")),n.attr("method",s.data("method")),n.find(r.tokenInput(t.getId())).val(s.data("csrf")),n.submit()}}var N=s(9567),U=Object.defineProperty,W=Object.getOwnPropertySymbols,V=Object.prototype.hasOwnProperty,J=Object.prototype.propertyIsEnumerable,K=(e,t,s)=>t in e?U(e,t,{enumerable:!0,configurable:!0,writable:!0,value:s}):e[t]=s,X=(e,t)=>{for(var s in t||(t={}))V.call(t,s)&&K(e,s,t[s]);if(W)for(var s of W(t))J.call(t,s)&&K(e,s,t[s]);return e};
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
class Y{constructor(e){const t=X({id:"confirm-modal",closable:!1},e);this.buildModalContainer(t)}buildModalContainer(e){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=e.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),e.dialogStyle&&Object.keys(e.dialogStyle).forEach((t=>{this.dialog.style[t]=e.dialogStyle[t]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),e.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=e.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class Z{constructor(e){const t=X({id:"confirm-modal",closable:!1,dialogStyle:{}},e);this.initContainer(t)}initContainer(e){this.modal||(this.modal=new Y(e)),this.$modal=N(this.modal.container);const{id:t,closable:s}=e;this.$modal.modal({backdrop:!!s||"static",keyboard:void 0===s||s,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const s=document.querySelector(`#${t}`);s&&s.remove(),e.closeCallback&&e.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(e){this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=e}render(e){this.modal.message.innerHTML=e}show(){this.$modal.modal("show")}hide(){this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")}))}}
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
function ee(e){return void 0===e}var te=Object.defineProperty,se=Object.getOwnPropertySymbols,oe=Object.prototype.hasOwnProperty,ne=Object.prototype.propertyIsEnumerable,re=(e,t,s)=>t in e?te(e,t,{enumerable:!0,configurable:!0,writable:!0,value:s}):e[t]=s;
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
class ie extends Y{constructor(e){super(e)}buildModalContainer(e){super.buildModalContainer(e),this.message.classList.add("confirm-message"),this.message.innerHTML=e.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=e.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",e.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=e.confirmButtonLabel,this.footer.append(this.closeButton,...e.customButtons,this.confirmButton),this.content.append(this.footer)}}class ae extends Z{constructor(e,t,s){var o;let n;n=ee(e.confirmCallback)?ee(t)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:t:e.confirmCallback;super(((e,t)=>{for(var s in t||(t={}))oe.call(t,s)&&re(e,s,t[s]);if(se)for(var s of se(t))ne.call(t,s)&&re(e,s,t[s]);return e})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:e.confirmTitle,dialogStyle:{},confirmCallback:n,closeCallback:null!=(o=e.closeCallback)?o:s},e))}initContainer(e){this.modal=new ie(e),this.modal.confirmButton.addEventListener("click",e.confirmCallback),super.initContainer(e)}}var de=function(){if("undefined"!=typeof Map)return Map;function e(e,t){var s=-1;return e.some((function(e,o){return e[0]===t&&(s=o,!0)})),s}return function(){function t(){this.__entries__=[]}return Object.defineProperty(t.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),t.prototype.get=function(t){var s=e(this.__entries__,t),o=this.__entries__[s];return o&&o[1]},t.prototype.set=function(t,s){var o=e(this.__entries__,t);~o?this.__entries__[o][1]=s:this.__entries__.push([t,s])},t.prototype.delete=function(t){var s=this.__entries__,o=e(s,t);~o&&s.splice(o,1)},t.prototype.has=function(t){return!!~e(this.__entries__,t)},t.prototype.clear=function(){this.__entries__.splice(0)},t.prototype.forEach=function(e,t){void 0===t&&(t=null);for(var s=0,o=this.__entries__;s<o.length;s++){var n=o[s];e.call(t,n[1],n[0])}},t}()}(),ce="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,le=void 0!==s.g&&s.g.Math===Math?s.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),ue="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(le):function(e){return setTimeout((function(){return e(Date.now())}),1e3/60)};var he=["top","right","bottom","left","width","height","size","weight"],me="undefined"!=typeof MutationObserver,pe=function(){function e(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(e,t){var s=!1,o=!1,n=0;function r(){s&&(s=!1,e()),o&&a()}function i(){ue(r)}function a(){var e=Date.now();if(s){if(e-n<2)return;o=!0}else s=!0,o=!1,setTimeout(i,t);n=e}return a}(this.refresh.bind(this),20)}return e.prototype.addObserver=function(e){~this.observers_.indexOf(e)||this.observers_.push(e),this.connected_||this.connect_()},e.prototype.removeObserver=function(e){var t=this.observers_,s=t.indexOf(e);~s&&t.splice(s,1),!t.length&&this.connected_&&this.disconnect_()},e.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},e.prototype.updateObservers_=function(){var e=this.observers_.filter((function(e){return e.gatherActive(),e.hasActive()}));return e.forEach((function(e){return e.broadcastActive()})),e.length>0},e.prototype.connect_=function(){ce&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),me?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},e.prototype.disconnect_=function(){ce&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},e.prototype.onTransitionEnd_=function(e){var t=e.propertyName,s=void 0===t?"":t;he.some((function(e){return!!~s.indexOf(e)}))&&this.refresh()},e.getInstance=function(){return this.instance_||(this.instance_=new e),this.instance_},e.instance_=null,e}(),fe=function(e,t){for(var s=0,o=Object.keys(t);s<o.length;s++){var n=o[s];Object.defineProperty(e,n,{value:t[n],enumerable:!1,writable:!1,configurable:!0})}return e},be=function(e){return e&&e.ownerDocument&&e.ownerDocument.defaultView||le},_e=ye(0,0,0,0);function ve(e){return parseFloat(e)||0}function ge(e){for(var t=[],s=1;s<arguments.length;s++)t[s-1]=arguments[s];return t.reduce((function(t,s){return t+ve(e["border-"+s+"-width"])}),0)}function ke(e){var t=e.clientWidth,s=e.clientHeight;if(!t&&!s)return _e;var o=be(e).getComputedStyle(e),n=function(e){for(var t={},s=0,o=["top","right","bottom","left"];s<o.length;s++){var n=o[s],r=e["padding-"+n];t[n]=ve(r)}return t}(o),r=n.left+n.right,i=n.top+n.bottom,a=ve(o.width),d=ve(o.height);if("border-box"===o.boxSizing&&(Math.round(a+r)!==t&&(a-=ge(o,"left","right")+r),Math.round(d+i)!==s&&(d-=ge(o,"top","bottom")+i)),!function(e){return e===be(e).document.documentElement}(e)){var c=Math.round(a+r)-t,l=Math.round(d+i)-s;1!==Math.abs(c)&&(a-=c),1!==Math.abs(l)&&(d-=l)}return ye(n.left,n.top,a,d)}var we="undefined"!=typeof SVGGraphicsElement?function(e){return e instanceof be(e).SVGGraphicsElement}:function(e){return e instanceof be(e).SVGElement&&"function"==typeof e.getBBox};function xe(e){return ce?we(e)?function(e){var t=e.getBBox();return ye(0,0,t.width,t.height)}(e):ke(e):_e}function ye(e,t,s,o){return{x:e,y:t,width:s,height:o}}var Ie=function(){function e(e){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=ye(0,0,0,0),this.target=e}return e.prototype.isActive=function(){var e=xe(this.target);return this.contentRect_=e,e.width!==this.broadcastWidth||e.height!==this.broadcastHeight},e.prototype.broadcastRect=function(){var e=this.contentRect_;return this.broadcastWidth=e.width,this.broadcastHeight=e.height,e},e}(),Te=function(e,t){var s,o,n,r,i,a,d,c=(o=(s=t).x,n=s.y,r=s.width,i=s.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,d=Object.create(a.prototype),fe(d,{x:o,y:n,width:r,height:i,top:n,right:o+r,bottom:i+n,left:o}),d);fe(this,{target:e,contentRect:c})},Ee=function(){function e(e,t,s){if(this.activeObservations_=[],this.observations_=new de,"function"!=typeof e)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=e,this.controller_=t,this.callbackCtx_=s}return e.prototype.observe=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof be(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)||(t.set(e,new Ie(e)),this.controller_.addObserver(this),this.controller_.refresh())}},e.prototype.unobserve=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof be(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)&&(t.delete(e),t.size||this.controller_.removeObserver(this))}},e.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},e.prototype.gatherActive=function(){var e=this;this.clearActive(),this.observations_.forEach((function(t){t.isActive()&&e.activeObservations_.push(t)}))},e.prototype.broadcastActive=function(){if(this.hasActive()){var e=this.callbackCtx_,t=this.activeObservations_.map((function(e){return new Te(e.target,e.broadcastRect())}));this.callback_.call(e,t,e),this.clearActive()}},e.prototype.clearActive=function(){this.activeObservations_.splice(0)},e.prototype.hasActive=function(){return this.activeObservations_.length>0},e}(),Oe="undefined"!=typeof WeakMap?new WeakMap:new de,Ce=function e(t){if(!(this instanceof e))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var s=pe.getInstance(),o=new Ee(t,s,this);Oe.set(this,o)};["observe","unobserve","disconnect"].forEach((function(e){Ce.prototype[e]=function(){var t;return(t=Oe.get(this))[e].apply(t,arguments)}}));void 0!==le.ResizeObserver&&le.ResizeObserver;const Se=class extends Event{constructor(e,t={}){super(Se.parentWindowEvent),this.eventName=e,this.eventParameters=t}get name(){return this.eventName}get parameters(){return this.eventParameters}};Se.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const qe=ae,{$:Pe}=window;class je{extend(e){e.getContainer().on("click",o.submitAction,(t=>{this.submit(t,e)}))}submit(e,t){const s=Pe(e.currentTarget),o=s.data("confirm-message"),n=s.data("confirmTitle");void 0!==o&&o.length>0?void 0!==n?this.showConfirmModal(s,t,o,n):window.confirm(o)&&this.postForm(s,t):this.postForm(s,t)}showConfirmModal(e,t,s,o){const n=e.data("confirmButtonLabel"),r=e.data("closeButtonLabel"),a=e.data("confirmButtonClass");new qe({id:i(t.getId()),confirmTitle:o,confirmMessage:s,confirmButtonLabel:n,closeButtonLabel:r,confirmButtonClass:a},(()=>this.postForm(e,t))).show()}postForm(e,t){const s=Pe(O(t.getId()));s.attr("action",e.data("form-url")),s.attr("method",e.data("form-method")),s.submit()}}
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
const{$:Be}=window;class Ae{extend(e){this.handleBulkActionCheckboxSelect(e),this.handleBulkActionSelectAllCheckbox(e)}handleBulkActionSelectAllCheckbox(e){e.getContainer().on("change",o.actionSelectAll,(t=>{const s=Be(t.currentTarget).is(":checked");s?this.enableBulkActionsBtn(e):this.disableBulkActionsBtn(e),e.getContainer().find(o.bulkActionCheckbox).prop("checked",s)}))}handleBulkActionCheckboxSelect(e){e.getContainer().on("change",o.bulkActionCheckbox,(()=>{e.getContainer().find(o.checkedCheckbox).length>0?this.enableBulkActionsBtn(e):this.disableBulkActionsBtn(e)}))}enableBulkActionsBtn(e){e.getContainer().find(o.bulkActionBtn).prop("disabled",!1)}disableBulkActionsBtn(e){e.getContainer().find(o.bulkActionBtn).prop("disabled",!0)}}
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
class Me{extend(e){const t=e.getContainer().find(v);t.find(g).prop("disabled",!0),t.find(w).on("input dp.change",(()=>{t.find(g).prop("disabled",!1),t.find(k).prop("hidden",!1)}))}}
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
const{$:$e}=window;class Le{constructor(){this.lockArray=[]}extend(e){e.getContainer().find(o.choiceOptions).find(u).on("click",(e=>{e.preventDefault();const t=$e(e.currentTarget),s=t.closest(o.choiceOptions).data("url");this.submitForm(s,t)}))}submitForm(e,t){const s=t.data("value");if(this.isLocked(e))return;const o=$e("<form>",{action:e,method:"POST"}).append($e("<input>",{name:"value",value:s,type:"hidden"}));o.appendTo("body"),o.submit(),this.lock(e)}isLocked(e){return this.lockArray.includes(e)}lock(e){this.lockArray.push(e)}}
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
const{$:Re}=window;class Ge{extend(e){e.getContainer().on("click",o.modalFormSubmitBtn,(t=>{const s=Re(t.target).data("modal-id"),n=Re(`#${s}`);n.modal("show"),n.find(r.submitModalFormBtn).on("click",(()=>{const t=n.find("form"),s=t.find(r.bulkInputsBlock(t.data("bulk-inputs-id")));e.getContainer().find(o.checkedCheckbox).each(((e,o)=>{const n=Re(o),r=s.data("prototype").replace(/__name__/g,n.val()),i=Re(Re.parseHTML(r)[0]);i.val(n.val()),t.append(i)})),t.submit()}))}))}}
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
const{$:De}=window;class Fe{constructor(e,t){this.locks=[],this.expandSelector=b,this.collapseSelector=_,this.previewOpenClass="preview-open",this.previewToggleSelector=x,this.previewCustomization=e,this.$gridContainer=De(t.getContainer)}extend(e){this.$gridContainer=De(e.getContainer),this.$gridContainer.find("tbody tr").on("mouseover mouseleave",(e=>this.handleIconHovering(e))),this.$gridContainer.find(this.previewToggleSelector).on("click",(e=>this.togglePreview(e)))}handleIconHovering(e){const t=De(e.currentTarget).find(this.previewToggleSelector);"mouseover"!==e.type||De(e.currentTarget).hasClass(this.previewOpenClass)?this.hideExpandIcon(t):this.showExpandIcon(t)}togglePreview(e){const t=De(e.currentTarget).closest("tr");if(t.hasClass(this.previewOpenClass))return t.next(y).remove(),t.removeClass(this.previewOpenClass),this.showExpandIcon(t),void this.hideCollapseIcon(t);this.closeOpenedPreviews();const s=De(e.currentTarget).data("preview-data-url");this.isLocked(s)||(this.lock(s),De.ajax({url:s,method:"GET",dataType:"json",complete:()=>{this.unlock(s)}}).then((e=>{this.renderPreviewContent(t,e.preview)})).catch((e=>{window.showErrorMessage(e.responseJSON.message)})))}renderPreviewContent(e,t){const s=e.find("td").length,o=De(`\n        <tr class="preview-row">\n          <td colspan="${s}">${t}</td>\n        </tr>\n      `);e.addClass(this.previewOpenClass),this.showCollapseIcon(e),this.hideExpandIcon(e),"function"==typeof this.previewCustomization&&this.previewCustomization(o),e.after(o)}showExpandIcon(e){e.find(this.expandSelector).removeClass("d-none")}hideExpandIcon(e){e.find(this.expandSelector).addClass("d-none")}showCollapseIcon(e){e.find(this.collapseSelector).removeClass("d-none")}hideCollapseIcon(e){e.find(this.collapseSelector).addClass("d-none")}isLocked(e){return-1!==this.locks.indexOf(e)}lock(e){this.isLocked(e)||this.locks.push(e)}unlock(e){const t=this.locks.indexOf(e);-1!==t&&this.locks.splice(t,1)}closeOpenedPreviews(){const e=this.$gridContainer.find(I).find(T);De.each(e,((e,t)=>{const s=De(t);if(!s.hasClass(this.previewOpenClass))return;const o=s.next();o.hasClass("preview-row")&&(o.remove(),s.removeClass(this.previewOpenClass),this.hideCollapseIcon(s))}))}}
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
const{$:He}=window;function ze(e){Qe(e),e.on("click",".js-preview-more-products-btn",(t=>{t.preventDefault();const s=He(t.currentTarget);s.closest("tbody").find(".js-product-preview-more").removeClass("d-none"),s.closest("tr").remove(),Qe(e)}))}function Qe(e){let t=!1;He(".js-cell-product-stock-location",e.find("tr:not(.d-none)")).filter("td").each(((e,s)=>{if(""!==He(s).html().trim())return t=!0,!1})),He(".js-cell-product-stock-location",e).toggle(t)}var Ne=s(2564),Ue=s.n(Ne);const We=JSON.parse('{"base_url":"","routes":{"admin_common_notifications":{"tokens":[["text","/common/notifications"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_product_form":{"tokens":[["variable","/","\\\\d+","id"],["text","/sell/catalog/products"]],"defaults":[],"requirements":{"id":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_feature_get_feature_values":{"tokens":[["variable","/","\\\\d+","idFeature"],["text","/sell/catalog/products/features"]],"defaults":{"idFeature":0},"requirements":{"idFeature":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations":{"tokens":[["text","/combinations"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_ids":{"tokens":[["text","/combinations/ids"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_update_combination_from_listing":{"tokens":[["text","/update-combination-from-listing"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_edit_combination":{"tokens":[["text","/edit"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_combinations_bulk_edit_combination":{"tokens":[["text","/combinations/bulk-edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_delete_combination":{"tokens":[["text","/delete"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_combinations_bulk_delete":{"tokens":[["text","/combinations/bulk-delete"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_attribute_groups":{"tokens":[["text","/attribute-groups"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_all_attribute_groups":{"tokens":[["text","/sell/catalog/products-v2/all-attribute-groups"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_generate":{"tokens":[["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2/generate-combinations"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_get_images":{"tokens":[["text","/images"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_v2_add_image":{"tokens":[["text","/sell/catalog/products-v2/images/add"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_update_image":{"tokens":[["text","/update"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_v2_delete_image":{"tokens":[["text","/delete"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_specific_prices_list":{"tokens":[["text","/specific-prices/list"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_specific_prices_create":{"tokens":[["text","/specific-prices/create"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_delete":{"tokens":[["text","/delete"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_v2_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST","PATCH"],"schemes":[]},"admin_products_v2_bulk_enable":{"tokens":[["text","/sell/catalog/products-v2/bulk-enable"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_disable":{"tokens":[["text","/sell/catalog/products-v2/bulk-disable"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_duplicate":{"tokens":[["text","/sell/catalog/products-v2/bulk-duplicate"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_delete":{"tokens":[["text","/sell/catalog/products-v2/bulk-delete"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST","DELETE"],"schemes":[]},"admin_categories_get_categories_tree":{"tokens":[["text","/sell/catalog/categories/tree"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_cart_rules_search":{"tokens":[["text","/sell/catalog/cart-rules/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_customers_search":{"tokens":[["text","/sell/customers/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_carts":{"tokens":[["text","/carts"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_orders":{"tokens":[["text","/orders"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_addresses_create":{"tokens":[["text","/sell/addresses/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_addresses_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","addressId"],["text","/sell/addresses"]],"defaults":[],"requirements":{"addressId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_order_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","orderId"],["text","/sell/addresses/order"]],"defaults":[],"requirements":{"orderId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_cart_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","cartId"],["text","/sell/addresses/cart"]],"defaults":[],"requirements":{"cartId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_carts_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_create":{"tokens":[["text","/sell/orders/carts/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_addresses":{"tokens":[["text","/addresses"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_carrier":{"tokens":[["text","/carrier"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_currency":{"tokens":[["text","/currency"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_language":{"tokens":[["text","/language"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_set_delivery_settings":{"tokens":[["text","/rules/delivery-settings"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_cart_rule":{"tokens":[["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_cart_rule":{"tokens":[["text","/delete"],["variable","/","[^/]++","cartRuleId"],["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_price":{"tokens":[["text","/price"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_quantity":{"tokens":[["text","/quantity"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_product":{"tokens":[["text","/delete-product"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_place":{"tokens":[["text","/sell/orders/place"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_orders_duplicate_cart":{"tokens":[["text","/duplicate-cart"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_update_product":{"tokens":[["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_partial_refund":{"tokens":[["text","/partial-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_standard_refund":{"tokens":[["text","/standard-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_return_product":{"tokens":[["text","/return-product"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_send_process_order_email":{"tokens":[["text","/sell/orders/process-order-email"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_delete_product":{"tokens":[["text","/delete"],["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_get_discounts":{"tokens":[["text","/discounts"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_prices":{"tokens":[["text","/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_payments":{"tokens":[["text","/payments"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_products":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_invoices":{"tokens":[["text","/invoices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_documents":{"tokens":[["text","/documents"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_shipping":{"tokens":[["text","/shipping"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_cancellation":{"tokens":[["text","/cancellation"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_configure_product_pagination":{"tokens":[["text","/sell/orders/configure-product-pagination"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_product_prices":{"tokens":[["text","/products/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_products_search":{"tokens":[["text","/sell/orders/products/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_attachment_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","attachmentId"],["text","/sell/attachments"]],"defaults":[],"requirements":{"attachmentId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_search":{"tokens":[["variable","/","[^/]++","searchPhrase"],["text","/sell/attachments/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_shops_search":{"tokens":[["variable","/","[^/]++","searchTerm"],["text","/configure/advanced/shops/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]}},"prefix":"","host":"localhost","port":"","scheme":"http","locale":""}'),{$:Ve}=window;class Je{constructor(){return window.prestashop&&window.prestashop.customRoutes&&Object.assign(We.routes,window.prestashop.customRoutes),Ue().setData(We),Ue().setBaseUrl(Ve(document).find("body").data("base-url")),this}generate(e,t={}){const s=Object.assign(t,{_token:Ve(document).find("body").data("token")});return Ue().generate(e,s)}}
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
const{$:Ke}=window;class Xe{constructor(){this.router=new Je}extend(e){e.getContainer().on("click",o.openTabsBtn,(t=>{this.openTabs(t,e)}))}openTabs(e,t){const s=Ke(e.currentTarget),n=s.data("route"),r=s.data("routeParamName"),i=s.data("tabsBlockedMessage"),a=t.getContainer().find(o.checkedCheckbox);let d=!0;a.each(((e,t)=>{const s=Ke(t),o={};o[r]=s.val();const a=window.open(this.router.generate(n,o));a?(a.blur(),window.focus()):d=!1,d||alert(i)}))}}
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
const{$:Ye}=window;Ye((()=>{const e=new q("order");e.addExtension(new M),e.addExtension(new L),e.addExtension(new A),e.addExtension(new D),e.addExtension(new H),e.addExtension(new Q),e.addExtension(new je),e.addExtension(new Ae),e.addExtension(new Me),e.addExtension(new Ge),e.addExtension(new Le),e.addExtension(new Fe(ze,e)),e.addExtension(new Xe)}))},2564:e=>{var t=Object.assign||function(e){for(var t,s=1;s<arguments.length;s++)for(var o in t=arguments[s])Object.prototype.hasOwnProperty.call(t,o)&&(e[o]=t[o]);return e},s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};e.exports=new function e(){var o=this;(function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")})(this,e),this.setRoutes=function(e){o.routesRouting=e||[]},this.getRoutes=function(){return o.routesRouting},this.setBaseUrl=function(e){o.contextRouting.base_url=e},this.getBaseUrl=function(){return o.contextRouting.base_url},this.setPrefix=function(e){o.contextRouting.prefix=e},this.setScheme=function(e){o.contextRouting.scheme=e},this.getScheme=function(){return o.contextRouting.scheme},this.setHost=function(e){o.contextRouting.host=e},this.getHost=function(){return o.contextRouting.host},this.buildQueryParams=function(e,t,n){var r=new RegExp(/\[]$/);t instanceof Array?t.forEach((function(t,i){r.test(e)?n(e,t):o.buildQueryParams(e+"["+("object"===(void 0===t?"undefined":s(t))?i:"")+"]",t,n)})):"object"===(void 0===t?"undefined":s(t))?Object.keys(t).forEach((function(s){return o.buildQueryParams(e+"["+s+"]",t[s],n)})):n(e,t)},this.getRoute=function(e){var t=o.contextRouting.prefix+e;if(o.routesRouting[t])return o.routesRouting[t];if(!o.routesRouting[e])throw new Error('The route "'+e+'" does not exist.');return o.routesRouting[e]},this.generate=function(e,s,n){var r=o.getRoute(e),i=s||{},a=t({},i),d="_scheme",c="",l=!0,u="";if((r.tokens||[]).forEach((function(t){if("text"===t[0])return c=t[1]+c,void(l=!1);if("variable"!==t[0])throw new Error('The token type "'+t[0]+'" is not supported.');var s=(r.defaults||{})[t[3]];if(0==l||!s||(i||{})[t[3]]&&i[t[3]]!==r.defaults[t[3]]){var o;if((i||{})[t[3]])o=i[t[3]],delete a[t[3]];else{if(!s){if(l)return;throw new Error('The route "'+e+'" requires the parameter "'+t[3]+'".')}o=r.defaults[t[3]]}if(!(!0===o||!1===o||""===o)||!l){var n=encodeURIComponent(o).replace(/%2F/g,"/");"null"===n&&null===o&&(n=""),c=t[1]+n+c}l=!1}else s&&delete a[t[3]]})),""==c&&(c="/"),(r.hosttokens||[]).forEach((function(e){var t;return"text"===e[0]?void(u=e[1]+u):void("variable"===e[0]&&((i||{})[e[3]]?(t=i[e[3]],delete a[e[3]]):r.defaults[e[3]]&&(t=r.defaults[e[3]]),u=e[1]+t+u))})),c=o.contextRouting.base_url+c,r.requirements[d]&&o.getScheme()!==r.requirements[d]?c=r.requirements[d]+"://"+(u||o.getHost())+c:u&&o.getHost()!==u?c=o.getScheme()+"://"+u+c:!0===n&&(c=o.getScheme()+"://"+o.getHost()+c),0<Object.keys(a).length){var h=[],m=function(e,t){var s=t;s=null===(s="function"==typeof s?s():s)?"":s,h.push(encodeURIComponent(e)+"="+encodeURIComponent(s))};Object.keys(a).forEach((function(e){return o.buildQueryParams(e,a[e],m)})),c=c+"?"+h.join("&").replace(/%20/g,"+")}return c},this.setData=function(e){o.setBaseUrl(e.base_url),o.setRoutes(e.routes),"prefix"in e&&o.setPrefix(e.prefix),o.setHost(e.host),o.setScheme(e.scheme)},this.contextRouting={base_url:"",prefix:"",host:"",scheme:""}}},9567:e=>{e.exports=window.jQuery}},t={};function s(o){var n=t[o];if(void 0!==n)return n.exports;var r=t[o]={exports:{}};return e[o](r,r.exports,s),r.exports}s.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return s.d(t,{a:t}),t},s.d=(e,t)=>{for(var o in t)s.o(t,o)&&!s.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},s.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),s.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),s.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o=s(7721);window.order=o})();