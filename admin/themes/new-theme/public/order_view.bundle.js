(()=>{var e={6798:(e,t,r)=>{"use strict";r.d(t,{Z:()=>o});const o=
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
class{constructor(e){this.message=e,this.name="LocalizationException"}}},4902:(e,t,r)=>{"use strict";r.d(t,{NumberFormatter:()=>d});var o=r(1463),s=r(1583),n=r(3096);
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
const i=r(1658);class a{constructor(e){this.numberSpecification=e}format(e,t){void 0!==t&&(this.numberSpecification=t);const r=Math.abs(e).toFixed(this.numberSpecification.getMaxFractionDigits());let[o,s]=this.extractMajorMinorDigits(r);o=this.splitMajorGroups(o),s=this.adjustMinorDigitsZeroes(s);let n=o;s&&(n+="."+s);const i=this.getCldrPattern(e<0);return n=this.addPlaceholders(n,i),n=this.replaceSymbols(n),n=this.performSpecificReplacements(n),n}extractMajorMinorDigits(e){const t=e.toString().split(".");return[t[0],void 0===t[1]?"":t[1]]}splitMajorGroups(e){if(!this.numberSpecification.isGroupingUsed())return e;const t=e.split("").reverse();let r=[];for(r.push(t.splice(0,this.numberSpecification.getPrimaryGroupSize()));t.length;)r.push(t.splice(0,this.numberSpecification.getSecondaryGroupSize()));r=r.reverse();const o=[];return r.forEach((e=>{o.push(e.reverse().join(""))})),o.join(",")}adjustMinorDigitsZeroes(e){let t=e;return t.length>this.numberSpecification.getMaxFractionDigits()&&(t=t.replace(/0+$/,"")),t.length<this.numberSpecification.getMinFractionDigits()&&(t=t.padEnd(this.numberSpecification.getMinFractionDigits(),"0")),t}getCldrPattern(e){return e?this.numberSpecification.getNegativePattern():this.numberSpecification.getPositivePattern()}replaceSymbols(e){const t=this.numberSpecification.getSymbol(),r={};return r["."]=t.getDecimal(),r[","]=t.getGroup(),r["-"]=t.getMinusSign(),r["%"]=t.getPercentSign(),r["+"]=t.getPlusSign(),this.strtr(e,r)}strtr(e,t){const r=Object.keys(t).map(i);return e.split(RegExp(`(${r.join("|")})`)).map((e=>t[e]||e)).join("")}addPlaceholders(e,t){return t.replace(/#?(,#+)*0(\.[0#]+)*/,e)}performSpecificReplacements(e){return this.numberSpecification instanceof s.Z?e.split("¤").join(this.numberSpecification.getCurrencySymbol()):e}static build(e){let t,r;return t=void 0!==e.numberSymbols?new o.Z(...e.numberSymbols):new o.Z(...e.symbol),r=e.currencySymbol?new s.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize,e.currencySymbol,e.currencyCode):new n.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize),new a(r)}}const d=a}
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
 */,1463:(e,t,r)=>{"use strict";r.d(t,{Z:()=>s});var o=r(6798);
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
 */const s=class{constructor(e,t,r,o,s,n,i,a,d,c,l){this.decimal=e,this.group=t,this.list=r,this.percentSign=o,this.minusSign=s,this.plusSign=n,this.exponential=i,this.superscriptingExponent=a,this.perMille=d,this.infinity=c,this.nan=l,this.validateData()}getDecimal(){return this.decimal}getGroup(){return this.group}getList(){return this.list}getPercentSign(){return this.percentSign}getMinusSign(){return this.minusSign}getPlusSign(){return this.plusSign}getExponential(){return this.exponential}getSuperscriptingExponent(){return this.superscriptingExponent}getPerMille(){return this.perMille}getInfinity(){return this.infinity}getNan(){return this.nan}validateData(){if(!this.decimal||"string"!=typeof this.decimal)throw new o.Z("Invalid decimal");if(!this.group||"string"!=typeof this.group)throw new o.Z("Invalid group");if(!this.list||"string"!=typeof this.list)throw new o.Z("Invalid symbol list");if(!this.percentSign||"string"!=typeof this.percentSign)throw new o.Z("Invalid percentSign");if(!this.minusSign||"string"!=typeof this.minusSign)throw new o.Z("Invalid minusSign");if(!this.plusSign||"string"!=typeof this.plusSign)throw new o.Z("Invalid plusSign");if(!this.exponential||"string"!=typeof this.exponential)throw new o.Z("Invalid exponential");if(!this.superscriptingExponent||"string"!=typeof this.superscriptingExponent)throw new o.Z("Invalid superscriptingExponent");if(!this.perMille||"string"!=typeof this.perMille)throw new o.Z("Invalid perMille");if(!this.infinity||"string"!=typeof this.infinity)throw new o.Z("Invalid infinity");if(!this.nan||"string"!=typeof this.nan)throw new o.Z("Invalid nan")}}},3096:(e,t,r)=>{"use strict";r.d(t,{Z:()=>n});var o=r(6798),s=r(1463);const n=
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
class{constructor(e,t,r,n,i,a,d,c){if(this.positivePattern=e,this.negativePattern=t,this.symbol=r,this.maxFractionDigits=n,this.minFractionDigits=n<i?n:i,this.groupingUsed=a,this.primaryGroupSize=d,this.secondaryGroupSize=c,!this.positivePattern||"string"!=typeof this.positivePattern)throw new o.Z("Invalid positivePattern");if(!this.negativePattern||"string"!=typeof this.negativePattern)throw new o.Z("Invalid negativePattern");if(!(this.symbol&&this.symbol instanceof s.Z))throw new o.Z("Invalid symbol");if("number"!=typeof this.maxFractionDigits)throw new o.Z("Invalid maxFractionDigits");if("number"!=typeof this.minFractionDigits)throw new o.Z("Invalid minFractionDigits");if("boolean"!=typeof this.groupingUsed)throw new o.Z("Invalid groupingUsed");if("number"!=typeof this.primaryGroupSize)throw new o.Z("Invalid primaryGroupSize");if("number"!=typeof this.secondaryGroupSize)throw new o.Z("Invalid secondaryGroupSize")}getSymbol(){return this.symbol}getPositivePattern(){return this.positivePattern}getNegativePattern(){return this.negativePattern}getMaxFractionDigits(){return this.maxFractionDigits}getMinFractionDigits(){return this.minFractionDigits}isGroupingUsed(){return this.groupingUsed}getPrimaryGroupSize(){return this.primaryGroupSize}getSecondaryGroupSize(){return this.secondaryGroupSize}}},1583:(e,t,r)=>{"use strict";r.d(t,{Z:()=>i});var o=r(6798),s=r(3096);class n extends s.Z{constructor(e,t,r,s,n,i,a,d,c,l){if(super(e,t,r,s,n,i,a,d),this.currencySymbol=c,this.currencyCode=l,!this.currencySymbol||"string"!=typeof this.currencySymbol)throw new o.Z("Invalid currencySymbol");if(!this.currencyCode||"string"!=typeof this.currencyCode)throw new o.Z("Invalid currencyCode")}static getCurrencyDisplay(){return"symbol"}getCurrencySymbol(){return this.currencySymbol}getCurrencyCode(){return this.currencyCode}}const i=n},1159:(e,t,r)=>{"use strict";r.r(t);
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
 */const o="#order-view-page",s=".js-payment-details-btn",n="#order_payment_amount_currency_amount",i="#order_payment_id_invoice",a="#view_order_payments_block",d=".js-view-order-payments-alert",c=".js-private-note-toggle-btn",l=".js-private-note-block",u="#private_note_note",h=".js-private-note-btn",p="#addOrderDiscountModal",m="#add_order_cart_rule_invoice_id",f="#add_order_cart_rule_name",v="#add_order_cart_rule_type",g="#add_order_cart_rule_value",b="#add_order_cart_rule_value_unit",_="#add_order_cart_rule_submit",x="#add_order_cart_rule_apply_on_all_invoices",y=".js-cart-rule-value-help",P="#update_order_status_action_btn",I="#update_order_status_action_input",w="#update_order_status_action_input_wrapper",T=".js-update-shipping-btn",k="#update_order_shipping_tracking_number",E="#update_order_shipping_current_order_carrier_id",S="#updateCustomerAddressModal",O=".js-update-customer-address-modal-btn",R="#change_order_address_address_type",C="#js-delivery-address-edit-btn",q="#js-invoice-address-edit-btn",j="#order_message_order_message",M=".js-order-messages-container",F="#order_message_message",L=".js-message-change-warning",D="#orderDocumentsTab .count",$="#orderDocumentsTabContent .card-body",A="#orderShippingTab .count",N="#orderShippingTabContent .card-body",G="#view_all_messages_modal",B="#all-messages-list",Z=".js-open-all-messages-btn",H="#orderProductsOriginalPosition",z="#orderProductsModificationPosition",U="#orderProductsPanel",Q="#orderProductsPanelCount",V=".js-order-product-delete-btn",J="#orderProductsTable",W=".order-product-pagination",K="#orderProductsNavPagination",X="#orderProductsTablePagination",Y="#orderProductsTablePaginationNext",ee="#orderProductsTablePaginationPrev",te=".page-item:not(.d-none):not(#orderProductsTablePaginationNext):not(#orderProductsTablePaginationPrev) .page-link",re="#orderProductsTablePagination .page-item.active span",oe="#orderProductsTablePagination .page-item.d-none",se="#orderProductsTablePaginationNumberSelector",ne=e=>`#orderProduct_${e}`,ie=e=>`#editOrderProduct_${e}`,ae="tr.cellProduct",de="tr .cellProductLocation",ce="tr .cellProductRefunded",le="tr:not(.d-none) .cellProductLocation",ue="tr:not(.d-none) .cellProductRefunded",he="#orderProductsTable .order-product-customization",pe=".js-order-product-edit-btn",me=e=>`#orderProduct_${e} .js-order-product-edit-btn`,fe="#addProductBtn",ve=".js-product-action-btn",ge="#add_product_row_add",be="#add_product_row_cancel",_e="#addProductTableRow",xe="#add_product_row_search",ye="#addProductTableRow .dropdown .dropdown-menu",Pe="#add_product_row_product_id",Ie="#add_product_row_tax_rate",we="#addProductCombinations",Te="#addProductCombinationId",ke="#add_product_row_price_tax_excluded",Ee="#add_product_row_price_tax_included",Se="#add_product_row_quantity",Oe="#addProductAvailable",Re="#addProductLocation",Ce="#addProductTotalPrice",qe="#add_product_row_invoice",je="#add_product_row_free_shipping",Me="#addProductNewInvoiceInfo",Fe=".productEditSaveBtn",Le=".productEditCancelBtn",De="#editProductTableRowTemplate",$e=".editProductRow",Ae=".cellProductImg",Ne=".cellProductName",Ge=".cellProductUnitPrice",Be=".cellProductQuantity",Ze=".cellProductAvailableQuantity",He=".cellProductTotalPrice",ze=".editProductPriceTaxExcl",Ue=".editProductPriceTaxIncl",Qe=".editProductInvoice",Ve=".editProductQuantity",Je=".editProductLocation",We=".editProductAvailable",Ke=".editProductTotalPrice",Xe={list:".table.discountList"},Ye={modal:"#product-pack-modal",table:"#product-pack-modal-table tbody",rows:"#product-pack-modal-table tbody tr:not(#template-pack-table-row)",template:"#template-pack-table-row",product:{img:".cell-product-img img",link:".cell-product-name a",name:".cell-product-name .product-name",ref:".cell-product-name .product-reference",supplierRef:".cell-product-name .product-supplier-reference",quantity:".cell-product-quantity",availableQuantity:".cell-product-available-quantity"}},et="#orderProductsTotal",tt="#order-discounts-total-container",rt="#orderDiscountsTotal",ot="#order-shipping-total-container",st="#orderShippingTotal",nt="#orderTaxesTotal",it="#orderTotal",at="#order_hook_tabs",dt={form:'form[name="cancel_product"]',buttons:{abort:"button.cancel-product-element-abort",save:"#cancel_product_save",partialRefund:"button.partial-refund-display",standardRefund:"button.standard-refund-display",returnProduct:"button.return-product-display",cancelProducts:"button.cancel-product-display"},inputs:{quantity:".cancel-product-quantity input",amount:".cancel-product-amount input",selector:".cancel-product-selector input"},table:{cell:".cancel-product-cell",header:"th.cancel-product-element p",actions:"td.cellProductActions, th.product_actions"},checkboxes:{restock:"#cancel_product_restock",creditSlip:"#cancel_product_credit_slip",voucher:"#cancel_product_voucher"},radios:{voucherRefundType:{productPrices:'input[voucher-refund-type="0"]',productPricesVoucherExcluded:'input[voucher-refund-type="1"]',negativeErrorMessage:".voucher-refund-type-negative-error"}},toggle:{partialRefund:".cancel-product-element:not(.hidden):not(.shipping-refund), .cancel-product-amount",standardRefund:".cancel-product-element:not(.hidden):not(.shipping-refund-amount):not(.restock-products), .cancel-product-selector",returnProduct:".cancel-product-element:not(.hidden):not(.shipping-refund-amount), .cancel-product-selector",cancelProducts:".cancel-product-element:not(.hidden):not(.shipping-refund-amount):not(.shipping-refund):not(.restock-products):not(.refund-credit-slip):not(.refund-voucher):not(.voucher-refund-type), .cancel-product-selector"}},ct=".js-print-order-view-page",lt=".js-order-notes-toggle-btn",ut=".js-order-notes-block",ht="#internal_note_note",pt=".js-order-notes-btn",mt="#orderProductsPanel .spinner-order-products-container#orderProductsLoading",{$:ft}=window;class vt{constructor(){this.initOrderShippingUpdateEventHandler()}initOrderShippingUpdateEventHandler(){ft(o).on("click",T,(e=>{const t=ft(e.currentTarget);ft(k).val(t.data("order-tracking-number")),ft(E).val(t.data("order-carrier-id"))}))}}
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
const{$:gt}=window;class bt{constructor(){this.setupListeners()}setupListeners(){this.initShowNoteFormEventHandler(),this.initCloseNoteFormEventHandler(),this.initEnterPaymentEventHandler()}initShowNoteFormEventHandler(){gt(".js-open-invoice-note-btn").on("click",(e=>{e.preventDefault();gt(e.currentTarget).closest("tr").next().removeClass("d-none")}))}initCloseNoteFormEventHandler(){gt(".js-cancel-invoice-note-btn").on("click",(e=>{gt(e.currentTarget).closest("tr").addClass("d-none")}))}initEnterPaymentEventHandler(){gt(".js-enter-payment-btn").on("click",(e=>{const t=gt(e.currentTarget).data("payment-amount");gt(a).get(0).scrollIntoView({behavior:"smooth"}),gt(n).val(t)}))}}var _t=r(2564),xt=r.n(_t);const yt=JSON.parse('{"base_url":"","routes":{"admin_common_notifications":{"tokens":[["text","/common/notifications"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_product_form":{"tokens":[["variable","/","\\\\d+","id"],["text","/sell/catalog/products"]],"defaults":[],"requirements":{"id":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_feature_get_feature_values":{"tokens":[["variable","/","\\\\d+","idFeature"],["text","/sell/catalog/products/features"]],"defaults":{"idFeature":0},"requirements":{"idFeature":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations":{"tokens":[["text","/combinations"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_ids":{"tokens":[["text","/combinations/ids"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_update_combination_from_listing":{"tokens":[["text","/update-combination-from-listing"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_edit_combination":{"tokens":[["text","/edit"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_combinations_bulk_edit_combination":{"tokens":[["text","/combinations/bulk-edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_delete_combination":{"tokens":[["text","/delete"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_combinations_bulk_delete":{"tokens":[["text","/combinations/bulk-delete"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_attribute_groups":{"tokens":[["text","/attribute-groups"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_all_attribute_groups":{"tokens":[["text","/sell/catalog/products-v2/all-attribute-groups"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_generate":{"tokens":[["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2/generate-combinations"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_get_images":{"tokens":[["text","/images"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_v2_add_image":{"tokens":[["text","/sell/catalog/products-v2/images/add"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_update_image":{"tokens":[["text","/update"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_v2_delete_image":{"tokens":[["text","/delete"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_specific_prices_list":{"tokens":[["text","/specific-prices/list"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_specific_prices_create":{"tokens":[["text","/specific-prices/create"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_delete":{"tokens":[["text","/delete"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_v2_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST","PATCH"],"schemes":[]},"admin_products_v2_bulk_enable":{"tokens":[["text","/sell/catalog/products-v2/bulk-enable"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_disable":{"tokens":[["text","/sell/catalog/products-v2/bulk-disable"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_duplicate":{"tokens":[["text","/sell/catalog/products-v2/bulk-duplicate"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_v2_bulk_delete":{"tokens":[["text","/sell/catalog/products-v2/bulk-delete"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST","DELETE"],"schemes":[]},"admin_categories_get_categories_tree":{"tokens":[["text","/sell/catalog/categories/tree"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_cart_rules_search":{"tokens":[["text","/sell/catalog/cart-rules/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_customers_search":{"tokens":[["text","/sell/customers/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_carts":{"tokens":[["text","/carts"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_orders":{"tokens":[["text","/orders"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_addresses_create":{"tokens":[["text","/sell/addresses/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_addresses_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","addressId"],["text","/sell/addresses"]],"defaults":[],"requirements":{"addressId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_order_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","orderId"],["text","/sell/addresses/order"]],"defaults":[],"requirements":{"orderId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_cart_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","cartId"],["text","/sell/addresses/cart"]],"defaults":[],"requirements":{"cartId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_carts_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_create":{"tokens":[["text","/sell/orders/carts/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_addresses":{"tokens":[["text","/addresses"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_carrier":{"tokens":[["text","/carrier"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_currency":{"tokens":[["text","/currency"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_language":{"tokens":[["text","/language"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_set_delivery_settings":{"tokens":[["text","/rules/delivery-settings"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_cart_rule":{"tokens":[["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_cart_rule":{"tokens":[["text","/delete"],["variable","/","[^/]++","cartRuleId"],["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_price":{"tokens":[["text","/price"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_quantity":{"tokens":[["text","/quantity"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_product":{"tokens":[["text","/delete-product"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_place":{"tokens":[["text","/sell/orders/place"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_orders_duplicate_cart":{"tokens":[["text","/duplicate-cart"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_update_product":{"tokens":[["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_partial_refund":{"tokens":[["text","/partial-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_standard_refund":{"tokens":[["text","/standard-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_return_product":{"tokens":[["text","/return-product"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_send_process_order_email":{"tokens":[["text","/sell/orders/process-order-email"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_delete_product":{"tokens":[["text","/delete"],["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_get_discounts":{"tokens":[["text","/discounts"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_prices":{"tokens":[["text","/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_payments":{"tokens":[["text","/payments"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_products":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_invoices":{"tokens":[["text","/invoices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_documents":{"tokens":[["text","/documents"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_shipping":{"tokens":[["text","/shipping"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_cancellation":{"tokens":[["text","/cancellation"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_configure_product_pagination":{"tokens":[["text","/sell/orders/configure-product-pagination"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_product_prices":{"tokens":[["text","/products/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_products_search":{"tokens":[["text","/sell/orders/products/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_attachment_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","attachmentId"],["text","/sell/attachments"]],"defaults":[],"requirements":{"attachmentId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_search":{"tokens":[["variable","/","[^/]++","searchPhrase"],["text","/sell/attachments/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_shops_search":{"tokens":[["variable","/","[^/]++","searchTerm"],["text","/configure/advanced/shops/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]}},"prefix":"","host":"localhost","port":"","scheme":"http","locale":""}'),{$:Pt}=window;class It{constructor(){return window.prestashop&&window.prestashop.customRoutes&&Object.assign(yt.routes,window.prestashop.customRoutes),xt().setData(yt),xt().setBaseUrl(Pt(document).find("body").data("base-url")),this}generate(e,t={}){const r=Object.assign(t,{_token:Pt(document).find("body").data("token")});return xt().generate(e,r)}}
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
const wt=new(r(7187).EventEmitter),Tt="productDeletedFromOrder",kt="productAddedToOrder",Et="productUpdated",St="productEditionCanceled",Ot="productListPaginated",Rt="productListNumberPerPage",{$:Ct}=window;class qt{constructor(){this.router=new It}handleDeleteProductEvent(e){e.preventDefault();const t=Ct(e.currentTarget);window.confirm(t.data("deleteMessage"))&&(t.pstooltip("dispose"),t.prop("disabled",!0),this.deleteProduct(t.data("orderId"),t.data("orderDetailId")))}deleteProduct(e,t){Ct.ajax(this.router.generate("admin_orders_delete_product",{orderId:e,orderDetailId:t}),{method:"POST"}).then((()=>{wt.emit(Tt,{oldOrderDetailId:t,orderId:e})}),(e=>{e.responseJSON&&e.responseJSON.message&&Ct.growl.error({message:e.responseJSON.message})}))}}
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
const{$:jt}=window;class Mt{constructor(){this.router=new It}refresh(e){jt.ajax(this.router.generate("admin_orders_get_discounts",{orderId:e})).then((e=>{jt(Xe.list).replaceWith(e)}))}}
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
class Ft{calculateTaxExcluded(e,t,r){let o=e;(o<0||Number.isNaN(o))&&(o=0);const s=t/100+1;return window.ps_round(o/s,r)}calculateTaxIncluded(e,t,r){let o=e;(o<0||Number.isNaN(o))&&(o=0);const s=t/100+1;return window.ps_round(o*s,r)}calculateTotalPrice(e,t,r){return window.ps_round(t*e,r)}}var Lt=r(9567),Dt=Object.defineProperty,$t=Object.getOwnPropertySymbols,At=Object.prototype.hasOwnProperty,Nt=Object.prototype.propertyIsEnumerable,Gt=(e,t,r)=>t in e?Dt(e,t,{enumerable:!0,configurable:!0,writable:!0,value:r}):e[t]=r,Bt=(e,t)=>{for(var r in t||(t={}))At.call(t,r)&&Gt(e,r,t[r]);if($t)for(var r of $t(t))Nt.call(t,r)&&Gt(e,r,t[r]);return e};
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
class Zt{constructor(e){const t=Bt({id:"confirm-modal",closable:!1},e);this.buildModalContainer(t)}buildModalContainer(e){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=e.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),e.dialogStyle&&Object.keys(e.dialogStyle).forEach((t=>{this.dialog.style[t]=e.dialogStyle[t]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),e.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=e.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class Ht{constructor(e){const t=Bt({id:"confirm-modal",closable:!1,dialogStyle:{}},e);this.initContainer(t)}initContainer(e){this.modal||(this.modal=new Zt(e)),this.$modal=Lt(this.modal.container);const{id:t,closable:r}=e;this.$modal.modal({backdrop:!!r||"static",keyboard:void 0===r||r,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const r=document.querySelector(`#${t}`);r&&r.remove(),e.closeCallback&&e.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(e){this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=e}render(e){this.modal.message.innerHTML=e}show(){this.$modal.modal("show")}hide(){this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")}))}}
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
function zt(e){return void 0===e}var Ut=Object.defineProperty,Qt=Object.getOwnPropertySymbols,Vt=Object.prototype.hasOwnProperty,Jt=Object.prototype.propertyIsEnumerable,Wt=(e,t,r)=>t in e?Ut(e,t,{enumerable:!0,configurable:!0,writable:!0,value:r}):e[t]=r;
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
class Kt extends Zt{constructor(e){super(e)}buildModalContainer(e){super.buildModalContainer(e),this.message.classList.add("confirm-message"),this.message.innerHTML=e.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=e.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",e.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=e.confirmButtonLabel,this.footer.append(this.closeButton,...e.customButtons,this.confirmButton),this.content.append(this.footer)}}class Xt extends Ht{constructor(e,t,r){var o;let s;s=zt(e.confirmCallback)?zt(t)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:t:e.confirmCallback;super(((e,t)=>{for(var r in t||(t={}))Vt.call(t,r)&&Wt(e,r,t[r]);if(Qt)for(var r of Qt(t))Jt.call(t,r)&&Wt(e,r,t[r]);return e})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:e.confirmTitle,dialogStyle:{},confirmCallback:s,closeCallback:null!=(o=e.closeCallback)?o:r},e))}initContainer(e){this.modal=new Kt(e),this.modal.confirmButton.addEventListener("click",e.confirmCallback),super.initContainer(e)}}var Yt=function(){if("undefined"!=typeof Map)return Map;function e(e,t){var r=-1;return e.some((function(e,o){return e[0]===t&&(r=o,!0)})),r}return function(){function t(){this.__entries__=[]}return Object.defineProperty(t.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),t.prototype.get=function(t){var r=e(this.__entries__,t),o=this.__entries__[r];return o&&o[1]},t.prototype.set=function(t,r){var o=e(this.__entries__,t);~o?this.__entries__[o][1]=r:this.__entries__.push([t,r])},t.prototype.delete=function(t){var r=this.__entries__,o=e(r,t);~o&&r.splice(o,1)},t.prototype.has=function(t){return!!~e(this.__entries__,t)},t.prototype.clear=function(){this.__entries__.splice(0)},t.prototype.forEach=function(e,t){void 0===t&&(t=null);for(var r=0,o=this.__entries__;r<o.length;r++){var s=o[r];e.call(t,s[1],s[0])}},t}()}(),er="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,tr=void 0!==r.g&&r.g.Math===Math?r.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),rr="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(tr):function(e){return setTimeout((function(){return e(Date.now())}),1e3/60)};var or=["top","right","bottom","left","width","height","size","weight"],sr="undefined"!=typeof MutationObserver,nr=function(){function e(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(e,t){var r=!1,o=!1,s=0;function n(){r&&(r=!1,e()),o&&a()}function i(){rr(n)}function a(){var e=Date.now();if(r){if(e-s<2)return;o=!0}else r=!0,o=!1,setTimeout(i,t);s=e}return a}(this.refresh.bind(this),20)}return e.prototype.addObserver=function(e){~this.observers_.indexOf(e)||this.observers_.push(e),this.connected_||this.connect_()},e.prototype.removeObserver=function(e){var t=this.observers_,r=t.indexOf(e);~r&&t.splice(r,1),!t.length&&this.connected_&&this.disconnect_()},e.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},e.prototype.updateObservers_=function(){var e=this.observers_.filter((function(e){return e.gatherActive(),e.hasActive()}));return e.forEach((function(e){return e.broadcastActive()})),e.length>0},e.prototype.connect_=function(){er&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),sr?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},e.prototype.disconnect_=function(){er&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},e.prototype.onTransitionEnd_=function(e){var t=e.propertyName,r=void 0===t?"":t;or.some((function(e){return!!~r.indexOf(e)}))&&this.refresh()},e.getInstance=function(){return this.instance_||(this.instance_=new e),this.instance_},e.instance_=null,e}(),ir=function(e,t){for(var r=0,o=Object.keys(t);r<o.length;r++){var s=o[r];Object.defineProperty(e,s,{value:t[s],enumerable:!1,writable:!1,configurable:!0})}return e},ar=function(e){return e&&e.ownerDocument&&e.ownerDocument.defaultView||tr},dr=mr(0,0,0,0);function cr(e){return parseFloat(e)||0}function lr(e){for(var t=[],r=1;r<arguments.length;r++)t[r-1]=arguments[r];return t.reduce((function(t,r){return t+cr(e["border-"+r+"-width"])}),0)}function ur(e){var t=e.clientWidth,r=e.clientHeight;if(!t&&!r)return dr;var o=ar(e).getComputedStyle(e),s=function(e){for(var t={},r=0,o=["top","right","bottom","left"];r<o.length;r++){var s=o[r],n=e["padding-"+s];t[s]=cr(n)}return t}(o),n=s.left+s.right,i=s.top+s.bottom,a=cr(o.width),d=cr(o.height);if("border-box"===o.boxSizing&&(Math.round(a+n)!==t&&(a-=lr(o,"left","right")+n),Math.round(d+i)!==r&&(d-=lr(o,"top","bottom")+i)),!function(e){return e===ar(e).document.documentElement}(e)){var c=Math.round(a+n)-t,l=Math.round(d+i)-r;1!==Math.abs(c)&&(a-=c),1!==Math.abs(l)&&(d-=l)}return mr(s.left,s.top,a,d)}var hr="undefined"!=typeof SVGGraphicsElement?function(e){return e instanceof ar(e).SVGGraphicsElement}:function(e){return e instanceof ar(e).SVGElement&&"function"==typeof e.getBBox};function pr(e){return er?hr(e)?function(e){var t=e.getBBox();return mr(0,0,t.width,t.height)}(e):ur(e):dr}function mr(e,t,r,o){return{x:e,y:t,width:r,height:o}}var fr=function(){function e(e){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=mr(0,0,0,0),this.target=e}return e.prototype.isActive=function(){var e=pr(this.target);return this.contentRect_=e,e.width!==this.broadcastWidth||e.height!==this.broadcastHeight},e.prototype.broadcastRect=function(){var e=this.contentRect_;return this.broadcastWidth=e.width,this.broadcastHeight=e.height,e},e}(),vr=function(e,t){var r,o,s,n,i,a,d,c=(o=(r=t).x,s=r.y,n=r.width,i=r.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,d=Object.create(a.prototype),ir(d,{x:o,y:s,width:n,height:i,top:s,right:o+n,bottom:i+s,left:o}),d);ir(this,{target:e,contentRect:c})},gr=function(){function e(e,t,r){if(this.activeObservations_=[],this.observations_=new Yt,"function"!=typeof e)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=e,this.controller_=t,this.callbackCtx_=r}return e.prototype.observe=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof ar(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)||(t.set(e,new fr(e)),this.controller_.addObserver(this),this.controller_.refresh())}},e.prototype.unobserve=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof ar(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)&&(t.delete(e),t.size||this.controller_.removeObserver(this))}},e.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},e.prototype.gatherActive=function(){var e=this;this.clearActive(),this.observations_.forEach((function(t){t.isActive()&&e.activeObservations_.push(t)}))},e.prototype.broadcastActive=function(){if(this.hasActive()){var e=this.callbackCtx_,t=this.activeObservations_.map((function(e){return new vr(e.target,e.broadcastRect())}));this.callback_.call(e,t,e),this.clearActive()}},e.prototype.clearActive=function(){this.activeObservations_.splice(0)},e.prototype.hasActive=function(){return this.activeObservations_.length>0},e}(),br="undefined"!=typeof WeakMap?new WeakMap:new Yt,_r=function e(t){if(!(this instanceof e))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var r=nr.getInstance(),o=new gr(t,r,this);br.set(this,o)};["observe","unobserve","disconnect"].forEach((function(e){_r.prototype[e]=function(){var t;return(t=br.get(this))[e].apply(t,arguments)}}));void 0!==tr.ResizeObserver&&tr.ResizeObserver;const xr=class extends Event{constructor(e,t={}){super(xr.parentWindowEvent),this.eventName=e,this.eventParameters=t}get name(){return this.eventName}get parameters(){return this.eventParameters}};xr.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const yr=Xt,{$:Pr}=window;class Ir{constructor(){this.router=new It}refresh(e){Pr.getJSON(this.router.generate("admin_orders_get_prices",{orderId:e})).then((e=>{Pr(it).text(e.orderTotalFormatted),Pr(rt).text(`-${e.discountsAmountFormatted}`),Pr(tt).toggleClass("d-none",!e.discountsAmountDisplayed),Pr(et).text(e.productsTotalFormatted),Pr(st).text(e.shippingTotalFormatted),Pr(ot).toggleClass("d-none",!e.shippingTotalDisplayed),Pr(nt).text(e.taxesTotalFormatted)}))}refreshProductPrices(e){Pr.getJSON(this.router.generate("admin_orders_product_prices",{orderId:e})).then((e=>{e.forEach((e=>{const t=ne(e.orderDetailId);let r=Pr(e.quantity);e.quantity>1&&(r=r.wrap('<span class="badge badge-secondary rounded-circle"></span>')),Pr(`${t} ${Ge}`).text(e.unitPrice),Pr(`${t} ${Be}`).html(r.html()),Pr(`${t} ${Ze}`).text(e.availableQuantity),Pr(`${t} ${He}`).text(e.totalPrice);const o=Pr(me(e.orderDetailId));o.data("product-price-tax-incl",e.unitPriceTaxInclRaw),o.data("product-price-tax-excl",e.unitPriceTaxExclRaw),o.data("product-quantity",e.quantity)}))}))}checkOtherProductPricesMatch(e,t,r,o,s){const n=document.querySelectorAll("tr.cellProduct"),i=Number(t),a=Number(r),d=Number(e);let c=!1,l=!1;return n.forEach((e=>{const t=Pr(e).attr("id");if(s&&t===`orderProduct_${s}`)return;const r=Pr(`#${t} ${pe}`),n=Number(r.data("order-invoice-id")),u=Number(r.data("product-id")),h=Number(r.data("combination-id"));u===i&&h===a&&d!==Number(r.data("product-price-tax-incl"))&&(!o||o&&n&&o===n?l=!0:c=!0)})),c?"invoice":l?"product":null}}
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
const{$:wr}=window;class Tr{constructor(e){this.router=new It,this.orderDetailId=e,this.productRow=wr(`#orderProduct_${this.orderDetailId}`),this.product={},this.currencyPrecision=wr(J).data("currencyPrecision"),this.priceTaxCalculator=new Ft,this.productEditSaveBtn=wr(Fe),this.quantityInput=wr(Ve),this.orderPricesRefresher=new Ir,this.availableText=null,this.isOrderTaxIncluded=null,this.productEditInvoiceSelect=null,this.priceTaxIncludedInput=null,this.taxExcluded=null,this.taxIncluded=null,this.taxRate=null,this.priceTaxExcludedInput=null,this.productEditCancelBtn=null,this.quantity=null,this.priceTotalText=null,this.initialTotal=null,this.productRowEdit=null,this.productEditImage=null,this.productEditName=null,this.locationText=null}setupListener(){this.quantityInput.on("change keyup",(e=>{var t;const r=e.target,o=Number(r.value),s=parseInt(wr(e.currentTarget).data("availableQuantity"),10)-(o-parseInt(this.quantityInput.data("previousQuantity"),10)),n=null==(t=this.availableText)?void 0:t.data("availableOutOfStock");this.quantity=o,this.availableText&&(this.availableText.text(s),this.availableText.toggleClass("text-danger font-weight-bold",s<0)),this.updateTotal();const i=o<=0||s<0&&!n;this.productEditSaveBtn.prop("disabled",i)})),this.productEditInvoiceSelect&&this.productEditInvoiceSelect.on("change",(()=>{this.productEditSaveBtn.prop("disabled",!1)})),this.priceTaxIncludedInput&&this.priceTaxIncludedInput.on("change keyup",(e=>{const t=e.target;this.taxIncluded=parseFloat(t.value),this.taxExcluded=this.priceTaxCalculator.calculateTaxExcluded(this.taxIncluded,this.taxRate,this.currencyPrecision),this.priceTaxExcludedInput&&this.priceTaxExcludedInput.val(this.taxExcluded),this.updateTotal()})),this.priceTaxExcludedInput&&this.priceTaxExcludedInput.on("change keyup",(e=>{const t=e.target;this.taxExcluded=parseFloat(t.value),this.taxIncluded=this.priceTaxCalculator.calculateTaxIncluded(this.taxExcluded,this.taxRate,this.currencyPrecision),this.priceTaxIncludedInput&&this.priceTaxIncludedInput.val(this.taxIncluded),this.updateTotal()})),this.productEditSaveBtn.on("click",(e=>{const t=wr(e.currentTarget);window.confirm(t.data("updateMessage"))&&(t.prop("disabled",!0),this.handleEditProductWithConfirmationModal(e))})),this.productEditCancelBtn&&this.productEditCancelBtn.on("click",(()=>{wt.emit(St,{orderDetailId:this.orderDetailId})}))}updateTotal(){const e=this.priceTaxCalculator.calculateTotalPrice(this.quantity,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision);this.priceTotalText&&this.priceTotalText.html(e),this.productEditSaveBtn.prop("disabled",e===this.initialTotal)}displayProduct(e){this.productRowEdit=wr(De).clone(!0),this.productRowEdit.attr("id",`editOrderProduct_${this.orderDetailId}`),this.productRowEdit.find("*[id]").each((function(){wr(this).removeAttr("id")})),this.productEditSaveBtn=this.productRowEdit.find(Fe),this.productEditCancelBtn=this.productRowEdit.find(Le),this.productEditInvoiceSelect=this.productRowEdit.find(Qe),this.productEditImage=this.productRowEdit.find(Ae),this.productEditName=this.productRowEdit.find(Ne),this.priceTaxIncludedInput=this.productRowEdit.find(Ue),this.priceTaxExcludedInput=this.productRowEdit.find(ze),this.quantityInput=this.productRowEdit.find(Ve),this.locationText=this.productRowEdit.find(Je),this.availableText=this.productRowEdit.find(We),this.priceTotalText=this.productRowEdit.find(Ke),this.priceTaxExcludedInput.val(window.ps_round(e.price_tax_excl,this.currencyPrecision)),this.priceTaxIncludedInput.val(window.ps_round(e.price_tax_incl,this.currencyPrecision)),this.quantityInput.val(e.quantity).data("availableQuantity",e.availableQuantity).data("previousQuantity",e.quantity),this.availableText.data("availableOutOfStock",e.availableOutOfStock),e.orderInvoiceId&&this.productEditInvoiceSelect.val(e.orderInvoiceId),this.taxRate=e.tax_rate,this.initialTotal=this.priceTaxCalculator.calculateTotalPrice(e.quantity,e.isOrderTaxIncluded?e.price_tax_incl:e.price_tax_excl,this.currencyPrecision),this.isOrderTaxIncluded=e.isOrderTaxIncluded,this.quantity=e.quantity,this.taxIncluded=e.price_tax_incl,this.taxExcluded=e.price_tax_excl,this.productEditImage.html(this.productRow.find(Ae).html()),this.productEditName.html(this.productRow.find(Ne).html()),this.locationText.html(e.location),this.availableText.html(e.availableQuantity),this.priceTotalText.html(this.initialTotal),this.productRow.addClass("d-none").after(this.productRowEdit.removeClass("d-none")),this.setupListener()}handleEditProductWithConfirmationModal(e){const t=wr(`#orderProduct_${this.orderDetailId} ${pe}`),r=t.data("product-id"),o=t.data("combination-id"),s=t.data("order-invoice-id");let n;if(this.priceTaxIncludedInput&&(n=this.orderPricesRefresher.checkOtherProductPricesMatch(this.priceTaxIncludedInput.val(),r,o,s,this.orderDetailId)),null===n)return void this.editProduct(wr(e.currentTarget).data("orderId"),this.orderDetailId);const i="product"===n?this.priceTaxExcludedInput:this.productEditInvoiceSelect;if(i){new yr({id:"modal-confirm-new-price",confirmTitle:i.data("modal-edit-price-title"),confirmMessage:i.data("modal-edit-price-body"),confirmButtonLabel:i.data("modal-edit-price-apply"),closeButtonLabel:i.data("modal-edit-price-cancel")},(()=>{this.editProduct(wr(e.currentTarget).data("orderId"),this.orderDetailId)})).show()}}editProduct(e,t){var r,o,s;const n={price_tax_incl:null==(r=this.priceTaxIncludedInput)?void 0:r.val(),price_tax_excl:null==(o=this.priceTaxExcludedInput)?void 0:o.val(),quantity:this.quantityInput.val(),invoice:null==(s=this.productEditInvoiceSelect)?void 0:s.val()};wr.ajax({url:this.router.generate("admin_orders_update_product",{orderId:e,orderDetailId:t}),method:"POST",data:n}).then((()=>{wt.emit(Et,{orderId:e,orderDetailId:t})}),(e=>{e.responseJSON&&e.responseJSON.message&&wr.growl.error({message:e.responseJSON.message})}))}}
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
const{$:kr}=window;class Er{constructor(){this.router=new It}addOrUpdateProductToList(e,t){e.length>0?e.html(kr(t).html()):kr(_e).before(kr(t).hide().fadeIn())}updateNumProducts(e){kr(Q).html(e)}editProductFromList(e,t,r,o,s,n,i,a,d,c){new Tr(e).displayProduct({price_tax_excl:o,price_tax_incl:r,tax_rate:s,quantity:t,location:n,availableQuantity:i,availableOutOfStock:a,orderInvoiceId:d,isOrderTaxIncluded:c}),kr(ge).addClass("d-none"),kr(_e).addClass("d-none")}moveProductsPanelToModificationPosition(e="body"){kr(ve).addClass("d-none"),kr(`${ge}, ${_e}`).removeClass("d-none"),this.moveProductPanelToTop(e)}moveProductsPanelToRefundPosition(){this.resetAllEditRows(),kr(`${ge}, ${_e}, ${ve}`).addClass("d-none"),this.moveProductPanelToTop()}moveProductPanelToTop(e="body"){const t=kr(z);if(t.find(U).length>0)return;kr(U).detach().appendTo(t),t.removeClass("d-none"),this.toggleColumn(de),this.toggleColumn(ce);kr(J).find('tr[id^="orderProduct_"]').removeClass("d-none"),kr(W).addClass("d-none");const r=kr(e).offset(),o=kr(".header-toolbar").height();if(r&&o){const e=r.top-o-100;kr("html,body").animate({scrollTop:e},"slow")}}moveProductPanelToOriginalPosition(){kr(Me).addClass("d-none"),kr(z).addClass("d-none"),kr(U).detach().appendTo(H),kr(W).removeClass("d-none"),kr(ve).removeClass("d-none"),kr(`${ge}, ${_e}`).addClass("d-none"),this.paginate(1)}resetAddRow(){kr(Pe).val(""),kr(xe).val(""),kr(we).addClass("d-none"),kr(Te).val(""),kr(Te).prop("disabled",!1),kr(ke).val(""),kr(Ee).val(""),kr(Se).val(""),kr(Oe).html(""),kr(Re).html(""),kr(Me).addClass("d-none"),kr(ge).prop("disabled",!0)}resetAllEditRows(){kr(pe).each(((e,t)=>{this.resetEditRow(kr(t).data("orderDetailId"))}))}resetEditRow(e){const t=kr(ne(e));kr(ie(e)).remove(),t.removeClass("d-none")}paginate(e){const t=kr(J).find('tr[id^="orderProduct_"]'),r=kr(he),o=kr(X),s=parseInt(o.data("numPerPage"),10),n=Math.ceil(t.length/s),i=Math.max(1,Math.min(e,n));this.paginateUpdateControls(i),t.addClass("d-none"),r.addClass("d-none");const a=i*s;for(let e=(i-1)*s+1-1;e<Math.min(a,t.length);e+=1)kr(t[e]).removeClass("d-none");r.each((function(){kr(this).prev().hasClass("d-none")||kr(this).removeClass("d-none")})),kr($e).not(De).remove(),this.toggleColumn(le),this.toggleColumn(ue)}paginateUpdateControls(e){const t=kr(X).find("li.page-item").length-3;kr(X).find(".active").removeClass("active"),kr(X).find(`li:has(> [data-page="${e}"])`).addClass("active"),kr(ee).removeClass("disabled"),1===e&&kr(ee).addClass("disabled"),kr(Y).removeClass("disabled"),e===t&&kr(Y).addClass("disabled"),this.togglePaginationControls()}updateNumPerPage(e){kr(X).data("numPerPage",e),this.updatePaginationControls()}togglePaginationControls(){const e=kr(X).find("li.page-item").length-3;kr(K).toggleClass("d-none",e<=1)}toggleProductAddNewInvoiceInfo(){kr(Me).toggleClass("d-none",0!==parseInt(kr(qe).val(),10))}toggleColumn(e,t=null){let r=!1;null===t?kr(e).filter("td").each((function(){if(""!==kr(this).html())return r=!0,!1})):r=t,kr(e).toggleClass("d-none",!r)}updatePaginationControls(){const e=kr(X),t=e.data("numPerPage"),r=kr(J).find('tr[id^="orderProduct_"]'),o=Math.ceil(r.length/t);e.data("numPages",o);const s=kr(oe);kr(X).find("li:has(> [data-page])").remove(),kr(Y).before(s);for(let e=1;e<=o;e+=1){const t=s.clone();t.find("span").attr("data-page",e),t.find("span").html(e),s.before(t.removeClass("d-none"))}this.togglePaginationControls()}}
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
const{$:Sr}=window;class Or{constructor(){this.router=new It}refresh(e){Sr.ajax(this.router.generate("admin_orders_get_payments",{orderId:e})).then((e=>{Sr(d).remove(),Sr(`${a} .card-body`).prepend(e)}),(e=>{e.responseJSON&&e.responseJSON.message&&Sr.growl.error({message:e.responseJSON.message})}))}}
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
const{$:Rr}=window;class Cr{constructor(){this.router=new It}refresh(e){Rr.getJSON(this.router.generate("admin_orders_get_shipping",{orderId:e})).then((e=>{Rr(A).text(e.total),Rr(N).html(e.html)}))}}
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
const{$:qr}=window;class jr{constructor(){this.router=new It}refresh(e){qr.getJSON(this.router.generate("admin_orders_get_invoices",{orderId:e})).then((e=>{if(!e||!e.invoices||Object.keys(e.invoices).length<=0)return;const t=qr(i),r=qr(qe).find("optgroup:first"),o=qr(Qe),s=qr(m);r.empty(),t.empty(),o.empty(),s.empty(),Object.keys(e.invoices).forEach((n=>{const i=e.invoices[n],a=n.split(" - ")[0];r.append(`<option value="${i}">${a}</option>`),t.append(`<option value="${i}">${a}</option>`),o.append(`<option value="${i}">${a}</option>`),s.append(`<option value="${i}">${n}</option>`)}));const n=document.querySelector(qe);n&&(n.selectedIndex=0)}))}}var Mr=r(4902);
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
const{$:Fr}=window;class Lr{constructor(){this.router=new It,this.cancelProductForm=Fr(dt.form),this.orderId=this.cancelProductForm.data("orderId"),this.orderDelivered=1===parseInt(this.cancelProductForm.data("isDelivered"),10),this.isTaxIncluded=1===parseInt(this.cancelProductForm.data("isTaxIncluded"),10),this.discountsAmount=parseFloat(this.cancelProductForm.data("discountsAmount")),this.currencyFormatter=Mr.NumberFormatter.build(this.cancelProductForm.data("priceSpecification")),this.useAmountInputs=!0,this.listenForInputs()}showPartialRefund(){this.hideCancelElements(),Fr(dt.toggle.partialRefund).show(),this.useAmountInputs=!0,this.initForm(Fr(dt.buttons.save).data("partialRefundLabel"),this.router.generate("admin_orders_partial_refund",{orderId:this.orderId}),"partial-refund")}showStandardRefund(){this.hideCancelElements(),Fr(dt.toggle.standardRefund).show(),this.useAmountInputs=!1,this.initForm(Fr(dt.buttons.save).data("standardRefundLabel"),this.router.generate("admin_orders_standard_refund",{orderId:this.orderId}),"standard-refund")}showReturnProduct(){this.hideCancelElements(),Fr(dt.toggle.returnProduct).show(),this.useAmountInputs=!1,this.initForm(Fr(dt.buttons.save).data("returnProductLabel"),this.router.generate("admin_orders_return_product",{orderId:this.orderId}),"return-product")}hideRefund(){this.hideCancelElements(),Fr(dt.table.actions).show()}hideCancelElements(){Fr(dt.toggle.standardRefund).hide(),Fr(dt.toggle.partialRefund).hide(),Fr(dt.toggle.returnProduct).hide(),Fr(dt.table.actions).hide()}initForm(e,t,r){this.updateVoucherRefund(),this.cancelProductForm.prop("action",t),this.cancelProductForm.removeClass("standard-refund partial-refund return-product cancel-product").addClass(r),Fr(dt.buttons.save).html(e),Fr(dt.table.header).html(e),Fr(dt.checkboxes.restock).prop("checked",this.orderDelivered),Fr(dt.checkboxes.creditSlip).prop("checked",!0),Fr(dt.checkboxes.voucher).prop("checked",!1)}listenForInputs(){Fr(document).on("change",dt.inputs.quantity,(e=>{const t=Fr(e.target),r=t.parents(dt.table.cell).find(dt.inputs.amount),o=parseInt(t.val(),10);if(o<=0)return r.val(0),void this.updateVoucherRefund();const s=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",n=parseFloat(t.data(s)),i=parseFloat(t.data("amountRefundable")),a=n*o<i?n*o:i,d=parseFloat(r.val());this.useAmountInputs&&this.updateAmountInput(t),(""===r.val()||0===d||d>a)&&(r.val(a),this.updateVoucherRefund())})),Fr(document).on("change",dt.inputs.amount,(()=>{this.updateVoucherRefund()})),Fr(document).on("change",dt.inputs.selector,(e=>{const t=Fr(e.target),r=t.parents(dt.table.cell).find(dt.inputs.quantity),o=parseInt(r.data("quantityRefundable"),10),s=parseInt(r.val(),10);t.is(":checked")?(Number.isNaN(s)||0===s)&&r.val(o):r.val(0),this.updateVoucherRefund()}))}updateAmountInput(e){const t=e.parents(dt.table.cell).find(dt.inputs.amount),r=parseInt(e.val(),10);if(r<=0)return void t.val(0);const o=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",s=parseFloat(e.data(o)),n=parseFloat(e.data("amountRefundable")),i=s*r<n?s*r:n,a=parseFloat(t.val());(""===t.val()||0===a||a>i)&&t.val(i)}getRefundAmount(){let e=0;return this.useAmountInputs?Fr(dt.inputs.amount).each(((t,r)=>{const o=parseFloat(r.value);e+=Number.isNaN(o)?0:o})):Fr(dt.inputs.quantity).each(((t,r)=>{const o=Fr(r),s=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",n=parseFloat(o.data(s)),i=parseInt(o.val(),10);e+=i*n})),e}updateVoucherRefund(){const e=this.getRefundAmount();this.updateVoucherRefundTypeLabel(Fr(dt.radios.voucherRefundType.productPrices),e);const t=e-this.discountsAmount;this.updateVoucherRefundTypeLabel(Fr(dt.radios.voucherRefundType.productPricesVoucherExcluded),t),t<0?(Fr(dt.radios.voucherRefundType.productPricesVoucherExcluded).prop("checked",!1).prop("disabled",!0),Fr(dt.radios.voucherRefundType.productPrices).prop("checked",!0),Fr(dt.radios.voucherRefundType.negativeErrorMessage).show()):(Fr(dt.radios.voucherRefundType.productPricesVoucherExcluded).prop("disabled",!1),Fr(dt.radios.voucherRefundType.negativeErrorMessage).hide())}updateVoucherRefundTypeLabel(e,t){var r;const o=e.data("defaultLabel"),s=e.parents("label"),n=this.currencyFormatter.format(t),i=null==(r=null==s?void 0:s.get(0))?void 0:r.lastChild;i&&(i.nodeValue=`\n      ${o} ${n}`)}showCancelProductForm(){const e=this.router.generate("admin_orders_cancellation",{orderId:this.orderId});this.initForm(Fr(dt.buttons.save).data("cancelLabel"),e,"cancel-product"),this.hideCancelElements(),Fr(dt.toggle.cancelProducts).show()}}
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
const{$:Dr}=window;class $r{constructor(){this.router=new It,this.invoiceNoteManager=new bt}refresh(e){Dr.getJSON(this.router.generate("admin_orders_get_documents",{orderId:e})).then((e=>{Dr(D).text(e.total),Dr($).html(e.html),this.invoiceNoteManager.setupListeners()}))}}
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
const{$:Ar}=window;class Nr{constructor(){this.orderDiscountsRefresher=new Mt,this.orderProductManager=new qt,this.orderProductRenderer=new Er,this.orderPricesRefresher=new Ir,this.orderPaymentsRefresher=new Or,this.orderShippingRefresher=new Cr,this.orderDocumentsRefresher=new $r,this.orderInvoicesRefresher=new jr,this.orderProductCancel=new Lr,this.router=new It,this.listenToEvents()}listenToEvents(){Ar(q).fancybox({type:"iframe",width:"90%",height:"90%"}),Ar(C).fancybox({type:"iframe",width:"90%",height:"90%"}),wt.on(Tt,(e=>{this.orderPricesRefresher.refresh(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.refreshProductsList(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId)})),wt.on(St,(e=>{this.orderProductRenderer.resetEditRow(e.orderDetailId);Ar($e).not(De).length>0||this.orderProductRenderer.moveProductPanelToOriginalPosition()})),wt.on(Et,(e=>{this.orderProductRenderer.resetEditRow(e.orderDetailId),this.orderPricesRefresher.refresh(e.orderId),this.orderPricesRefresher.refreshProductPrices(e.orderId),this.refreshProductsList(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderInvoicesRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId),this.listenForProductDelete(),this.listenForProductEdit(),this.resetToolTips();Ar($e).not(De).length>0||this.orderProductRenderer.moveProductPanelToOriginalPosition()})),wt.on(kt,(e=>{this.orderProductRenderer.resetAddRow(),this.orderPricesRefresher.refreshProductPrices(e.orderId),this.orderPricesRefresher.refresh(e.orderId),this.refreshProductsList(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderInvoicesRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId),this.orderProductRenderer.moveProductPanelToOriginalPosition()}))}listenForProductDelete(){Ar(V).off("click").on("click",(e=>this.orderProductManager.handleDeleteProductEvent(e)))}resetToolTips(){Ar(pe).pstooltip(),Ar(V).pstooltip()}listenForProductEdit(){Ar(pe).off("click").on("click",(e=>{const t=Ar(e.currentTarget);this.orderProductRenderer.moveProductsPanelToModificationPosition(),this.orderProductRenderer.editProductFromList(t.data("orderDetailId"),t.data("productQuantity"),t.data("productPriceTaxIncl"),t.data("productPriceTaxExcl"),t.data("taxRate"),t.data("location"),t.data("availableQuantity"),t.data("availableOutOfStock"),t.data("orderInvoiceId"),t.data("isOrderTaxIncluded"))}))}listenForProductPack(){Ar(Ye.modal).on("show.bs.modal",(e=>{const t=Ar(e.relatedTarget).data("packItems");Ar(Ye.rows).remove(),t.forEach((e=>{const t=Ar(Ye.template).clone();t.attr("id",`productpack_${e.id}`).removeClass("d-none"),t.find(Ye.product.img).attr("src",e.imagePath),t.find(Ye.product.name).html(e.name),t.find(Ye.product.link).attr("href",this.router.generate("admin_product_form",{id:e.id})),""!==e.reference?t.find(Ye.product.ref).append(e.reference):t.find(Ye.product.ref).remove(),""!==e.supplierReference?t.find(Ye.product.supplierRef).append(e.supplierReference):t.find(Ye.product.supplierRef).remove(),e.quantity>1?t.find(`${Ye.product.quantity} span`).html(e.quantity):t.find(Ye.product.quantity).html(e.quantity),t.find(Ye.product.availableQuantity).html(e.availableQuantity),Ar(Ye.template).before(t)}))}))}listenForProductAdd(){Ar(fe).on("click",(()=>{this.orderProductRenderer.toggleProductAddNewInvoiceInfo(),this.orderProductRenderer.moveProductsPanelToModificationPosition(xe)})),Ar(be).on("click",(()=>this.orderProductRenderer.moveProductPanelToOriginalPosition()))}listenForProductPagination(){Ar(X).on("click",te,(e=>{e.preventDefault();const t=Ar(e.currentTarget);wt.emit(Ot,{numPage:t.data("page")})})),Ar(Y).on("click",(e=>{e.preventDefault();if(Ar(e.currentTarget).hasClass("disabled"))return;const t=this.getActivePage();wt.emit(Ot,{numPage:parseInt(Ar(t).html(),10)+1})})),Ar(ee).on("click",(e=>{e.preventDefault();if(Ar(e.currentTarget).hasClass("disabled"))return;const t=this.getActivePage();wt.emit(Ot,{numPage:parseInt(Ar(t).html(),10)-1})})),Ar(se).on("change",(e=>{e.preventDefault();const t=Ar(e.currentTarget),r=parseInt(t.val(),10);wt.emit(Rt,{numPerPage:r})})),wt.on(Ot,(e=>{this.orderProductRenderer.paginate(e.numPage),this.listenForProductDelete(),this.listenForProductEdit(),this.resetToolTips()})),wt.on(Rt,(e=>{this.orderProductRenderer.updateNumPerPage(e.numPerPage),wt.emit(Ot,{numPage:1}),Ar.ajax({url:this.router.generate("admin_orders_configure_product_pagination"),method:"POST",data:{numPerPage:e.numPerPage}})}))}listenForRefund(){Ar(dt.buttons.partialRefund).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showPartialRefund()})),Ar(dt.buttons.standardRefund).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showStandardRefund()})),Ar(dt.buttons.returnProduct).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showReturnProduct()})),Ar(dt.buttons.abort).on("click",(()=>{this.orderProductRenderer.moveProductPanelToOriginalPosition(),this.orderProductCancel.hideRefund()}))}listenForCancelProduct(){Ar(dt.buttons.cancelProducts).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showCancelProductForm()}))}getActivePage(){return Ar(X).find(".active span").get(0)}refreshProductsList(e){Ar(mt).show();const t=Ar(X).data("numPerPage"),r=Ar(ae).length,o=parseInt(Ar(re).html(),10);Ar.ajax(this.router.generate("admin_orders_get_products",{orderId:e})).done((e=>{Ar(J).find(ae).remove(),Ar(he).remove(),Ar(`${J} tbody`).prepend(e),Ar(mt).hide();const s=Ar(ae).length,n=Math.ceil(s/t);this.orderProductRenderer.updateNumProducts(s),this.orderProductRenderer.updatePaginationControls();let i=1,a="";r>s?(a=r-s==1?window.translate_javascripts["The product was successfully removed."]:window.translate_javascripts["[1] products were successfully removed."].replace("[1]",r-s),i=1===n?1:o):r<s&&(a=s-r==1?window.translate_javascripts["The product was successfully added."]:window.translate_javascripts["[1] products were successfully added."].replace("[1]",s-r),i=1),""!==a&&Ar.growl.notice({title:"",message:a}),wt.emit(Ot,{numPage:i}),this.resetToolTips()})).fail((()=>{Ar.growl.error({title:"",message:"Failed to reload the products list. Please reload the page"})}))}}
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
const{$:Gr}=window;class Br{constructor(e){this.activeSearchRequest=null,this.router=new It,this.input=e,this.results=[],this.searchTimeoutId=void 0,this.dropdownMenu=Gr(ye),this.onItemClickedCallback=()=>{}}listenForSearch(){this.input.on("click",(e=>{e.stopImmediatePropagation(),this.updateResults(this.results)})),this.input.on("keyup",(e=>this.delaySearch(e.currentTarget))),Gr(document).on("click",(()=>this.dropdownMenu.hide()))}delaySearch(e){clearTimeout(this.searchTimeoutId),e.value.length<2||(this.searchTimeoutId=setTimeout((()=>{this.search(e.value,Gr(e).data("currency"),Gr(e).data("order"))}),300))}search(e,t,r){const o={search_phrase:e};t&&(o.currency_id=t),r&&(o.order_id=r),null!==this.activeSearchRequest&&this.activeSearchRequest.abort(),this.activeSearchRequest=Gr.get(this.router.generate("admin_orders_products_search",o)),this.activeSearchRequest.then((e=>this.updateResults(e))).always((()=>{this.activeSearchRequest=null}))}updateResults(e){this.dropdownMenu.empty(),!e||!e.products||Object.keys(e.products).length<=0?this.dropdownMenu.hide():(this.results=e.products,Object.values(this.results).forEach((e=>{const t=Gr(`<a class="dropdown-item" data-id="${e.productId}" href="#">${e.name}</a>`);t.on("click",(e=>{e.preventDefault(),this.onItemClicked(Gr(e.target).data("id"))})),this.dropdownMenu.append(t)})),this.dropdownMenu.show())}onItemClicked(e){const t=this.results.filter((t=>t.productId===e));0!==t.length&&(this.input.val(t[0].name),this.onItemClickedCallback(t[0]))}}
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
const{$:Zr}=window;class Hr{constructor(){this.router=new It,this.productAddActionBtn=Zr(ge),this.productIdInput=Zr(Pe),this.combinationsBlock=Zr(we),this.combinationsSelect=Zr(Te),this.priceTaxIncludedInput=Zr(Ee),this.priceTaxExcludedInput=Zr(ke),this.taxRateInput=Zr(Ie),this.quantityInput=Zr(Se),this.availableText=Zr(Oe),this.locationText=Zr(Re),this.totalPriceText=Zr(Ce),this.invoiceSelect=Zr(qe),this.freeShippingSelect=Zr(je),this.productAddMenuBtn=Zr(fe),this.available=null,this.setupListener(),this.product={},this.currencyPrecision=Zr(J).data("currencyPrecision"),this.priceTaxCalculator=new Ft,this.orderProductRenderer=new Er,this.orderPricesRefresher=new Ir,this.isOrderTaxIncluded=Zr(_e).data("isOrderTaxIncluded"),this.taxExcluded=null,this.taxIncluded=null}setupListener(){this.combinationsSelect.on("change",(e=>{const t=window.ps_round(Zr(e.currentTarget).find(":selected").data("priceTaxExcluded"),this.currencyPrecision);this.priceTaxExcludedInput.val(t),this.taxExcluded=parseFloat(t);const r=window.ps_round(Zr(e.currentTarget).find(":selected").data("priceTaxIncluded"),this.currencyPrecision);this.priceTaxIncludedInput.val(r),this.taxIncluded=parseFloat(r),this.locationText.html(Zr(e.currentTarget).find(":selected").data("location")),this.available=Zr(e.currentTarget).find(":selected").data("stock"),this.quantityInput.trigger("change"),this.orderProductRenderer.toggleColumn(de)})),this.quantityInput.on("change keyup",(e=>{if(null!==this.available){const t=e.target,r=Number(t.value),o=this.available-r,s=this.availableText.data("availableOutOfStock");this.availableText.text(o),this.availableText.toggleClass("text-danger font-weight-bold",o<0);const n=r<=0||o<0&&!s;this.productAddActionBtn.prop("disabled",n),this.invoiceSelect.prop("disabled",!s&&o<0),this.taxIncluded=parseFloat(this.priceTaxIncludedInput.val()),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))}})),this.productIdInput.on("change",(()=>{this.productAddActionBtn.removeAttr("disabled"),this.invoiceSelect.removeAttr("disabled")})),this.priceTaxIncludedInput.on("change keyup",(e=>{const t=e.target;this.taxIncluded=parseFloat(t.value),this.taxExcluded=this.priceTaxCalculator.calculateTaxExcluded(this.taxIncluded,this.taxRateInput.val(),this.currencyPrecision);const r=parseInt(this.quantityInput.val(),10);this.priceTaxExcludedInput.val(this.taxExcluded),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))})),this.priceTaxExcludedInput.on("change keyup",(e=>{const t=e.target;this.taxExcluded=parseFloat(t.value),this.taxIncluded=this.priceTaxCalculator.calculateTaxIncluded(this.taxExcluded,this.taxRateInput.val(),this.currencyPrecision);const r=parseInt(this.quantityInput.val(),10);this.priceTaxIncludedInput.val(this.taxIncluded),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))})),this.productAddActionBtn.on("click",(e=>this.confirmNewInvoice(e))),this.invoiceSelect.on("change",(()=>this.orderProductRenderer.toggleProductAddNewInvoiceInfo()))}setProduct(e){if(e){this.productIdInput.val(e.productId).trigger("change");const t=window.ps_round(e.priceTaxExcl,this.currencyPrecision);this.priceTaxExcludedInput.val(t),this.taxExcluded=parseFloat(t);const r=window.ps_round(e.priceTaxIncl,this.currencyPrecision);this.priceTaxIncludedInput.val(r),this.taxIncluded=parseFloat(r),this.taxRateInput.val(e.taxRate),this.locationText.html(e.location),this.available=e.stock,this.availableText.data("availableOutOfStock",e.availableOutOfStock),this.quantityInput.val(1),this.quantityInput.trigger("change"),this.setCombinations(e.combinations),this.orderProductRenderer.toggleColumn(de)}}setCombinations(e){this.combinationsSelect.empty(),Object.values(e).forEach((e=>{this.combinationsSelect.append(`<option value="${e.attributeCombinationId}" data-price-tax-excluded="${e.priceTaxExcluded}" data-price-tax-included="${e.priceTaxIncluded}" data-stock="${e.stock}" data-location="${e.location}">${e.attribute}</option>`)})),this.combinationsBlock.toggleClass("d-none",0===Object.keys(e).length),Object.keys(e).length>0&&this.combinationsSelect.trigger("change")}addProduct(e){this.productAddActionBtn.prop("disabled",!0),this.invoiceSelect.prop("disabled",!0),this.combinationsSelect.prop("disabled",!0);const t={product_id:this.productIdInput.val(),combination_id:Zr(":selected",this.combinationsSelect).val(),price_tax_incl:this.priceTaxIncludedInput.val(),price_tax_excl:this.priceTaxExcludedInput.val(),quantity:this.quantityInput.val(),invoice_id:this.invoiceSelect.val(),free_shipping:this.freeShippingSelect.prop("checked")};Zr.ajax({url:this.router.generate("admin_orders_add_product",{orderId:e}),method:"POST",data:t}).then((r=>{wt.emit(kt,{orderId:e,orderProductId:t.product_id,newRow:r})}),(e=>{this.productAddActionBtn.prop("disabled",!1),this.invoiceSelect.prop("disabled",!1),this.combinationsSelect.prop("disabled",!1),e.responseJSON&&e.responseJSON.message&&Zr.growl.error({message:e.responseJSON.message})}))}confirmNewInvoice(e){const t=parseInt(this.invoiceSelect.val(),10),r=Zr(e.currentTarget).data("orderId");if(0===t){new yr({id:"modal-confirm-new-invoice",confirmTitle:this.invoiceSelect.data("modal-title"),confirmMessage:this.invoiceSelect.data("modal-body"),confirmButtonLabel:this.invoiceSelect.data("modal-apply"),closeButtonLabel:this.invoiceSelect.data("modal-cancel")},(()=>{this.confirmNewPrice(r,t)})).show()}else this.addProduct(r)}confirmNewPrice(e,t){const r=Zr(":selected",this.combinationsSelect).val(),o=void 0===r?0:r;if("invoice"===this.orderPricesRefresher.checkOtherProductPricesMatch(this.priceTaxIncludedInput.val(),this.productIdInput.val(),o,t)){new yr({id:"modal-confirm-new-price",confirmTitle:this.invoiceSelect.data("modal-edit-price-title"),confirmMessage:this.invoiceSelect.data("modal-edit-price-body"),confirmButtonLabel:this.invoiceSelect.data("modal-edit-price-apply"),closeButtonLabel:this.invoiceSelect.data("modal-edit-price-cancel")},(()=>{this.addProduct(e)})).show()}else this.addProduct(e)}}
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
const{$:zr}=window;class Ur{constructor(){this.wrapperSelector=".js-text-with-length-counter",this.textSelector=".js-countable-text",this.inputSelector=".js-countable-input",zr(document).on("input",`${this.wrapperSelector} ${this.inputSelector}`,(e=>{const t=zr(e.currentTarget),r=t.val(),o=t.data("max-length")-r.length;t.closest(this.wrapperSelector).find(this.textSelector).text(o)}))}}
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
const{$:Qr}=window;class Vr{constructor(){this.$orderMessageChangeWarning=Qr(L),this.$messagesContainer=Qr(M)}listenForPredefinedMessageSelection(){this.handlePredefinedMessageSelection()}listenForFullMessagesOpen(){this.onFullMessagesOpen()}handlePredefinedMessageSelection(){Qr(document).on("change",j,(e=>{const t=Qr(e.currentTarget).val();if(!t)return;const r=this.$messagesContainer.find(`div[data-id=${t}]`).text().trim(),o=Qr(F),s=o.val();(null==s?void 0:s.trim())===r||o.val()&&!window.confirm(this.$orderMessageChangeWarning.text())||(o.val(r),o.trigger("input"))}))}onFullMessagesOpen(){Qr(document).on("click",Z,(()=>this.scrollToMsgListBottom()))}scrollToMsgListBottom(){const e=Qr(G),t=document.querySelector(B),r=window.setInterval((()=>{e.hasClass("show")&&t&&(t.scrollTop=null==t?void 0:t.scrollHeight,clearInterval(r))}),10)}}
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
const{$:Jr}=window;Jr((()=>{const e="free_shipping";new vt,new Ur;const t=new Nr,r=new Br(Jr(xe)),n=new Hr;t.listenForProductPack(),t.listenForProductDelete(),t.listenForProductEdit(),t.listenForProductAdd(),t.listenForProductPagination(),t.listenForRefund(),t.listenForCancelProduct(),r.listenForSearch(),r.onItemClickedCallback=e=>n.setProduct(e),Jr(s).on("click",(e=>{Jr(e.currentTarget).closest("tr").next(":first").toggleClass("d-none")})),function(){const e=Jr(h);Jr(u).on("input",(()=>{e.prop("disabled",!1)}))}(),function(){const e=Jr(pt);Jr(ht).on("input",(()=>{e.prop("disabled",!1)}))}(),function(){const e=Jr(P),t=Jr(w);Jr(I).on("change",(r=>{const o=Jr(r.currentTarget),s=Jr("option:selected",o),n=o.val();t.css("background-color",s.data("background-color")),t.toggleClass("is-bright",void 0!==s.data("is-bright")),e.prop("disabled",parseInt(n,10)===e.data("orderStatusId"))}))}(),new bt;const i=new Vr;i.listenForPredefinedMessageSelection(),i.listenForFullMessagesOpen(),Jr(c).on("click",(e=>{e.preventDefault(),function(){const e=Jr(l),t=Jr(c),r=t.hasClass("is-opened");r?(t.removeClass("is-opened"),e.addClass("d-none")):(t.addClass("is-opened"),e.removeClass("d-none"));t.find(".material-icons").text(r?"add":"remove")}()})),Jr(lt).on("click",(e=>{e.preventDefault(),function(){const e=Jr(ut),t=Jr(lt),r=t.hasClass("is-opened");t.toggleClass("is-opened",!r),e.toggleClass("d-none",r);t.find(".material-icons").text(r?"add":"remove")}()})),Jr(ct).on("click",(()=>{const e=document.title;document.title=Jr(o).data("orderTitle"),window.print(),document.title=e})),function(){const t=Jr(p),r=t.find("form"),o=t.find(m),s=t.find(y),n=r.find(g),i=n.closest(".form-group");t.on("shown.bs.modal",(()=>{Jr(_).prop("disabled",!0)})),r.find(f).on("keyup",(e=>{const t=Jr(e.currentTarget).val();Jr(_).prop("disabled",0===t.trim().length)})),r.find(x).on("change",(e=>{const t=Jr(e.currentTarget).is(":checked");o.prop("disabled",t)})),r.find(v).on("change",(t=>{const o=Jr(t.currentTarget).val(),a=r.find(b);"amount"===o?(s.removeClass("d-none"),a.html(a.data("currencySymbol"))):s.addClass("d-none"),"percent"===o&&a.html("%"),n.prop("disabled",o===e),i.toggleClass("d-none",o===e)}))}(),function(){const e=Jr(S);Jr(O).on("click",(t=>{e.find(R).val(Jr(t.currentTarget).data("addressType"))}))}(),Jr(at).find(".nav-tabs li:first-child a").tab("show")}))},7187:e=>{"use strict";var t,r="object"==typeof Reflect?Reflect:null,o=r&&"function"==typeof r.apply?r.apply:function(e,t,r){return Function.prototype.apply.call(e,t,r)};t=r&&"function"==typeof r.ownKeys?r.ownKeys:Object.getOwnPropertySymbols?function(e){return Object.getOwnPropertyNames(e).concat(Object.getOwnPropertySymbols(e))}:function(e){return Object.getOwnPropertyNames(e)};var s=Number.isNaN||function(e){return e!=e};function n(){n.init.call(this)}e.exports=n,n.EventEmitter=n,n.prototype._events=void 0,n.prototype._eventsCount=0,n.prototype._maxListeners=void 0;var i=10;function a(e){if("function"!=typeof e)throw new TypeError('The "listener" argument must be of type Function. Received type '+typeof e)}function d(e){return void 0===e._maxListeners?n.defaultMaxListeners:e._maxListeners}function c(e,t,r,o){var s,n,i,c;if(a(r),void 0===(n=e._events)?(n=e._events=Object.create(null),e._eventsCount=0):(void 0!==n.newListener&&(e.emit("newListener",t,r.listener?r.listener:r),n=e._events),i=n[t]),void 0===i)i=n[t]=r,++e._eventsCount;else if("function"==typeof i?i=n[t]=o?[r,i]:[i,r]:o?i.unshift(r):i.push(r),(s=d(e))>0&&i.length>s&&!i.warned){i.warned=!0;var l=new Error("Possible EventEmitter memory leak detected. "+i.length+" "+String(t)+" listeners added. Use emitter.setMaxListeners() to increase limit");l.name="MaxListenersExceededWarning",l.emitter=e,l.type=t,l.count=i.length,c=l,console&&console.warn&&console.warn(c)}return e}function l(){if(!this.fired)return this.target.removeListener(this.type,this.wrapFn),this.fired=!0,0===arguments.length?this.listener.call(this.target):this.listener.apply(this.target,arguments)}function u(e,t,r){var o={fired:!1,wrapFn:void 0,target:e,type:t,listener:r},s=l.bind(o);return s.listener=r,o.wrapFn=s,s}function h(e,t,r){var o=e._events;if(void 0===o)return[];var s=o[t];return void 0===s?[]:"function"==typeof s?r?[s.listener||s]:[s]:r?function(e){for(var t=new Array(e.length),r=0;r<t.length;++r)t[r]=e[r].listener||e[r];return t}(s):m(s,s.length)}function p(e){var t=this._events;if(void 0!==t){var r=t[e];if("function"==typeof r)return 1;if(void 0!==r)return r.length}return 0}function m(e,t){for(var r=new Array(t),o=0;o<t;++o)r[o]=e[o];return r}Object.defineProperty(n,"defaultMaxListeners",{enumerable:!0,get:function(){return i},set:function(e){if("number"!=typeof e||e<0||s(e))throw new RangeError('The value of "defaultMaxListeners" is out of range. It must be a non-negative number. Received '+e+".");i=e}}),n.init=function(){void 0!==this._events&&this._events!==Object.getPrototypeOf(this)._events||(this._events=Object.create(null),this._eventsCount=0),this._maxListeners=this._maxListeners||void 0},n.prototype.setMaxListeners=function(e){if("number"!=typeof e||e<0||s(e))throw new RangeError('The value of "n" is out of range. It must be a non-negative number. Received '+e+".");return this._maxListeners=e,this},n.prototype.getMaxListeners=function(){return d(this)},n.prototype.emit=function(e){for(var t=[],r=1;r<arguments.length;r++)t.push(arguments[r]);var s="error"===e,n=this._events;if(void 0!==n)s=s&&void 0===n.error;else if(!s)return!1;if(s){var i;if(t.length>0&&(i=t[0]),i instanceof Error)throw i;var a=new Error("Unhandled error."+(i?" ("+i.message+")":""));throw a.context=i,a}var d=n[e];if(void 0===d)return!1;if("function"==typeof d)o(d,this,t);else{var c=d.length,l=m(d,c);for(r=0;r<c;++r)o(l[r],this,t)}return!0},n.prototype.addListener=function(e,t){return c(this,e,t,!1)},n.prototype.on=n.prototype.addListener,n.prototype.prependListener=function(e,t){return c(this,e,t,!0)},n.prototype.once=function(e,t){return a(t),this.on(e,u(this,e,t)),this},n.prototype.prependOnceListener=function(e,t){return a(t),this.prependListener(e,u(this,e,t)),this},n.prototype.removeListener=function(e,t){var r,o,s,n,i;if(a(t),void 0===(o=this._events))return this;if(void 0===(r=o[e]))return this;if(r===t||r.listener===t)0==--this._eventsCount?this._events=Object.create(null):(delete o[e],o.removeListener&&this.emit("removeListener",e,r.listener||t));else if("function"!=typeof r){for(s=-1,n=r.length-1;n>=0;n--)if(r[n]===t||r[n].listener===t){i=r[n].listener,s=n;break}if(s<0)return this;0===s?r.shift():function(e,t){for(;t+1<e.length;t++)e[t]=e[t+1];e.pop()}(r,s),1===r.length&&(o[e]=r[0]),void 0!==o.removeListener&&this.emit("removeListener",e,i||t)}return this},n.prototype.off=n.prototype.removeListener,n.prototype.removeAllListeners=function(e){var t,r,o;if(void 0===(r=this._events))return this;if(void 0===r.removeListener)return 0===arguments.length?(this._events=Object.create(null),this._eventsCount=0):void 0!==r[e]&&(0==--this._eventsCount?this._events=Object.create(null):delete r[e]),this;if(0===arguments.length){var s,n=Object.keys(r);for(o=0;o<n.length;++o)"removeListener"!==(s=n[o])&&this.removeAllListeners(s);return this.removeAllListeners("removeListener"),this._events=Object.create(null),this._eventsCount=0,this}if("function"==typeof(t=r[e]))this.removeListener(e,t);else if(void 0!==t)for(o=t.length-1;o>=0;o--)this.removeListener(e,t[o]);return this},n.prototype.listeners=function(e){return h(this,e,!0)},n.prototype.rawListeners=function(e){return h(this,e,!1)},n.listenerCount=function(e,t){return"function"==typeof e.listenerCount?e.listenerCount(t):p.call(e,t)},n.prototype.listenerCount=p,n.prototype.eventNames=function(){return this._eventsCount>0?t(this._events):[]}},2564:e=>{"use strict";var t=Object.assign||function(e){for(var t,r=1;r<arguments.length;r++)for(var o in t=arguments[r])Object.prototype.hasOwnProperty.call(t,o)&&(e[o]=t[o]);return e},r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};e.exports=new function e(){var o=this;(function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")})(this,e),this.setRoutes=function(e){o.routesRouting=e||[]},this.getRoutes=function(){return o.routesRouting},this.setBaseUrl=function(e){o.contextRouting.base_url=e},this.getBaseUrl=function(){return o.contextRouting.base_url},this.setPrefix=function(e){o.contextRouting.prefix=e},this.setScheme=function(e){o.contextRouting.scheme=e},this.getScheme=function(){return o.contextRouting.scheme},this.setHost=function(e){o.contextRouting.host=e},this.getHost=function(){return o.contextRouting.host},this.buildQueryParams=function(e,t,s){var n=new RegExp(/\[]$/);t instanceof Array?t.forEach((function(t,i){n.test(e)?s(e,t):o.buildQueryParams(e+"["+("object"===(void 0===t?"undefined":r(t))?i:"")+"]",t,s)})):"object"===(void 0===t?"undefined":r(t))?Object.keys(t).forEach((function(r){return o.buildQueryParams(e+"["+r+"]",t[r],s)})):s(e,t)},this.getRoute=function(e){var t=o.contextRouting.prefix+e;if(o.routesRouting[t])return o.routesRouting[t];if(!o.routesRouting[e])throw new Error('The route "'+e+'" does not exist.');return o.routesRouting[e]},this.generate=function(e,r,s){var n=o.getRoute(e),i=r||{},a=t({},i),d="_scheme",c="",l=!0,u="";if((n.tokens||[]).forEach((function(t){if("text"===t[0])return c=t[1]+c,void(l=!1);if("variable"!==t[0])throw new Error('The token type "'+t[0]+'" is not supported.');var r=(n.defaults||{})[t[3]];if(0==l||!r||(i||{})[t[3]]&&i[t[3]]!==n.defaults[t[3]]){var o;if((i||{})[t[3]])o=i[t[3]],delete a[t[3]];else{if(!r){if(l)return;throw new Error('The route "'+e+'" requires the parameter "'+t[3]+'".')}o=n.defaults[t[3]]}if(!(!0===o||!1===o||""===o)||!l){var s=encodeURIComponent(o).replace(/%2F/g,"/");"null"===s&&null===o&&(s=""),c=t[1]+s+c}l=!1}else r&&delete a[t[3]]})),""==c&&(c="/"),(n.hosttokens||[]).forEach((function(e){var t;return"text"===e[0]?void(u=e[1]+u):void("variable"===e[0]&&((i||{})[e[3]]?(t=i[e[3]],delete a[e[3]]):n.defaults[e[3]]&&(t=n.defaults[e[3]]),u=e[1]+t+u))})),c=o.contextRouting.base_url+c,n.requirements[d]&&o.getScheme()!==n.requirements[d]?c=n.requirements[d]+"://"+(u||o.getHost())+c:u&&o.getHost()!==u?c=o.getScheme()+"://"+u+c:!0===s&&(c=o.getScheme()+"://"+o.getHost()+c),0<Object.keys(a).length){var h=[],p=function(e,t){var r=t;r=null===(r="function"==typeof r?r():r)?"":r,h.push(encodeURIComponent(e)+"="+encodeURIComponent(r))};Object.keys(a).forEach((function(e){return o.buildQueryParams(e,a[e],p)})),c=c+"?"+h.join("&").replace(/%20/g,"+")}return c},this.setData=function(e){o.setBaseUrl(e.base_url),o.setRoutes(e.routes),"prefix"in e&&o.setPrefix(e.prefix),o.setHost(e.host),o.setScheme(e.scheme)},this.contextRouting={base_url:"",prefix:"",host:"",scheme:""}}},1658:(e,t,r)=>{var o="[object Symbol]",s=/[\\^$.*+?()[\]{}|]/g,n=RegExp(s.source),i="object"==typeof r.g&&r.g&&r.g.Object===Object&&r.g,a="object"==typeof self&&self&&self.Object===Object&&self,d=i||a||Function("return this")(),c=Object.prototype.toString,l=d.Symbol,u=l?l.prototype:void 0,h=u?u.toString:void 0;function p(e){if("string"==typeof e)return e;if(function(e){return"symbol"==typeof e||function(e){return!!e&&"object"==typeof e}(e)&&c.call(e)==o}(e))return h?h.call(e):"";var t=e+"";return"0"==t&&1/e==-Infinity?"-0":t}e.exports=function(e){var t;return(e=null==(t=e)?"":p(t))&&n.test(e)?e.replace(s,"\\$&"):e}},9567:e=>{"use strict";e.exports=window.jQuery}},t={};function r(o){var s=t[o];if(void 0!==s)return s.exports;var n=t[o]={exports:{}};return e[o](n,n.exports,r),n.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var o in t)r.o(t,o)&&!r.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},r.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o=r(1159);window.order_view=o})();