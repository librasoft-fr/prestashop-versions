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
 */function a(e){document.readyState==="complete"||document.readyState==="interactive"?setTimeout(e,1):document.addEventListener("DOMContentLoaded",e)}function u(e){const n=document.createElement("div");return n.innerHTML=e,n.firstElementChild!==null?n.firstElementChild:n}function d(e,n){const t=r(e);if(t!=null){const i=t.parentElement;if(i!==null){const l=u(n);i.insertBefore(l,t)}}}function f(e,n){const t=r(e);if(t!=null){const i=u(n);t.append(i)}}function m(e,n){const t=r(e);if(t!=null){const i=u(n);t.prepend(i)}}function g(e,n){const t=s(e);t.length!==0&&t.forEach(i=>{i.innerHTML=n})}function h(e){const n=new DataTransfer;return e.forEach(t=>{n.items.add(t)}),n.files}function r(e){if(typeof e=="string")return document.querySelector(e);if(typeof e=="object"&&e.nodeName!=="")return e}function s(e){return typeof e=="string"?document.querySelectorAll(e):e}function p(e,n){const t=r(e);t!=null&&(n?t.style.display="":t.style.display="none")}function c(e,n){let t;return n===!0?t=document.querySelector(`link[href="${e}"]`):t=document.querySelector(`link[href*="${e}"]`),t!==null}function y(e,n,t){if(c(e,!0))return;const i=document.head,l=document.createElement("link");l.href=e,n!=null&&(l.rel=n),t===!0&&(l.crossOrigin=""),i.append(l)}function E(e){const n=e+"=",i=decodeURIComponent(document.cookie).split(";");for(let l=0;l<i.length;l++){let o=i[l];for(;o.charAt(0)===" ";)o=o.substring(1);if(o.indexOf(n)===0)return o.substring(n.length,o.length)}return""}function L(e){let n=`${e.key}=${e.value};`;e.maxAge!=null&&e.maxAge>0&&(n+=`max-age=${e.maxAge};`),e.path!=null&&(n+=`path=${e.path};`),e.samesite!=null&&(n+=`samesite=${e.samesite};`),document.cookie=n}export{f as a,y as b,g as c,a as d,h as e,E as f,r as g,d as i,m as p,L as s,p as t};
