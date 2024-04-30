import{d as u,c as l}from"./assets/dom-utils-d77254b8.js";import{s as h,a as _,e as b,i as g,t as q,h as i,d as s,f as d,j as c}from"./assets/sentry-96f4e833.js";import{I as w}from"./assets/file-uploader-a8dcb49d.js";/**
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
 */function y(){location.reload()}function F(t,e){_(t,e),b(t)}function p(t){const e=document.forms[t];if(e===void 0)return;const o=e.action,a=e.dataset.action;e.addEventListener("submit",f=>{f.preventDefault();const m=new XMLHttpRequest;m.open("POST",o,!0),m.setRequestHeader("X-Requested-With","XMLHttpRequest"),m.onreadystatechange=()=>{if(m.readyState===XMLHttpRequest.DONE){const n=m.status;n===0||n>=200&&n<400?y():F(e,JSON.parse(m.response).message)}};let r;a==="import-from-computer"&&(r=new FormData,r.append("file",e["theme-computer-file"].files[0],e["theme-computer-file"].files[0].name),r.append("action",a)),a==="import-from-web"&&(r=JSON.stringify({path:e["theme-web-path"].value,action:a})),a==="import-from-ftp"&&(r=JSON.stringify({path:e["theme-ftp-path"].value,action:a})),h(t),m.send(r)})}/**
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
 */u(()=>{g(),q("Theme"),new w,i("psxdesign-upgrade-form"),s("import-from-computer"),p("import-from-computer"),$("#import_theme_from_computer_modal").on("hidden.bs.modal",()=>{d("import-from-computer")}),s("import-from-web"),p("import-from-web"),$("#import_theme_from_web_modal").on("hidden.bs.modal",()=>{d("import-from-web")}),s("import-from-ftp"),p("import-from-ftp"),$("#import_theme_from_ftp_modal").on("hidden.bs.modal",()=>{d("import-from-ftp")}),i("delete_theme"),document.querySelectorAll(".js-open-delete-theme-modal").forEach(t=>{const e=t.dataset.themeName,o=t.dataset.action;t.addEventListener("click",()=>{e!=null&&l("#delete_theme_modal .theme-name",e),o!=null&&c("delete_theme",o)})}),i("use_theme"),document.querySelectorAll(".js-open-use-theme-modal").forEach(t=>{const e=t.dataset.themeName,o=t.dataset.action;t.addEventListener("click",()=>{e!=null&&l("#use_theme_modal .theme-name",e),o!=null&&c("use_theme",o)})})});
