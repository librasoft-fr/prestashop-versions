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
import{_ as p,o as v,A as g,q as M,p as _}from"./sentry-96f4e833.js";import"./dom-utils-d77254b8.js";function P(s,w,h){var i;return p(this,void 0,void 0,function(){var o,c,l,d,n,b=this;return v(this,function(u){switch(u.label){case 0:return g()?[2,[]]:(o=M(),c=(i=w.enabledMiddleware)!==null&&i!==void 0?i:{},l=Object.entries(c).filter(function(r){r[0];var e=r[1];return e}).map(function(r){var e=r[0];return e}),d=l.map(function(r){return p(b,void 0,void 0,function(){var e,a,m,f;return v(this,function(t){switch(t.label){case 0:e=r.replace("@segment/",""),a=e,h&&(a=btoa(e).replace(/=/g,"")),m="".concat(o,"/middleware/").concat(a,"/latest/").concat(a,".js.gz"),t.label=1;case 1:return t.trys.push([1,3,,4]),[4,_(m)];case 2:return t.sent(),[2,window["".concat(e,"Middleware")]];case 3:return f=t.sent(),s.log("error",f),s.stats.increment("failed_remote_middleware"),[3,4];case 4:return[2]}})})}),[4,Promise.all(d)]);case 1:return n=u.sent(),n=n.filter(Boolean),[2,n]}})})}export{P as remoteMiddlewares};
