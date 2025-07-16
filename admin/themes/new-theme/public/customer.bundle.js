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
 */const e=".js-form-submit-btn",{$:n}=window;class t{constructor(){n(document).on("click",e,(e=>{e.preventDefault();const t=n(e.target);if(t.data("form-confirm-message")&&!1===window.confirm(t.data("form-confirm-message")))return;let o="POST",s=null;if(t.data("method")){const e=t.data("method"),i=["GET","POST"].includes(e);o=i?e:"POST",i||(s=n("<input>",{type:"_hidden",name:"_method",value:e}))}const i=n("<form>",{action:t.data("form-submit-url"),method:o});s&&i.append(s),t.data("form-csrf-token")&&i.append(n("<input>",{type:"_hidden",name:"_csrf_token",value:t.data("form-csrf-token")})),i.appendTo("body").submit()}))}}
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
const{$:o}=window;class s{constructor(){o(document).on("click",".js-linkable-item",(e=>{window.location=o(e.currentTarget).data("linkable-href")}))}}
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
 */const i={deleteCategories:".js-delete-categories-bulk-action",deleteCategoriesModal:e=>`#${e}_grid_delete_categories_modal`,checkedCheckbox:".js-bulk-action-checkbox:checked",deleteCustomers:".js-delete-customers-bulk-action",deleteCustomerModal:e=>`#${e}_grid_delete_customers_modal`,submitDeleteCategories:".js-submit-delete-categories",submitDeleteCustomers:".js-submit-delete-customers",categoriesToDelete:"#delete_categories_categories_to_delete",customersToDelete:"#delete_customers_customers_to_delete",actionSelectAll:".js-bulk-action-select-all",bulkActionCheckbox:".js-bulk-action-checkbox",bulkActionBtn:".js-bulk-actions-btn",openTabsBtn:".js-bulk-action-btn.open_tabs",tableChoiceOptions:"table.table .js-choice-options",choiceOptions:".js-choice-options",modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitAction:".js-bulk-action-submit-btn",ajaxAction:".js-bulk-action-ajax-btn",gridSubmitAction:".js-grid-action-submit-btn"},d={categoryDeleteAction:".js-delete-category-row-action",customerDeleteAction:".js-delete-customer-row-action",linkRowAction:".js-link-row-action",linkRowActionClickableFirst:".js-link-row-action[data-clickable-row=1]:first",clickableTd:"td.clickable",imageTypeDeleteAction:".js-delete-image-type-row-action",deleteImageTypeModal:e=>`#${e}_grid_delete_image_type_modal`,submitDeleteImageType:".js-submit-delete-image-type"},{$:c}=window;class r{extend(e){e.getContainer().on("click",i.deleteCustomers,(n=>{n.preventDefault();const t=c(n.currentTarget).data("customers-delete-url"),o=c(i.deleteCustomerModal(e.getId()));o.modal("show"),o.on("click",i.submitDeleteCustomers,(()=>{e.getContainer().find(i.checkedCheckbox).each(((e,n)=>{const t=c(n);this.addCustomerToDeleteCollectionInput(t.val())}));const n=o.find("form");n.attr("action",t),n.submit()}))}))}addCustomerToDeleteCollectionInput(e){const n=c(i.customersToDelete),t=n.data("prototype").replace(/__name__/g,e),o=c(c.parseHTML(t)[0]);o.val(e),n.append(o)}}
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
const{$:a}=window;class l{extend(e){e.getContainer().on("click",d.customerDeleteAction,(n=>{n.preventDefault();const t=a(i.deleteCustomerModal(e.getId()));t.modal("show"),t.on("click",i.submitDeleteCustomers,(()=>{const e=a(n.currentTarget),o=e.data("customer-id");this.addCustomerInput(o);const s=t.find("form");s.attr("action",e.data("customer-delete-url")),s.submit()}))}))}addCustomerInput(e){const n=a(i.customersToDelete),t=n.data("prototype").replace(/__name__/g,n.children().length),o=a(a.parseHTML(t)[0]);o.val(e),n.append(o)}}
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
const{$:m}=window;class p{constructor(e){this.id=e,this.$container=m(`#${this.id}`)}getContainer(){return this.$container}addExtension(e){e.extend(this)}}
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
const{$:w}=window;class u{extend(e){const n=e.getContainer();n.on("click",".js-remove-helper-block",(e=>{n.remove();const t=w(e.target),o=t.data("closeUrl"),s=t.data("cardName");o&&w.post(o,{close:1,name:s})}))}}
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
 */const x="#customerRequiredFieldsAlertMessageOptin",h='#customerRequiredFieldsContainer input[type="checkbox"][value="optin"]',{$:E}=window;E((()=>{const e=new window.prestashop.component.Grid("customer");e.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),e.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),e.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),e.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),e.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),e.addExtension(new r),e.addExtension(new l),e.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),e.addExtension(new window.prestashop.component.GridExtensions.AsyncToggleColumnExtension);const n=new window.prestashop.component.Grid("customer_discount");n.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension);const o=new window.prestashop.component.Grid("customer_address");o.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),o.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),o.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension);const i=new window.prestashop.component.Grid("customer_order");i.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),i.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),i.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension);const d=new window.prestashop.component.Grid("customer_cart");d.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),d.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),d.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension);new window.prestashop.component.Grid("customer_bought_product").addExtension(new window.prestashop.component.GridExtensions.SortingExtension);new window.prestashop.component.Grid("customer_viewed_product").addExtension(new window.prestashop.component.GridExtensions.SortingExtension);new p("customersShowcaseCard").addExtension(new u),new s,new t,function(){var e,n,t,o,s,i,d,c,r;const a=new URL(document.URL).hash.slice(1);if(""===a)return;const l=document.getElementById(a);if(!l)return;let m=0;if(l.offsetParent){let e=l;do{m+=e.offsetTop,e=e.offsetParent?e.offsetParent:null}while(null!==e)}m-=null!=(t=null==(n=null==(e=document.querySelector("#header_infos"))?void 0:e.getBoundingClientRect())?void 0:n.height)?t:0,m-=null!=(i=null==(s=null==(o=document.querySelector(".header-toolbar"))?void 0:o.getBoundingClientRect())?void 0:s.height)?i:0,m-=null!=(r=null==(c=null==(d=document.querySelector(".card-header"))?void 0:d.getBoundingClientRect())?void 0:c.height)?r:0,m-=10,window.scroll(0,m)}(),E(h).on("click",(()=>{E(x).toggleClass("d-none",!E(h).is(":checked"))}))})),window.customer={}})();