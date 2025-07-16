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
 */const e={deleteCategories:".js-delete-categories-bulk-action",deleteCategoriesModal:e=>`#${e}_grid_delete_categories_modal`,checkedCheckbox:".js-bulk-action-checkbox:checked",deleteCustomers:".js-delete-customers-bulk-action",deleteCustomerModal:e=>`#${e}_grid_delete_customers_modal`,submitDeleteCategories:".js-submit-delete-categories",submitDeleteCustomers:".js-submit-delete-customers",categoriesToDelete:"#delete_categories_categories_to_delete",customersToDelete:"#delete_customers_customers_to_delete",actionSelectAll:".js-bulk-action-select-all",bulkActionCheckbox:".js-bulk-action-checkbox",bulkActionBtn:".js-bulk-actions-btn",openTabsBtn:".js-bulk-action-btn.open_tabs",tableChoiceOptions:"table.table .js-choice-options",choiceOptions:".js-choice-options",modalFormSubmitBtn:".js-bulk-modal-form-submit-btn",submitAction:".js-bulk-action-submit-btn",ajaxAction:".js-bulk-action-ajax-btn",gridSubmitAction:".js-grid-action-submit-btn"},t={categoryDeleteAction:".js-delete-category-row-action",customerDeleteAction:".js-delete-customer-row-action",linkRowAction:".js-link-row-action",linkRowActionClickableFirst:".js-link-row-action[data-clickable-row=1]:first",clickableTd:"td.clickable",imageTypeDeleteAction:".js-delete-image-type-row-action",deleteImageTypeModal:e=>`#${e}_grid_delete_image_type_modal`,submitDeleteImageType:".js-submit-delete-image-type"},{$:o}=window;class n{extend(n){n.getContainer().on("click",t.categoryDeleteAction,(t=>{t.preventDefault();const i=o(e.deleteCategoriesModal(n.getId()));i.modal("show"),i.on("click",e.submitDeleteCategories,(()=>{const n=o(t.currentTarget),s=n.data("category-id"),d=o(e.categoriesToDelete),a=d.data("prototype").replace(/__name__/g,d.children().length),c=o(o.parseHTML(a)[0]);c.val(s),d.append(c);const r=i.find("form");r.attr("action",n.data("category-delete-url")),r.submit()}))}))}}
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
const{$:i}=window;class s{constructor(e){this.id=e,this.$container=i(`#${this.id}`)}getContainer(){return this.$container}addExtension(e){e.extend(this)}}
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
const{$:d}=window;class a{extend(e){const t=e.getContainer();t.on("click",".js-remove-helper-block",(e=>{t.remove();const o=d(e.target),n=o.data("closeUrl"),i=o.data("cardName");n&&d.post(n,{close:1,name:i})}))}}
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
const{$:c}=window;c((()=>{const e=new window.prestashop.component.Grid("empty_category");e.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),e.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),e.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),e.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),e.addExtension(new window.prestashop.component.GridExtensions.AsyncToggleColumnExtension),e.addExtension(new n),e.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),["no_qty_product_with_combination","no_qty_product_without_combination","disabled_product","product_without_image","product_without_description","product_without_price"].forEach((e=>{const t=new window.prestashop.component.Grid(e);t.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),t.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),t.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),t.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),t.addExtension(new window.prestashop.component.GridExtensions.AsyncToggleColumnExtension),t.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),t.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),t.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),t.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),t.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension)}));new s("monitoringShowcaseCard").addExtension(new a)})),window.monitoring={}})();