(()=>{"use strict";var e={r:e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})}},s={};e.r(s);
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
 */const o=".js-translation-type",t=".js-email-content-type",h=".js-email-form-group",i=".js-module-form-group",d=".js-theme-form-group",c=".js-default-theme",n=".js-no-theme",l="#form_core_selectors_core_type",a="#form_core_selectors_selected_value",r="#form_themes_selectors_themes_type",p="#form_themes_selectors_selected_value",u="#form_modules_selectors_modules_type",g="#form_modules_selectors_selected_value",b="#form-export-language-button",{$:m}=window,f="mails";class _{constructor(){m(o).on("change",this.toggleFields.bind(this)),m(t).on("change",this.toggleEmailFields.bind(this)),this.toggleFields()}toggleFields(){const e=m(o).val(),s=m(i),t=m(h),n=m(d),l=n.find(c);switch(e){case"back":case"others":this.hide(s,t,n);break;case"themes":this.show(n),this.hide(s,t,l);break;case"modules":this.hide(t,n),this.show(s);break;case f:this.hide(s,n),this.show(t)}this.toggleEmailFields()}toggleEmailFields(){if(m(o).val()!==f)return;const e=m(h).find("select").val(),s=m(d),t=s.find(n),i=s.find(c);"body"===e?(t.prop("selected",!0),this.show(t,s,i)):this.hide(t,s,i)}hide(...e){Object.values(e).forEach((e=>{e.addClass("d-none"),e.find("select").prop("disabled","disabled")}))}show(...e){Object.values(e).forEach((e=>{e.removeClass("d-none"),e.find("select").prop("disabled",!1)}))}}
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
const{$:C}=window,w=C(l),k=C(r),v=C(u),y=C(a).closest(".form-group"),j=C(p).closest(".form-group"),E=C(g).closest(".form-group"),O=C(a),T=C(p),F=C(g),S=C(b);class ${constructor(){w.on("change",this.coreTypeChanged.bind(this)),k.on("change",this.themesTypeChanged.bind(this)),v.on("change",this.modulesTypeChanged.bind(this)),O.on("change",this.subChoicesChanged.bind(this)),T.on("change",this.subChoicesChanged.bind(this)),F.on("change",this.subChoicesChanged.bind(this)),this.check(w)}coreTypeChanged(){w.is(":checked")&&(w.prop("disabled",!1),this.uncheck(k,v),this.show(y),this.hide(j,E),this.subChoicesChanged())}themesTypeChanged(){k.is(":checked")&&(k.prop("disabled",!1),this.uncheck(w,v),this.show(j),this.hide(y,E),this.subChoicesChanged())}modulesTypeChanged(){v.is(":checked")&&(E.prop("disabled",!1),this.uncheck(k,w),this.show(E),this.hide(j,y),this.subChoicesChanged())}subChoicesChanged(){w.prop("checked")&&O.find(":checked").length>0||k.prop("checked")&&null!==T.val()||v.prop("checked")&&null!==F.val()?S.prop("disabled",!1):S.prop("disabled",!0)}hide(...e){Object.values(e).forEach((e=>{e.addClass("d-none"),e.find("select, input").prop("disabled","disabled")}))}show(...e){Object.values(e).forEach((e=>{e.removeClass("d-none"),e.find("select, input").prop("disabled",!1)}))}uncheck(...e){Object.values(e).forEach((e=>{e.prop("checked",!1)}))}check(...e){Object.values(e).forEach((e=>{e.prop("checked",!0)}))}}
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
class M{constructor(){new _,new $}}
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
const{$:P}=window;P((()=>{new M})),window.translation_settings=s})();