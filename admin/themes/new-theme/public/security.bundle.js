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
 */const n=".js-form-submit-btn",{$:o}=window;class e{constructor(){o(document).on("click",n,(n=>{n.preventDefault();const e=o(n.target);if(e.data("form-confirm-message")&&!1===window.confirm(e.data("form-confirm-message")))return;let t="POST",s=null;if(e.data("method")){const n=e.data("method"),i=["GET","POST"].includes(n);t=i?n:"POST",i||(s=o("<input>",{type:"_hidden",name:"_method",value:n}))}const i=o("<form>",{action:e.data("form-submit-url"),method:t});s&&i.append(s),e.data("form-csrf-token")&&i.append(o("<input>",{type:"_hidden",name:"_csrf_token",value:e.data("form-csrf-token")})),i.appendTo("body").submit()}))}}
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
const{$:t}=window;t((()=>{const n=new window.prestashop.component.Grid("security_session_employee");n.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),n.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),n.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),n.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),n.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension),n.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension);const o=new window.prestashop.component.Grid("security_session_customer");o.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension),o.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension),o.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension),o.addExtension(new window.prestashop.component.GridExtensions.SortingExtension),o.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension),o.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension),o.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension),o.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension),o.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension),o.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension),o.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension),new e})),window.security={}})();