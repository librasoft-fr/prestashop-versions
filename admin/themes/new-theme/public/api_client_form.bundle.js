(()=>{"use strict";var t={428:t=>{t.exports=window.jQuery}},e={};
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
 */const o="a.generate-client-secret",i="generate-secret-modal",n=".copy-secret-to-clipboard",s=".copy-secret-to-clipboard i";var a=function o(i){var n=e[i];if(void 0!==n)return n.exports;var s=e[i]={exports:{}};return t[i](s,s.exports,o),s.exports}(428),l=Object.defineProperty,r=Object.getOwnPropertySymbols,c=Object.prototype.hasOwnProperty,d=Object.prototype.propertyIsEnumerable,m=(t,e,o)=>e in t?l(t,e,{enumerable:!0,configurable:!0,writable:!0,value:o}):t[e]=o,h=(t,e)=>{for(var o in e||(e={}))c.call(e,o)&&m(t,o,e[o]);if(r)for(var o of r(e))d.call(e,o)&&m(t,o,e[o]);return t};
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
class u{constructor(t){const e=h({id:"confirm-modal",closable:!1},t);this.buildModalContainer(e)}buildModalContainer(t){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=t.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),t.dialogStyle&&Object.keys(t.dialogStyle).forEach((e=>{this.dialog.style[e]=t.dialogStyle[e]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),t.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=t.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class b{constructor(t){const e=h({id:"confirm-modal",closable:!1,dialogStyle:{}},t);this.initContainer(e)}initContainer(t){this.modal||(this.modal=new u(t)),this.$modal=a(this.modal.container);const{id:e,closable:o}=t;this.$modal.modal({backdrop:!!o||"static",keyboard:void 0===o||o}),this.$modal.modal("hide"),this.$modal.on("hidden.bs.modal",(()=>{const o=document.querySelector(`#${e}`);o&&o.remove(),t.closeCallback&&t.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(t){return this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=t,this}render(t){return this.modal.message.innerHTML=t,this}show(){return this.$modal.modal("show"),this}hide(){return this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")})),this}}
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
function p(t){return void 0===t}var f=Object.defineProperty,y=Object.getOwnPropertySymbols,g=Object.prototype.hasOwnProperty,v=Object.prototype.propertyIsEnumerable,L=(t,e,o)=>e in t?f(t,e,{enumerable:!0,configurable:!0,writable:!0,value:o}):t[e]=o;
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
class C extends u{constructor(t){super(t)}buildModalContainer(t){super.buildModalContainer(t),this.message.classList.add("confirm-message"),this.message.innerHTML=t.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=t.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",t.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=t.confirmButtonLabel,this.footer.append(this.closeButton,...t.customButtons,this.confirmButton),this.content.append(this.footer)}}const B=class extends b{constructor(t,e,o){var i;let n;n=p(t.confirmCallback)?p(e)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:e:t.confirmCallback;super(((t,e)=>{for(var o in e||(e={}))g.call(e,o)&&L(t,o,e[o]);if(y)for(var o of y(e))v.call(e,o)&&L(t,o,e[o]);return t})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:t.confirmTitle,dialogStyle:{},confirmCallback:n,closeCallback:null!=(i=t.closeCallback)?i:o},t))}initContainer(t){this.modal=new C(t),this.modal.confirmButton.addEventListener("click",t.confirmCallback),super.initContainer(t)}},{$:w}=window;w((()=>{var t,e,a;function l(t){var e;const o=document.createElement("input");o.value=null!=(e=t.dataset.secret)?e:"",document.body.prepend(o),o.select(),o.setSelectionRange(0,99999);try{document.execCommand("copy")}finally{o.remove()}}null==(t=document.querySelector(o))||t.addEventListener("click",(t=>{t.preventDefault();const e=t.target,o=new B({id:i,confirmTitle:e.dataset.confirmTitle,confirmMessage:e.dataset.confirmMessage,confirmButtonLabel:e.dataset.confirmButtonLabel,closeButtonLabel:e.dataset.closeButtonLabel,confirmButtonClass:"btn-warning",closable:!0},(()=>{!function(t){const e=document.createElement("form");e.setAttribute("method","POST"),e.setAttribute("action",t.href),e.setAttribute("style","display: none;"),document.body.appendChild(e),e.submit()}(e)}));o.show()})),null==(e=document.querySelector(n))||e.addEventListener("click",(t=>{t.preventDefault();l(t.target)})),null==(a=document.querySelector(s))||a.addEventListener("click",(t=>{t.preventDefault();l(t.target.parentElement)}))})),window.api_client_form={}})();