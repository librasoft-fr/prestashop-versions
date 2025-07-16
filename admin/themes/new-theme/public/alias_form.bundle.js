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
*/const t=".js-aliases-collection",e=".js-btn-add-alias",n=".js-btn-delete-alias",i=".alias-item",l='.form-control[name$="[alias]"]',{$:o}=window;class s{constructor(){this.$collection=o(t),this.idxAlias=this.$collection.children(i).length-1,this.initListeners(),0===this.$collection.children(i).length&&this.onAddAlias(null,!1)}initListeners(){this.$collection.parent().on("click",e,(t=>this.onAddAlias(t))),this.$collection.on("click",n,(t=>this.onDeleteAlias(t))),this.$collection.on("keydown",l,(t=>this.onKeyDownAlias(t)))}onAddAlias(t=null,e=!0){t&&t.preventDefault(),this.idxAlias+=1;let n=this.$collection.data("prototype");n=n.replace(/__name__/g,this.idxAlias),this.$collection.append(n),this.$collection.children().last().find('[name$="[active]"][value=1]').prop("checked",!0),e&&this.$collection.children().last().find(l).focus(),this.refreshDeleteAliasButtons()}onDeleteAlias(t){t.preventDefault();o(t.target).parents(i).remove(),this.refreshDeleteAliasButtons()}onKeyDownAlias(t){","===t.key&&(t.preventDefault(),""!==this.$collection.children().last().find("input").val()&&this.onAddAlias(t))}refreshDeleteAliasButtons(){1===this.$collection.children().length?this.$collection.children().find(n).addClass("d-none"):this.$collection.children().find(n).removeClass("d-none")}}
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
 */const a=".js-form-submit-btn",{$:c}=window;class d{constructor(){c(document).on("click",a,(t=>{t.preventDefault();const e=c(t.target);if(e.data("form-confirm-message")&&!1===window.confirm(e.data("form-confirm-message")))return;let n="POST",i=null;if(e.data("method")){const t=e.data("method"),l=["GET","POST"].includes(t);n=l?t:"POST",l||(i=c("<input>",{type:"_hidden",name:"_method",value:t}))}const l=c("<form>",{action:e.data("form-submit-url"),method:n});i&&l.append(i),e.data("form-csrf-token")&&l.append(c("<input>",{type:"_hidden",name:"_csrf_token",value:e.data("form-csrf-token")})),l.appendTo("body").submit()}))}}
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
const{$:r}=window;r((()=>{new d,new s})),window.alias_form={}})();