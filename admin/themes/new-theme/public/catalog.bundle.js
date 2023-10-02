(()=>{"use strict";var t={r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},o={};t.r(o);
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
const{$:e}=window;const r=class{constructor(t){var o;this.selector=".ps-sortable-column",this.idTable=null!=(o=t.attr("id"))?o:"",this.columns=t.find(this.selector)}attach(){this.columns.on("click",(t=>{const o=e(t.delegateTarget);this.sortByColumn(o,this.getToggledSortDirection(o))}))}sortBy(t,o){if(!this.columns.is(`[data-sort-col-name="${t}"]`))throw new Error(`Cannot sort by "${t}": invalid column`);this.sortByColumn(this.columns,o)}sortByColumn(t,o){window.location.href=this.getUrl(t.data("sortColName"),"desc"===o?"desc":"asc",t.data("sortPrefix"))}getToggledSortDirection(t){return"asc"===t.data("sortDirection")?"desc":"asc"}getUrl(t,o,e){const r=new URL(window.location.href),s=r.searchParams;return e?(s.set(`${e}[orderBy]`,t),s.set(`${e}[sortOrder]`,o)):(s.set("orderBy",t),s.set("sortOrder",o)),r.hash=this.idTable,r.toString()}},{$:s}=window;s((()=>{new r(s("table.table")).attach()})),window.catalog=o})();