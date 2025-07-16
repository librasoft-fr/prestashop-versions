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
 */
const{$:n}=window,o=({sourceElementSelector:o,destinationElementSelector:e,options:t={eventName:"input"}})=>{n(document).on(t.eventName,`${o}`,(o=>{n(o.currentTarget).closest("form").data("id")||n(e).val(window.str2url(n(o.currentTarget).val(),"UTF-8"))}))},{$:e}=window;class t{constructor(n){this.id=n,this.$container=e(`#${this.id}`)}getContainer(){return this.$container}addExtension(n){n.extend(this)}}
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
const{$:s}=window;class i{extend(n){const o=n.getContainer();o.on("click",".js-remove-helper-block",(n=>{o.remove();const e=s(n.target),t=e.data("closeUrl"),i=e.data("cardName");t&&s.post(t,{close:1,name:i})}))}}
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
const{$:d}=window;d((()=>{const n=new window.prestashop.component.Grid("cms_page_category");n.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),n.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),n.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),n.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension),n.addExtension(new window.prestashop.component.GridExtensions.PositionExtension(n)),n.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),window.prestashop.component.initComponents(["TranslatableInput"]);const e=window.prestashop.instance.translatableInput;o({sourceElementSelector:'input[name^="cms_page_category[name]"]',destinationElementSelector:`${e.localeInputSelector}:not(.d-none) input[name^="cms_page_category[friendly_url]"]`}),new window.prestashop.component.ChoiceTree("#cms_page_category_parent_category");new window.prestashop.component.ChoiceTree("#cms_page_category_shop_association").enableAutoCheckChildren();const s=new window.prestashop.component.Grid("cms_page");s.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),s.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),s.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),s.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),s.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension),s.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),s.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),s.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),s.addExtension(new window.prestashop.component.GridExtensions.PositionExtension(s)),s.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),s.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension);new t("cms-pages-showcase-card").addExtension(new i)})),window.cms_page={}})();