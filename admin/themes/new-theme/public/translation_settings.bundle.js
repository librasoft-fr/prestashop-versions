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
 */const e=".js-translation-type",s=".js-email-content-type",o=".js-email-form-group",h=".js-module-form-group",t=".js-theme-form-group",i=".js-default-theme",d=".js-no-theme",c="#form_core_selectors_core_type",n="#form_core_selectors_selected_value",l="#form_themes_selectors_themes_type",a="#form_themes_selectors_selected_value",r="#form_modules_selectors_modules_type",p="#form_modules_selectors_selected_value",u="#form-export-language-button",{$:g}=window,b="mails";class m{constructor(){g(e).on("change",this.toggleFields.bind(this)),g(s).on("change",this.toggleEmailFields.bind(this)),g(window).on("load",this.toggleFields.bind(this))}toggleFields(){const s=g(e).val(),d=g(h),c=g(o),n=g(t),l=n.find(i);switch(s){case"back":case"others":this.hide(d,c,n);break;case"themes":this.show(n),this.hide(d,c,l);break;case"modules":this.hide(c,n),this.show(d);break;case b:this.hide(d,n),this.show(c)}this.toggleEmailFields()}toggleEmailFields(){if(g(e).val()!==b)return;const s=g(o).find("select").val(),h=g(t),c=h.find(d),n=h.find(i);"body"===s?(c.prop("selected",!0),this.show(c,h,n)):this.hide(c,h,n)}hide(...e){Object.values(e).forEach((e=>{e.addClass("d-none"),e.find("select").prop("disabled","disabled")}))}show(...e){Object.values(e).forEach((e=>{e.removeClass("d-none"),e.find("select").prop("disabled",!1)}))}}
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
const{$:f}=window,_=f(c),C=f(l),w=f(r),k=f(n).closest(".form-group"),v=f(a).closest(".form-group"),j=f(p).closest(".form-group"),y=f(n),E=f(a),F=f(p),O=f(u);class T{constructor(){_.on("change",this.coreTypeChanged.bind(this)),C.on("change",this.themesTypeChanged.bind(this)),w.on("change",this.modulesTypeChanged.bind(this)),y.on("change",this.subChoicesChanged.bind(this)),E.on("change",this.subChoicesChanged.bind(this)),F.on("change",this.subChoicesChanged.bind(this)),this.check(_)}coreTypeChanged(){_.is(":checked")&&(_.prop("disabled",!1),this.uncheck(C,w),this.show(k),this.hide(v,j),this.subChoicesChanged())}themesTypeChanged(){C.is(":checked")&&(C.prop("disabled",!1),this.uncheck(_,w),this.show(v),this.hide(k,j),this.subChoicesChanged())}modulesTypeChanged(){w.is(":checked")&&(j.prop("disabled",!1),this.uncheck(C,_),this.show(j),this.hide(v,k),this.subChoicesChanged())}subChoicesChanged(){_.prop("checked")&&y.find(":checked").length>0||C.prop("checked")&&null!==E.val()||w.prop("checked")&&null!==F.val()?O.prop("disabled",!1):O.prop("disabled",!0)}hide(...e){Object.values(e).forEach((e=>{e.addClass("d-none"),e.find("select, input").prop("disabled","disabled")}))}show(...e){Object.values(e).forEach((e=>{e.removeClass("d-none"),e.find("select, input").prop("disabled",!1)}))}uncheck(...e){Object.values(e).forEach((e=>{e.prop("checked",!1)}))}check(...e){Object.values(e).forEach((e=>{e.prop("checked",!0)}))}}
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
class ${constructor(){new m,new T}}
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
const{$:x}=window;x((()=>{new $})),window.translation_settings={}})();