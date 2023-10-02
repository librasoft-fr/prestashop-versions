(()=>{"use strict";var t={9567:t=>{t.exports=window.jQuery}},e={};function n(o){var i=e[o];if(void 0!==i)return i.exports;var r=e[o]={exports:{}};return t[o](r,r.exports,n),r.exports}n.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),n.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var o={};(()=>{n.r(o);var t=n(9567),e=Object.defineProperty,i=Object.getOwnPropertySymbols,r=Object.prototype.hasOwnProperty,s=Object.prototype.propertyIsEnumerable,a=(t,n,o)=>n in t?e(t,n,{enumerable:!0,configurable:!0,writable:!0,value:o}):t[n]=o,c=(t,e)=>{for(var n in e||(e={}))r.call(e,n)&&a(t,n,e[n]);if(i)for(var n of i(e))s.call(e,n)&&a(t,n,e[n]);return t};
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
class l{constructor(t){const e=c({id:"confirm-modal",closable:!1},t);this.buildModalContainer(e)}buildModalContainer(t){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=t.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),t.dialogStyle&&Object.keys(t.dialogStyle).forEach((e=>{this.dialog.style[e]=t.dialogStyle[e]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),t.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=t.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class d{constructor(t){const e=c({id:"confirm-modal",closable:!1,dialogStyle:{}},t);this.initContainer(e)}initContainer(e){this.modal||(this.modal=new l(e)),this.$modal=t(this.modal.container);const{id:n,closable:o}=e;this.$modal.modal({backdrop:!!o||"static",keyboard:void 0===o||o,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const t=document.querySelector(`#${n}`);t&&t.remove(),e.closeCallback&&e.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(t){return this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=t,this}render(t){return this.modal.message.innerHTML=t,this}show(){return this.$modal.modal("show"),this}hide(){return this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")})),this}}
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
function h(t){return void 0===t}var u=Object.defineProperty,f=Object.getOwnPropertySymbols,m=Object.prototype.hasOwnProperty,p=Object.prototype.propertyIsEnumerable,b=(t,e,n)=>e in t?u(t,e,{enumerable:!0,configurable:!0,writable:!0,value:n}):t[e]=n;
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
class v extends l{constructor(t){super(t)}buildModalContainer(t){super.buildModalContainer(t),this.message.classList.add("confirm-message"),this.message.innerHTML=t.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=t.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",t.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=t.confirmButtonLabel,this.footer.append(this.closeButton,...t.customButtons,this.confirmButton),this.content.append(this.footer)}}class y extends d{constructor(t,e,n){var o;let i;i=h(t.confirmCallback)?h(e)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:e:t.confirmCallback;super(((t,e)=>{for(var n in e||(e={}))m.call(e,n)&&b(t,n,e[n]);if(f)for(var n of f(e))p.call(e,n)&&b(t,n,e[n]);return t})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:t.confirmTitle,dialogStyle:{},confirmCallback:i,closeCallback:null!=(o=t.closeCallback)?o:n},t))}initContainer(t){this.modal=new v(t),this.modal.confirmButton.addEventListener("click",t.confirmCallback),super.initContainer(t)}}var _=function(){if("undefined"!=typeof Map)return Map;function t(t,e){var n=-1;return t.some((function(t,o){return t[0]===e&&(n=o,!0)})),n}return function(){function e(){this.__entries__=[]}return Object.defineProperty(e.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),e.prototype.get=function(e){var n=t(this.__entries__,e),o=this.__entries__[n];return o&&o[1]},e.prototype.set=function(e,n){var o=t(this.__entries__,e);~o?this.__entries__[o][1]=n:this.__entries__.push([e,n])},e.prototype.delete=function(e){var n=this.__entries__,o=t(n,e);~o&&n.splice(o,1)},e.prototype.has=function(e){return!!~t(this.__entries__,e)},e.prototype.clear=function(){this.__entries__.splice(0)},e.prototype.forEach=function(t,e){void 0===e&&(e=null);for(var n=0,o=this.__entries__;n<o.length;n++){var i=o[n];t.call(e,i[1],i[0])}},e}()}(),g="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,w=void 0!==n.g&&n.g.Math===Math?n.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),O="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(w):function(t){return setTimeout((function(){return t(Date.now())}),1e3/60)};var E=["top","right","bottom","left","width","height","size","weight"],M="undefined"!=typeof MutationObserver,L=function(){function t(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(t,e){var n=!1,o=!1,i=0;function r(){n&&(n=!1,t()),o&&a()}function s(){O(r)}function a(){var t=Date.now();if(n){if(t-i<2)return;o=!0}else n=!0,o=!1,setTimeout(s,e);i=t}return a}(this.refresh.bind(this),20)}return t.prototype.addObserver=function(t){~this.observers_.indexOf(t)||this.observers_.push(t),this.connected_||this.connect_()},t.prototype.removeObserver=function(t){var e=this.observers_,n=e.indexOf(t);~n&&e.splice(n,1),!e.length&&this.connected_&&this.disconnect_()},t.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},t.prototype.updateObservers_=function(){var t=this.observers_.filter((function(t){return t.gatherActive(),t.hasActive()}));return t.forEach((function(t){return t.broadcastActive()})),t.length>0},t.prototype.connect_=function(){g&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),M?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},t.prototype.disconnect_=function(){g&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},t.prototype.onTransitionEnd_=function(t){var e=t.propertyName,n=void 0===e?"":e;E.some((function(t){return!!~n.indexOf(t)}))&&this.refresh()},t.getInstance=function(){return this.instance_||(this.instance_=new t),this.instance_},t.instance_=null,t}(),C=function(t,e){for(var n=0,o=Object.keys(e);n<o.length;n++){var i=o[n];Object.defineProperty(t,i,{value:e[i],enumerable:!1,writable:!1,configurable:!0})}return t},T=function(t){return t&&t.ownerDocument&&t.ownerDocument.defaultView||w},j=P(0,0,0,0);function B(t){return parseFloat(t)||0}function A(t){for(var e=[],n=1;n<arguments.length;n++)e[n-1]=arguments[n];return e.reduce((function(e,n){return e+B(t["border-"+n+"-width"])}),0)}function k(t){var e=t.clientWidth,n=t.clientHeight;if(!e&&!n)return j;var o=T(t).getComputedStyle(t),i=function(t){for(var e={},n=0,o=["top","right","bottom","left"];n<o.length;n++){var i=o[n],r=t["padding-"+i];e[i]=B(r)}return e}(o),r=i.left+i.right,s=i.top+i.bottom,a=B(o.width),c=B(o.height);if("border-box"===o.boxSizing&&(Math.round(a+r)!==e&&(a-=A(o,"left","right")+r),Math.round(c+s)!==n&&(c-=A(o,"top","bottom")+s)),!function(t){return t===T(t).document.documentElement}(t)){var l=Math.round(a+r)-e,d=Math.round(c+s)-n;1!==Math.abs(l)&&(a-=l),1!==Math.abs(d)&&(c-=d)}return P(i.left,i.top,a,c)}var S="undefined"!=typeof SVGGraphicsElement?function(t){return t instanceof T(t).SVGGraphicsElement}:function(t){return t instanceof T(t).SVGElement&&"function"==typeof t.getBBox};function x(t){return g?S(t)?function(t){var e=t.getBBox();return P(0,0,e.width,e.height)}(t):k(t):j}function P(t,e,n,o){return{x:t,y:e,width:n,height:o}}var z=function(){function t(t){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=P(0,0,0,0),this.target=t}return t.prototype.isActive=function(){var t=x(this.target);return this.contentRect_=t,t.width!==this.broadcastWidth||t.height!==this.broadcastHeight},t.prototype.broadcastRect=function(){var t=this.contentRect_;return this.broadcastWidth=t.width,this.broadcastHeight=t.height,t},t}(),I=function(t,e){var n,o,i,r,s,a,c,l=(o=(n=e).x,i=n.y,r=n.width,s=n.height,a="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,c=Object.create(a.prototype),C(c,{x:o,y:i,width:r,height:s,top:i,right:o+r,bottom:s+i,left:o}),c);C(this,{target:t,contentRect:l})},R=function(){function t(t,e,n){if(this.activeObservations_=[],this.observations_=new _,"function"!=typeof t)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=t,this.controller_=e,this.callbackCtx_=n}return t.prototype.observe=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof T(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)||(e.set(t,new z(t)),this.controller_.addObserver(this),this.controller_.refresh())}},t.prototype.unobserve=function(t){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(t instanceof T(t).Element))throw new TypeError('parameter 1 is not of type "Element".');var e=this.observations_;e.has(t)&&(e.delete(t),e.size||this.controller_.removeObserver(this))}},t.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},t.prototype.gatherActive=function(){var t=this;this.clearActive(),this.observations_.forEach((function(e){e.isActive()&&t.activeObservations_.push(e)}))},t.prototype.broadcastActive=function(){if(this.hasActive()){var t=this.callbackCtx_,e=this.activeObservations_.map((function(t){return new I(t.target,t.broadcastRect())}));this.callback_.call(t,e,t),this.clearActive()}},t.prototype.clearActive=function(){this.activeObservations_.splice(0)},t.prototype.hasActive=function(){return this.activeObservations_.length>0},t}(),H="undefined"!=typeof WeakMap?new WeakMap:new _,D=function t(e){if(!(this instanceof t))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var n=L.getInstance(),o=new R(e,n,this);H.set(this,o)};["observe","unobserve","disconnect"].forEach((function(t){D.prototype[t]=function(){var e;return(e=H.get(this))[t].apply(e,arguments)}}));void 0!==w.ResizeObserver&&w.ResizeObserver;const $=class extends Event{constructor(t,e={}){super($.parentWindowEvent),this.eventName=t,this.eventParameters=e}get name(){return this.eventName}get parameters(){return this.eventParameters}};$.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const W=y,q="#feature_flag_beta_submit",F='form[name="feature_flag_beta"]',G='form[name="feature_flag_beta"] input',N='form[name="feature_flag_stable"]',V='form[name="feature_flag_stable"] input',Q="#feature_flag_stable_submit",{$:J}=window;J((()=>{const t=J(q),e=J(Q),n=J(F),o=J(G),i=J(N),r=J(V),s=i.serialize(),a=n.serialize(),c=n.serializeArray();o.change((()=>{t.prop("disabled",a===n.serialize())})),r.change((()=>{e.prop("disabled",s===i.serialize())})),t.on("click",(e=>{e.preventDefault();const o=n.serializeArray();if(a===n.serialize())return;let i=!1;for(let t=0;t<o.length;t+=1)if("form[_token]"!==o[t].name&&"0"!==o[t].value&&"0"===c[t].value){i=!0;break}const r=new W({id:"modal-confirm-submit-feature-flag",confirmTitle:t.data("modal-title"),confirmMessage:t.data("modal-message"),confirmButtonLabel:t.data("modal-apply"),closeButtonLabel:t.data("modal-cancel")},(()=>{n.submit()}));i?r.show():n.submit()}))}))})(),window.feature_flag=o})();