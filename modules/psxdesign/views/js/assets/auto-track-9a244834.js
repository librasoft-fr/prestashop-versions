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
import{K as d}from"./sentry-96f4e833.js";import"./dom-utils-d77254b8.js";function b(t){var r=t;return!!(r.ctrlKey||r.shiftKey||r.metaKey||r.button&&r.button==1)}function w(t,r){return!!(t.target==="_blank"&&r)}function y(t,r,e,a){var f=this,o=[];return t?(t instanceof Element?o=[t]:"toArray"in t?o=t.toArray():o=t,o.forEach(function(n){n.addEventListener("click",function(i){var c,u,s=r instanceof Function?r(n):r,v=e instanceof Function?e(n):e,h=n.getAttribute("href")||n.getAttributeNS("http://www.w3.org/1999/xlink","href")||n.getAttribute("xlink:href")||((c=n.getElementsByTagName("a")[0])===null||c===void 0?void 0:c.getAttribute("href")),l=d(f.track(s,v,a??{}),(u=f.settings.timeout)!==null&&u!==void 0?u:500);!w(n,h)&&!b(i)&&h&&(i.preventDefault?i.preventDefault():i.returnValue=!1,l.catch(console.error).then(function(){window.location.href=h}).catch(console.error))},!1)}),this):this}function E(t,r,e,a){var f=this;if(!t)return this;t instanceof HTMLFormElement&&(t=[t]);var o=t;return o.forEach(function(n){if(!(n instanceof Element))throw new TypeError("Must pass HTMLElement to trackForm/trackSubmit.");var i=function(u){var s;u.preventDefault?u.preventDefault():u.returnValue=!1;var v=r instanceof Function?r(n):r,h=e instanceof Function?e(n):e,l=d(f.track(v,h,a??{}),(s=f.settings.timeout)!==null&&s!==void 0?s:500);l.catch(console.error).then(function(){n.submit()}).catch(console.error)},c=window.jQuery||window.Zepto;c?c(n).submit(i):n.addEventListener("submit",i,!1)}),this}export{E as form,y as link};
