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
import{L as k,D}from"./sentry-96f4e833.js";import"./dom-utils-d77254b8.js";function f(a,s){return Object.keys(s).reduce(function(e,i){if(i.startsWith(a)){var r=i.substr(a.length);e[r]=s[i]}return e},{})}function q(a,s){var e=document.createElement("a");e.href=s;var i=e.search.slice(1),r=i.split("&").reduce(function(o,P){var j=P.split("="),b=j[0],S=j[1];return o[b]=k(S),o},{}),t=[],p=r.ajs_uid,c=r.ajs_event,m=r.ajs_aid,n=D(a.options.useQueryString)?a.options.useQueryString:{},u=n.aid,l=u===void 0?/.+/:u,_=n.uid,A=_===void 0?/.+/:_;if(m){var d=Array.isArray(r.ajs_aid)?r.ajs_aid[0]:r.ajs_aid;l.test(d)&&a.setAnonymousId(d)}if(p){var v=Array.isArray(r.ajs_uid)?r.ajs_uid[0]:r.ajs_uid;if(A.test(v)){var h=f("ajs_trait_",r);t.push(a.identify(v,h))}}if(c){var y=Array.isArray(r.ajs_event)?r.ajs_event[0]:r.ajs_event,g=f("ajs_prop_",r);t.push(a.track(y,g))}return Promise.all(t)}export{q as queryString};
