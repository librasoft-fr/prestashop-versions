(()=>{var e={5158:(e,t,r)=>{"use strict";r.d(t,{Z:()=>o});const o=
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
class{constructor(e){this.message=e,this.name="LocalizationException"}}},1527:(e,t,r)=>{"use strict";r.d(t,{NumberFormatter:()=>a});var o=r(9475),s=r(3368),n=r(6965);
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
const i=r(1658);class d{constructor(e){this.numberSpecification=e}format(e,t){void 0!==t&&(this.numberSpecification=t);const r=Math.abs(e).toFixed(this.numberSpecification.getMaxFractionDigits());let[o,s]=this.extractMajorMinorDigits(r);o=this.splitMajorGroups(o),s=this.adjustMinorDigitsZeroes(s);let n=o;s&&(n+="."+s);const i=this.getCldrPattern(e<0);return n=this.addPlaceholders(n,i),n=this.replaceSymbols(n),n=this.performSpecificReplacements(n),n}extractMajorMinorDigits(e){const t=e.toString().split(".");return[t[0],void 0===t[1]?"":t[1]]}splitMajorGroups(e){if(!this.numberSpecification.isGroupingUsed())return e;const t=e.split("").reverse();let r=[];for(r.push(t.splice(0,this.numberSpecification.getPrimaryGroupSize()));t.length;)r.push(t.splice(0,this.numberSpecification.getSecondaryGroupSize()));r=r.reverse();const o=[];return r.forEach((e=>{o.push(e.reverse().join(""))})),o.join(",")}adjustMinorDigitsZeroes(e){let t=e;return t.length>this.numberSpecification.getMaxFractionDigits()&&(t=t.replace(/0+$/,"")),t.length<this.numberSpecification.getMinFractionDigits()&&(t=t.padEnd(this.numberSpecification.getMinFractionDigits(),"0")),t}getCldrPattern(e){return e?this.numberSpecification.getNegativePattern():this.numberSpecification.getPositivePattern()}replaceSymbols(e){const t=this.numberSpecification.getSymbol(),r={};return r["."]=t.getDecimal(),r[","]=t.getGroup(),r["-"]=t.getMinusSign(),r["%"]=t.getPercentSign(),r["+"]=t.getPlusSign(),this.strtr(e,r)}strtr(e,t){const r=Object.keys(t).map(i);return e.split(RegExp(`(${r.join("|")})`)).map((e=>t[e]||e)).join("")}addPlaceholders(e,t){return t.replace(/#?(,#+)*0(\.[0#]+)*/,e)}performSpecificReplacements(e){return this.numberSpecification instanceof s.Z?e.split("¤").join(this.numberSpecification.getCurrencySymbol()):e}static build(e){let t,r;return t=void 0!==e.numberSymbols?new o.Z(...e.numberSymbols):new o.Z(...e.symbol),r=e.currencySymbol?new s.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize,e.currencySymbol,e.currencyCode):new n.Z(e.positivePattern,e.negativePattern,t,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize),new d(r)}}const a=d}
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
 */,9475:(e,t,r)=>{"use strict";r.d(t,{Z:()=>s});var o=r(5158);
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
 */const s=class{constructor(e,t,r,o,s,n,i,d,a,c,l){this.decimal=e,this.group=t,this.list=r,this.percentSign=o,this.minusSign=s,this.plusSign=n,this.exponential=i,this.superscriptingExponent=d,this.perMille=a,this.infinity=c,this.nan=l,this.validateData()}getDecimal(){return this.decimal}getGroup(){return this.group}getList(){return this.list}getPercentSign(){return this.percentSign}getMinusSign(){return this.minusSign}getPlusSign(){return this.plusSign}getExponential(){return this.exponential}getSuperscriptingExponent(){return this.superscriptingExponent}getPerMille(){return this.perMille}getInfinity(){return this.infinity}getNan(){return this.nan}validateData(){if(!this.decimal||"string"!=typeof this.decimal)throw new o.Z("Invalid decimal");if(!this.group||"string"!=typeof this.group)throw new o.Z("Invalid group");if(!this.list||"string"!=typeof this.list)throw new o.Z("Invalid symbol list");if(!this.percentSign||"string"!=typeof this.percentSign)throw new o.Z("Invalid percentSign");if(!this.minusSign||"string"!=typeof this.minusSign)throw new o.Z("Invalid minusSign");if(!this.plusSign||"string"!=typeof this.plusSign)throw new o.Z("Invalid plusSign");if(!this.exponential||"string"!=typeof this.exponential)throw new o.Z("Invalid exponential");if(!this.superscriptingExponent||"string"!=typeof this.superscriptingExponent)throw new o.Z("Invalid superscriptingExponent");if(!this.perMille||"string"!=typeof this.perMille)throw new o.Z("Invalid perMille");if(!this.infinity||"string"!=typeof this.infinity)throw new o.Z("Invalid infinity");if(!this.nan||"string"!=typeof this.nan)throw new o.Z("Invalid nan")}}},6965:(e,t,r)=>{"use strict";r.d(t,{Z:()=>n});var o=r(5158),s=r(9475);const n=
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
class{constructor(e,t,r,n,i,d,a,c){if(this.positivePattern=e,this.negativePattern=t,this.symbol=r,this.maxFractionDigits=n,this.minFractionDigits=n<i?n:i,this.groupingUsed=d,this.primaryGroupSize=a,this.secondaryGroupSize=c,!this.positivePattern||"string"!=typeof this.positivePattern)throw new o.Z("Invalid positivePattern");if(!this.negativePattern||"string"!=typeof this.negativePattern)throw new o.Z("Invalid negativePattern");if(!(this.symbol&&this.symbol instanceof s.Z))throw new o.Z("Invalid symbol");if("number"!=typeof this.maxFractionDigits)throw new o.Z("Invalid maxFractionDigits");if("number"!=typeof this.minFractionDigits)throw new o.Z("Invalid minFractionDigits");if("boolean"!=typeof this.groupingUsed)throw new o.Z("Invalid groupingUsed");if("number"!=typeof this.primaryGroupSize)throw new o.Z("Invalid primaryGroupSize");if("number"!=typeof this.secondaryGroupSize)throw new o.Z("Invalid secondaryGroupSize")}getSymbol(){return this.symbol}getPositivePattern(){return this.positivePattern}getNegativePattern(){return this.negativePattern}getMaxFractionDigits(){return this.maxFractionDigits}getMinFractionDigits(){return this.minFractionDigits}isGroupingUsed(){return this.groupingUsed}getPrimaryGroupSize(){return this.primaryGroupSize}getSecondaryGroupSize(){return this.secondaryGroupSize}}},3368:(e,t,r)=>{"use strict";r.d(t,{Z:()=>i});var o=r(5158),s=r(6965);class n extends s.Z{constructor(e,t,r,s,n,i,d,a,c,l){if(super(e,t,r,s,n,i,d,a),this.currencySymbol=c,this.currencyCode=l,!this.currencySymbol||"string"!=typeof this.currencySymbol)throw new o.Z("Invalid currencySymbol");if(!this.currencyCode||"string"!=typeof this.currencyCode)throw new o.Z("Invalid currencyCode")}static getCurrencyDisplay(){return"symbol"}getCurrencySymbol(){return this.currencySymbol}getCurrencyCode(){return this.currencyCode}}const i=n},7187:e=>{"use strict";var t,r="object"==typeof Reflect?Reflect:null,o=r&&"function"==typeof r.apply?r.apply:function(e,t,r){return Function.prototype.apply.call(e,t,r)};t=r&&"function"==typeof r.ownKeys?r.ownKeys:Object.getOwnPropertySymbols?function(e){return Object.getOwnPropertyNames(e).concat(Object.getOwnPropertySymbols(e))}:function(e){return Object.getOwnPropertyNames(e)};var s=Number.isNaN||function(e){return e!=e};function n(){n.init.call(this)}e.exports=n,e.exports.once=function(e,t){return new Promise((function(r,o){function s(r){e.removeListener(t,n),o(r)}function n(){"function"==typeof e.removeListener&&e.removeListener("error",s),r([].slice.call(arguments))}f(e,t,n,{once:!0}),"error"!==t&&function(e,t,r){"function"==typeof e.on&&f(e,"error",t,r)}(e,s,{once:!0})}))},n.EventEmitter=n,n.prototype._events=void 0,n.prototype._eventsCount=0,n.prototype._maxListeners=void 0;var i=10;function d(e){if("function"!=typeof e)throw new TypeError('The "listener" argument must be of type Function. Received type '+typeof e)}function a(e){return void 0===e._maxListeners?n.defaultMaxListeners:e._maxListeners}function c(e,t,r,o){var s,n,i,c;if(d(r),void 0===(n=e._events)?(n=e._events=Object.create(null),e._eventsCount=0):(void 0!==n.newListener&&(e.emit("newListener",t,r.listener?r.listener:r),n=e._events),i=n[t]),void 0===i)i=n[t]=r,++e._eventsCount;else if("function"==typeof i?i=n[t]=o?[r,i]:[i,r]:o?i.unshift(r):i.push(r),(s=a(e))>0&&i.length>s&&!i.warned){i.warned=!0;var l=new Error("Possible EventEmitter memory leak detected. "+i.length+" "+String(t)+" listeners added. Use emitter.setMaxListeners() to increase limit");l.name="MaxListenersExceededWarning",l.emitter=e,l.type=t,l.count=i.length,c=l,console&&console.warn&&console.warn(c)}return e}function l(){if(!this.fired)return this.target.removeListener(this.type,this.wrapFn),this.fired=!0,0===arguments.length?this.listener.call(this.target):this.listener.apply(this.target,arguments)}function u(e,t,r){var o={fired:!1,wrapFn:void 0,target:e,type:t,listener:r},s=l.bind(o);return s.listener=r,o.wrapFn=s,s}function h(e,t,r){var o=e._events;if(void 0===o)return[];var s=o[t];return void 0===s?[]:"function"==typeof s?r?[s.listener||s]:[s]:r?function(e){for(var t=new Array(e.length),r=0;r<t.length;++r)t[r]=e[r].listener||e[r];return t}(s):m(s,s.length)}function p(e){var t=this._events;if(void 0!==t){var r=t[e];if("function"==typeof r)return 1;if(void 0!==r)return r.length}return 0}function m(e,t){for(var r=new Array(t),o=0;o<t;++o)r[o]=e[o];return r}function f(e,t,r,o){if("function"==typeof e.on)o.once?e.once(t,r):e.on(t,r);else{if("function"!=typeof e.addEventListener)throw new TypeError('The "emitter" argument must be of type EventEmitter. Received type '+typeof e);e.addEventListener(t,(function s(n){o.once&&e.removeEventListener(t,s),r(n)}))}}Object.defineProperty(n,"defaultMaxListeners",{enumerable:!0,get:function(){return i},set:function(e){if("number"!=typeof e||e<0||s(e))throw new RangeError('The value of "defaultMaxListeners" is out of range. It must be a non-negative number. Received '+e+".");i=e}}),n.init=function(){void 0!==this._events&&this._events!==Object.getPrototypeOf(this)._events||(this._events=Object.create(null),this._eventsCount=0),this._maxListeners=this._maxListeners||void 0},n.prototype.setMaxListeners=function(e){if("number"!=typeof e||e<0||s(e))throw new RangeError('The value of "n" is out of range. It must be a non-negative number. Received '+e+".");return this._maxListeners=e,this},n.prototype.getMaxListeners=function(){return a(this)},n.prototype.emit=function(e){for(var t=[],r=1;r<arguments.length;r++)t.push(arguments[r]);var s="error"===e,n=this._events;if(void 0!==n)s=s&&void 0===n.error;else if(!s)return!1;if(s){var i;if(t.length>0&&(i=t[0]),i instanceof Error)throw i;var d=new Error("Unhandled error."+(i?" ("+i.message+")":""));throw d.context=i,d}var a=n[e];if(void 0===a)return!1;if("function"==typeof a)o(a,this,t);else{var c=a.length,l=m(a,c);for(r=0;r<c;++r)o(l[r],this,t)}return!0},n.prototype.addListener=function(e,t){return c(this,e,t,!1)},n.prototype.on=n.prototype.addListener,n.prototype.prependListener=function(e,t){return c(this,e,t,!0)},n.prototype.once=function(e,t){return d(t),this.on(e,u(this,e,t)),this},n.prototype.prependOnceListener=function(e,t){return d(t),this.prependListener(e,u(this,e,t)),this},n.prototype.removeListener=function(e,t){var r,o,s,n,i;if(d(t),void 0===(o=this._events))return this;if(void 0===(r=o[e]))return this;if(r===t||r.listener===t)0==--this._eventsCount?this._events=Object.create(null):(delete o[e],o.removeListener&&this.emit("removeListener",e,r.listener||t));else if("function"!=typeof r){for(s=-1,n=r.length-1;n>=0;n--)if(r[n]===t||r[n].listener===t){i=r[n].listener,s=n;break}if(s<0)return this;0===s?r.shift():function(e,t){for(;t+1<e.length;t++)e[t]=e[t+1];e.pop()}(r,s),1===r.length&&(o[e]=r[0]),void 0!==o.removeListener&&this.emit("removeListener",e,i||t)}return this},n.prototype.off=n.prototype.removeListener,n.prototype.removeAllListeners=function(e){var t,r,o;if(void 0===(r=this._events))return this;if(void 0===r.removeListener)return 0===arguments.length?(this._events=Object.create(null),this._eventsCount=0):void 0!==r[e]&&(0==--this._eventsCount?this._events=Object.create(null):delete r[e]),this;if(0===arguments.length){var s,n=Object.keys(r);for(o=0;o<n.length;++o)"removeListener"!==(s=n[o])&&this.removeAllListeners(s);return this.removeAllListeners("removeListener"),this._events=Object.create(null),this._eventsCount=0,this}if("function"==typeof(t=r[e]))this.removeListener(e,t);else if(void 0!==t)for(o=t.length-1;o>=0;o--)this.removeListener(e,t[o]);return this},n.prototype.listeners=function(e){return h(this,e,!0)},n.prototype.rawListeners=function(e){return h(this,e,!1)},n.listenerCount=function(e,t){return"function"==typeof e.listenerCount?e.listenerCount(t):p.call(e,t)},n.prototype.listenerCount=p,n.prototype.eventNames=function(){return this._eventsCount>0?t(this._events):[]}},2564:e=>{"use strict";var t=Object.assign||function(e){for(var t,r=1;r<arguments.length;r++)for(var o in t=arguments[r])Object.prototype.hasOwnProperty.call(t,o)&&(e[o]=t[o]);return e},r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};e.exports=new function e(){var o=this;(function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")})(this,e),this.setRoutes=function(e){o.routesRouting=e||[]},this.getRoutes=function(){return o.routesRouting},this.setBaseUrl=function(e){o.contextRouting.base_url=e},this.getBaseUrl=function(){return o.contextRouting.base_url},this.setPrefix=function(e){o.contextRouting.prefix=e},this.setScheme=function(e){o.contextRouting.scheme=e},this.getScheme=function(){return o.contextRouting.scheme},this.setHost=function(e){o.contextRouting.host=e},this.getHost=function(){return o.contextRouting.host},this.buildQueryParams=function(e,t,s){var n=new RegExp(/\[]$/);t instanceof Array?t.forEach((function(t,i){n.test(e)?s(e,t):o.buildQueryParams(e+"["+("object"===(void 0===t?"undefined":r(t))?i:"")+"]",t,s)})):"object"===(void 0===t?"undefined":r(t))?Object.keys(t).forEach((function(r){return o.buildQueryParams(e+"["+r+"]",t[r],s)})):s(e,t)},this.getRoute=function(e){var t=o.contextRouting.prefix+e;if(o.routesRouting[t])return o.routesRouting[t];if(!o.routesRouting[e])throw new Error('The route "'+e+'" does not exist.');return o.routesRouting[e]},this.generate=function(e,r,s){var n=o.getRoute(e),i=r||{},d=t({},i),a="_scheme",c="",l=!0,u="";if((n.tokens||[]).forEach((function(t){if("text"===t[0])return c=t[1]+c,void(l=!1);if("variable"!==t[0])throw new Error('The token type "'+t[0]+'" is not supported.');var r=(n.defaults||{})[t[3]];if(0==l||!r||(i||{})[t[3]]&&i[t[3]]!==n.defaults[t[3]]){var o;if((i||{})[t[3]])o=i[t[3]],delete d[t[3]];else{if(!r){if(l)return;throw new Error('The route "'+e+'" requires the parameter "'+t[3]+'".')}o=n.defaults[t[3]]}if(!(!0===o||!1===o||""===o)||!l){var s=encodeURIComponent(o).replace(/%2F/g,"/");"null"===s&&null===o&&(s=""),c=t[1]+s+c}l=!1}else r&&delete d[t[3]]})),""==c&&(c="/"),(n.hosttokens||[]).forEach((function(e){var t;return"text"===e[0]?void(u=e[1]+u):void("variable"===e[0]&&((i||{})[e[3]]?(t=i[e[3]],delete d[e[3]]):n.defaults[e[3]]&&(t=n.defaults[e[3]]),u=e[1]+t+u))})),c=o.contextRouting.base_url+c,n.requirements[a]&&o.getScheme()!==n.requirements[a]?c=n.requirements[a]+"://"+(u||o.getHost())+c:u&&o.getHost()!==u?c=o.getScheme()+"://"+u+c:!0===s&&(c=o.getScheme()+"://"+o.getHost()+c),0<Object.keys(d).length){var h=[],p=function(e,t){var r=t;r=null===(r="function"==typeof r?r():r)?"":r,h.push(encodeURIComponent(e)+"="+encodeURIComponent(r))};Object.keys(d).forEach((function(e){return o.buildQueryParams(e,d[e],p)})),c=c+"?"+h.join("&").replace(/%20/g,"+")}return c},this.setData=function(e){o.setBaseUrl(e.base_url),o.setRoutes(e.routes),"prefix"in e&&o.setPrefix(e.prefix),o.setHost(e.host),o.setScheme(e.scheme)},this.contextRouting={base_url:"",prefix:"",host:"",scheme:""}}},1658:(e,t,r)=>{var o="[object Symbol]",s=/[\\^$.*+?()[\]{}|]/g,n=RegExp(s.source),i="object"==typeof r.g&&r.g&&r.g.Object===Object&&r.g,d="object"==typeof self&&self&&self.Object===Object&&self,a=i||d||Function("return this")(),c=Object.prototype.toString,l=a.Symbol,u=l?l.prototype:void 0,h=u?u.toString:void 0;function p(e){if("string"==typeof e)return e;if(function(e){return"symbol"==typeof e||function(e){return!!e&&"object"==typeof e}(e)&&c.call(e)==o}(e))return h?h.call(e):"";var t=e+"";return"0"==t&&1/e==-Infinity?"-0":t}e.exports=function(e){var t;return(e=null==(t=e)?"":p(t))&&n.test(e)?e.replace(s,"\\$&"):e}},9567:e=>{"use strict";e.exports=window.jQuery}},t={};function r(o){var s=t[o];if(void 0!==s)return s.exports;var n=t[o]={exports:{}};return e[o](n,n.exports,r),n.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var o in t)r.o(t,o)&&!r.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},r.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),r.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o={};(()=>{"use strict";r.r(o);
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
 */const e="#order-view-page",t=".js-payment-details-btn",s="#order_payment_amount_currency_amount",n="#order_payment_id_invoice",i="#view_order_payments_block",d=".js-view-order-payments-alert",a=".js-private-note-toggle-btn",c=".js-private-note-block",l="#private_note_note",u=".js-private-note-btn",h="#addOrderDiscountModal",p="#add_order_cart_rule_invoice_id",m="#add_order_cart_rule_name",f="#add_order_cart_rule_type",v="#add_order_cart_rule_value",g="#add_order_cart_rule_value_unit",b="#add_order_cart_rule_submit",_="#add_order_cart_rule_apply_on_all_invoices",x=".js-cart-rule-value-help",y="#update_order_status_action_btn",I="#update_order_status_action_input",P="#update_order_status_action_input_wrapper",w=".js-update-shipping-btn",T="#update_order_shipping_tracking_number",k="#update_order_shipping_current_order_carrier_id",E="#updateCustomerAddressModal",S=".js-update-customer-address-modal-btn",O="#change_order_address_address_type",R="#js-delivery-address-edit-btn",C="#js-invoice-address-edit-btn",q="#order_message_order_message",j=".js-order-messages-container",L="#order_message_message",M=".js-message-change-warning",F="#orderDocumentsTab .count",D="#orderDocumentsTabContent .card-body",$="#orderShippingTab .count",A="#orderShippingTabContent .card-body",N="#view_all_messages_modal",G="#all-messages-list",B=".js-open-all-messages-btn",Z="#orderProductsOriginalPosition",H="#orderProductsModificationPosition",z="#orderProductsPanel",U="#orderProductsPanelCount",Q=".js-order-product-delete-btn",V="#orderProductsTable",J=".order-product-pagination",W="#orderProductsNavPagination",K="#orderProductsTablePagination",X="#orderProductsTablePaginationNext",Y="#orderProductsTablePaginationPrev",ee=".page-item:not(.d-none):not(#orderProductsTablePaginationNext):not(#orderProductsTablePaginationPrev) .page-link",te="#orderProductsTablePagination .page-item.active span",re="#orderProductsTablePagination .page-item.d-none",oe="#orderProductsTablePaginationNumberSelector",se=e=>`#orderProduct_${e}`,ne=e=>`#editOrderProduct_${e}`,ie="tr.cellProduct",de="tr .cellProductLocation",ae="tr .cellProductRefunded",ce="tr:not(.d-none) .cellProductLocation",le="tr:not(.d-none) .cellProductRefunded",ue="#orderProductsTable .order-product-customization",he=".js-order-product-edit-btn",pe=e=>`#orderProduct_${e} .js-order-product-edit-btn`,me="#addProductBtn",fe=".js-product-action-btn",ve="#add_product_row_add",ge="#add_product_row_cancel",be="#addProductTableRow",_e="#add_product_row_search",xe="#addProductTableRow .dropdown .dropdown-menu",ye="#add_product_row_product_id",Ie="#add_product_row_tax_rate",Pe="#addProductCombinations",we="#addProductCombinationId",Te="#add_product_row_price_tax_excluded",ke="#add_product_row_price_tax_included",Ee="#add_product_row_quantity",Se="#addProductAvailable",Oe="#addProductLocation",Re="#addProductTotalPrice",Ce="#add_product_row_invoice",qe="#add_product_row_free_shipping",je="#addProductNewInvoiceInfo",Le=".productEditSaveBtn",Me=".productEditCancelBtn",Fe="#editProductTableRowTemplate",De=".editProductRow",$e=".cellProductImg",Ae=".cellProductName",Ne=".cellProductUnitPrice",Ge=".cellProductQuantity",Be=".cellProductAvailableQuantity",Ze=".cellProductTotalPrice",He=".editProductPriceTaxExcl",ze=".editProductPriceTaxIncl",Ue=".editProductInvoice",Qe=".editProductQuantity",Ve=".editProductLocation",Je=".editProductAvailable",We=".editProductTotalPrice",Ke={list:".table.discountList"},Xe={modal:"#product-pack-modal",table:"#product-pack-modal-table tbody",rows:"#product-pack-modal-table tbody tr:not(#template-pack-table-row)",template:"#template-pack-table-row",product:{img:".cell-product-img img",link:".cell-product-name a",name:".cell-product-name .product-name",ref:".cell-product-name .product-reference",supplierRef:".cell-product-name .product-supplier-reference",quantity:".cell-product-quantity",availableQuantity:".cell-product-available-quantity"}},Ye="#orderProductsTotal",et="#order-discounts-total-container",tt="#orderDiscountsTotal",rt="#order-shipping-total-container",ot="#orderShippingTotal",st="#orderTaxesTotal",nt="#orderTotal",it="#order_hook_tabs",dt={form:'form[name="cancel_product"]',buttons:{abort:"button.cancel-product-element-abort",save:"#cancel_product_save",partialRefund:"button.partial-refund-display",standardRefund:"button.standard-refund-display",returnProduct:"button.return-product-display",cancelProducts:"button.cancel-product-display"},inputs:{quantity:".cancel-product-quantity input",amount:".cancel-product-amount input",selector:".cancel-product-selector input"},table:{cell:".cancel-product-cell",header:"th.cancel-product-element p",actions:"td.cellProductActions, th.product_actions"},checkboxes:{restock:"#cancel_product_restock",creditSlip:"#cancel_product_credit_slip",voucher:"#cancel_product_voucher"},radios:{voucherRefundType:{productPrices:'input[voucher-refund-type="0"]',productPricesVoucherExcluded:'input[voucher-refund-type="1"]',negativeErrorMessage:".voucher-refund-type-negative-error"}},toggle:{partialRefund:".cancel-product-element:not(.hidden):not(.shipping-refund), .cancel-product-amount",standardRefund:".cancel-product-element:not(.hidden):not(.shipping-refund-amount):not(.restock-products), .cancel-product-selector",returnProduct:".cancel-product-element:not(.hidden):not(.shipping-refund-amount), .cancel-product-selector",cancelProducts:".cancel-product-element:not(.hidden):not(.shipping-refund-amount):not(.shipping-refund):not(.restock-products):not(.refund-credit-slip):not(.refund-voucher):not(.voucher-refund-type), .cancel-product-selector"}},at=".js-print-order-view-page",ct=".js-order-notes-toggle-btn",lt=".js-order-notes-block",ut="#internal_note_note",ht=".js-order-notes-btn",pt="#orderProductsPanel .spinner-order-products-container#orderProductsLoading",{$:mt}=window;class ft{constructor(){this.initOrderShippingUpdateEventHandler()}initOrderShippingUpdateEventHandler(){mt(e).on("click",w,(e=>{const t=mt(e.currentTarget);mt(T).val(t.data("order-tracking-number")),mt(k).val(t.data("order-carrier-id"))}))}}
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
const{$:vt}=window;class gt{constructor(){this.setupListeners()}setupListeners(){this.initShowNoteFormEventHandler(),this.initCloseNoteFormEventHandler(),this.initEnterPaymentEventHandler()}initShowNoteFormEventHandler(){vt(".js-open-invoice-note-btn").on("click",(e=>{e.preventDefault();vt(e.currentTarget).closest("tr").next().removeClass("d-none")}))}initCloseNoteFormEventHandler(){vt(".js-cancel-invoice-note-btn").on("click",(e=>{vt(e.currentTarget).closest("tr").addClass("d-none")}))}initEnterPaymentEventHandler(){vt(".js-enter-payment-btn").on("click",(e=>{var t;const r=vt(e.currentTarget).data("payment-amount");null==(t=vt(i).get(0))||t.scrollIntoView({behavior:"smooth"}),vt(s).val(r)}))}}var bt=r(2564),_t=r.n(bt);const xt=JSON.parse('{"base_url":"","routes":{"admin_common_notifications":{"tokens":[["text","/common/notifications"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_product_form":{"tokens":[["variable","/","\\\\d+","id"],["text","/sell/catalog/products"]],"defaults":[],"requirements":{"id":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_feature_get_feature_values":{"tokens":[["variable","/","\\\\d+","idFeature"],["text","/sell/catalog/products/features"]],"defaults":{"idFeature":0},"requirements":{"idFeature":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations":{"tokens":[["text","/combinations"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_ids":{"tokens":[["text","/combinations/ids"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_update_combination_from_listing":{"tokens":[["text","/update-combination-from-listing"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_edit_combination":{"tokens":[["text","/edit"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":[],"requirements":{"combinationId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_combinations_bulk_edit_combination":{"tokens":[["text","/combinations/bulk-edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_combinations_delete_combination":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/delete"],["variable","/","\\\\d+","combinationId"],["text","/sell/catalog/products-v2/combinations"]],"defaults":{"shopId":null},"requirements":{"combinationId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_combinations_bulk_delete":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/combinations/bulk-delete"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":{"shopId":null},"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_attribute_groups":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/attribute-groups"],["variable","/","[^/]++","productId"],["text","/sell/catalog/products-v2"]],"defaults":{"shopId":null},"requirements":{"shopId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_all_attribute_groups":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/sell/catalog/products-v2/all-attribute-groups"]],"defaults":{"shopId":null},"requirements":{"shopId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_combinations_generate":{"tokens":[["variable","/","\\\\d+","shopId"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2/generate-combinations"]],"defaults":{"shopId":null},"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_images_for_shop":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/images-for-shop"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_product_shop_images":{"tokens":[["text","/shopImages"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_add_image":{"tokens":[["text","/sell/catalog/products-v2/images/add"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_update_image":{"tokens":[["text","/update"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["PATCH"],"schemes":[]},"admin_products_delete_image":{"tokens":[["text","/delete"],["variable","/","\\\\d+","productImageId"],["text","/sell/catalog/products-v2/images"]],"defaults":[],"requirements":{"productImageId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_specific_prices_list":{"tokens":[["text","/specific-prices/list"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_specific_prices_create":{"tokens":[["text","/specific-prices/create"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_products_specific_prices_delete":{"tokens":[["text","/delete"],["variable","/","\\\\d+","specificPriceId"],["text","/sell/catalog/products-v2/specific-prices"]],"defaults":[],"requirements":{"specificPriceId":"\\\\d+"},"hosttokens":[],"methods":["DELETE"],"schemes":[]},"admin_products_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST","PATCH"],"schemes":[]},"admin_products_select_shops":{"tokens":[["text","/shops"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST","PATCH"],"schemes":[]},"admin_products_bulk_enable_all_shops":{"tokens":[["text","/sell/catalog/products-v2/bulk-enable-all-shops"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_enable_shop":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/sell/catalog/products-v2/bulk-enable-shop"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_enable_shop_group":{"tokens":[["variable","/","\\\\d+","shopGroupId"],["text","/sell/catalog/products-v2/bulk-enable-shop-group"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopGroupId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_disable_all_shops":{"tokens":[["text","/sell/catalog/products-v2/bulk-disable-for-all-shops"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_disable_shop":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/sell/catalog/products-v2/bulk-disable-shop"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_disable_shop_group":{"tokens":[["variable","/","\\\\d+","shopGroupId"],["text","/sell/catalog/products-v2/bulk-disable-shop-group"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopGroupId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_duplicate_all_shops":{"tokens":[["text","/sell/catalog/products-v2/bulk-duplicate-all-shops"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_duplicate_shop":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/sell/catalog/products-v2/bulk-duplicate-shop"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_duplicate_shop_group":{"tokens":[["variable","/","\\\\d+","shopGroupId"],["text","/sell/catalog/products-v2/bulk-duplicate-shop-group"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopGroupId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_products_bulk_delete_from_all_shops":{"tokens":[["text","/sell/catalog/products-v2/bulk-delete-from-all-shops"]],"defaults":[],"requirements":{"productId":"\\\\d+"},"hosttokens":[],"methods":["POST","DELETE"],"schemes":[]},"admin_products_bulk_delete_from_shop":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/sell/catalog/products-v2/bulk-delete-from-shop"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["POST","DELETE"],"schemes":[]},"admin_products_bulk_delete_from_shop_group":{"tokens":[["variable","/","\\\\d+","shopGroupId"],["text","/sell/catalog/products-v2/bulk-delete-from-shop-group"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopGroupId":"\\\\d+"},"hosttokens":[],"methods":["POST","DELETE"],"schemes":[]},"admin_products_search_product_combinations":{"tokens":[["variable","/","\\\\d+","languageId"],["variable","/","\\\\d+","shopId"],["text","/search-product-combinations"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":{"languageId":null,"shopId":null},"requirements":{"productId":"\\\\d+","shopId":"\\\\d+","languageId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_products_quantity":{"tokens":[["variable","/","\\\\d+","shopId"],["text","/quantity"],["variable","/","\\\\d+","productId"],["text","/sell/catalog/products-v2"]],"defaults":[],"requirements":{"productId":"\\\\d+","shopId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_categories_get_categories_tree":{"tokens":[["text","/sell/catalog/categories/tree"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_catalog_price_rules_list_for_product":{"tokens":[["variable","/","[^/]++","productId"],["text","/sell/catalog/catalog-price-rules/list-for-product"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_cart_rules_search":{"tokens":[["text","/sell/catalog/cart-rules/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_customers_search":{"tokens":[["text","/sell/customers/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_carts":{"tokens":[["text","/carts"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_customers_orders":{"tokens":[["text","/orders"],["variable","/","\\\\d+","customerId"],["text","/sell/customers"]],"defaults":[],"requirements":{"customerId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_addresses_create":{"tokens":[["text","/sell/addresses/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_addresses_edit":{"tokens":[["text","/edit"],["variable","/","\\\\d+","addressId"],["text","/sell/addresses"]],"defaults":[],"requirements":{"addressId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_order_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","orderId"],["text","/sell/addresses/order"]],"defaults":[],"requirements":{"orderId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_cart_addresses_edit":{"tokens":[["text","/edit"],["variable","/","delivery|invoice","addressType"],["variable","/","\\\\d+","cartId"],["text","/sell/addresses/cart"]],"defaults":[],"requirements":{"cartId":"\\\\d+","addressType":"delivery|invoice"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_customer_threads_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","customerThreadId"],["text","/sell/customer-service/customer-threads"]],"defaults":[],"requirements":{"customerThreadId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_carts_create":{"tokens":[["text","/sell/orders/carts/new"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_addresses":{"tokens":[["text","/addresses"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_carrier":{"tokens":[["text","/carrier"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_currency":{"tokens":[["text","/currency"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_language":{"tokens":[["text","/language"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_set_delivery_settings":{"tokens":[["text","/rules/delivery-settings"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_cart_rule":{"tokens":[["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_cart_rule":{"tokens":[["text","/delete"],["variable","/","[^/]++","cartRuleId"],["text","/cart-rules"],["variable","/","[^/]++","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_price":{"tokens":[["text","/price"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_edit_product_quantity":{"tokens":[["text","/quantity"],["variable","/","\\\\d+","productId"],["text","/products"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+","productId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_carts_delete_product":{"tokens":[["text","/delete-product"],["variable","/","\\\\d+","cartId"],["text","/sell/orders/carts"]],"defaults":[],"requirements":{"cartId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_place":{"tokens":[["text","/sell/orders/place"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_view":{"tokens":[["text","/view"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET","POST"],"schemes":[]},"admin_orders_duplicate_cart":{"tokens":[["text","/duplicate-cart"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_update_product":{"tokens":[["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_partial_refund":{"tokens":[["text","/partial-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_standard_refund":{"tokens":[["text","/standard-refund"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_return_product":{"tokens":[["text","/return-product"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_send_process_order_email":{"tokens":[["text","/sell/orders/process-order-email"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_add_product":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_delete_product":{"tokens":[["text","/delete"],["variable","/","\\\\d+","orderDetailId"],["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+","orderDetailId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_get_discounts":{"tokens":[["text","/discounts"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_prices":{"tokens":[["text","/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_payments":{"tokens":[["text","/payments"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_products":{"tokens":[["text","/products"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_invoices":{"tokens":[["text","/invoices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_documents":{"tokens":[["text","/documents"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_get_shipping":{"tokens":[["text","/shipping"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_cancellation":{"tokens":[["text","/cancellation"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_configure_product_pagination":{"tokens":[["text","/sell/orders/configure-product-pagination"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["POST"],"schemes":[]},"admin_orders_product_prices":{"tokens":[["text","/products/prices"],["variable","/","\\\\d+","orderId"],["text","/sell/orders"]],"defaults":[],"requirements":{"orderId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_orders_products_search":{"tokens":[["text","/sell/orders/products/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_attachment_info":{"tokens":[["text","/info"],["variable","/","\\\\d+","attachmentId"],["text","/sell/attachments"]],"defaults":[],"requirements":{"attachmentId":"\\\\d+"},"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_attachments_search":{"tokens":[["variable","/","[^/]++","searchPhrase"],["text","/sell/attachments/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]},"admin_shops_search":{"tokens":[["variable","/","[^/]++","searchTerm"],["text","/configure/advanced/shops/search"]],"defaults":[],"requirements":[],"hosttokens":[],"methods":["GET"],"schemes":[]}},"prefix":"","host":"localhost","port":"","scheme":"http","locale":""}'),{$:yt}=window;class It{constructor(){window.prestashop&&window.prestashop.customRoutes&&Object.assign(xt.routes,window.prestashop.customRoutes),_t().setData(xt),_t().setBaseUrl(yt(document).find("body").data("base-url"))}generate(e,t={}){const r=Object.assign(t,{_token:yt(document).find("body").data("token")});return _t().generate(e,r)}}
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
const Pt=new(r(7187).EventEmitter),wt="productDeletedFromOrder",Tt="productAddedToOrder",kt="productUpdated",Et="productEditionCanceled",St="productListPaginated",Ot="productListNumberPerPage",{$:Rt}=window;class Ct{constructor(){this.router=new It}handleDeleteProductEvent(e){e.preventDefault();const t=Rt(e.currentTarget);window.confirm(t.data("deleteMessage"))&&(t.pstooltip("dispose"),t.prop("disabled",!0),this.deleteProduct(t.data("orderId"),t.data("orderDetailId")))}deleteProduct(e,t){Rt.ajax(this.router.generate("admin_orders_delete_product",{orderId:e,orderDetailId:t}),{method:"POST"}).then((()=>{Pt.emit(wt,{oldOrderDetailId:t,orderId:e})}),(e=>{e.responseJSON&&e.responseJSON.message&&Rt.growl.error({message:e.responseJSON.message})}))}}
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
const{$:qt}=window;class jt{constructor(){this.router=new It}refresh(e){qt.ajax(this.router.generate("admin_orders_get_discounts",{orderId:e})).then((e=>{qt(Ke.list).replaceWith(e)}))}}
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
class Lt{calculateTaxExcluded(e,t,r){let o=e;(o<0||Number.isNaN(o))&&(o=0);const s=t/100+1;return window.ps_round(o/s,r)}calculateTaxIncluded(e,t,r){let o=e;(o<0||Number.isNaN(o))&&(o=0);const s=t/100+1;return window.ps_round(o*s,r)}calculateTotalPrice(e,t,r){return window.ps_round(t*e,r)}}var Mt=r(9567),Ft=Object.defineProperty,Dt=Object.getOwnPropertySymbols,$t=Object.prototype.hasOwnProperty,At=Object.prototype.propertyIsEnumerable,Nt=(e,t,r)=>t in e?Ft(e,t,{enumerable:!0,configurable:!0,writable:!0,value:r}):e[t]=r,Gt=(e,t)=>{for(var r in t||(t={}))$t.call(t,r)&&Nt(e,r,t[r]);if(Dt)for(var r of Dt(t))At.call(t,r)&&Nt(e,r,t[r]);return e};
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
class Bt{constructor(e){const t=Gt({id:"confirm-modal",closable:!1},e);this.buildModalContainer(t)}buildModalContainer(e){this.container=document.createElement("div"),this.container.classList.add("modal","fade"),this.container.id=e.id,this.dialog=document.createElement("div"),this.dialog.classList.add("modal-dialog"),e.dialogStyle&&Object.keys(e.dialogStyle).forEach((t=>{this.dialog.style[t]=e.dialogStyle[t]})),this.content=document.createElement("div"),this.content.classList.add("modal-content"),this.message=document.createElement("p"),this.message.classList.add("modal-message"),this.header=document.createElement("div"),this.header.classList.add("modal-header"),e.modalTitle&&(this.title=document.createElement("h4"),this.title.classList.add("modal-title"),this.title.innerHTML=e.modalTitle),this.closeIcon=document.createElement("button"),this.closeIcon.classList.add("close"),this.closeIcon.setAttribute("type","button"),this.closeIcon.dataset.dismiss="modal",this.closeIcon.innerHTML="×",this.body=document.createElement("div"),this.body.classList.add("modal-body","text-left","font-weight-normal"),this.title&&this.header.appendChild(this.title),this.header.appendChild(this.closeIcon),this.content.append(this.header,this.body),this.body.appendChild(this.message),this.dialog.appendChild(this.content),this.container.appendChild(this.dialog)}}class Zt{constructor(e){const t=Gt({id:"confirm-modal",closable:!1,dialogStyle:{}},e);this.initContainer(t)}initContainer(e){this.modal||(this.modal=new Bt(e)),this.$modal=Mt(this.modal.container);const{id:t,closable:r}=e;this.$modal.modal({backdrop:!!r||"static",keyboard:void 0===r||r,show:!1}),this.$modal.on("hidden.bs.modal",(()=>{const r=document.querySelector(`#${t}`);r&&r.remove(),e.closeCallback&&e.closeCallback()})),document.body.appendChild(this.modal.container)}setTitle(e){return this.modal.title||(this.modal.title=document.createElement("h4"),this.modal.title.classList.add("modal-title"),this.modal.closeIcon?this.modal.header.insertBefore(this.modal.title,this.modal.closeIcon):this.modal.header.appendChild(this.modal.title)),this.modal.title.innerHTML=e,this}render(e){return this.modal.message.innerHTML=e,this}show(){return this.$modal.modal("show"),this}hide(){return this.$modal.modal("hide"),this.$modal.on("shown.bs.modal",(()=>{this.$modal.modal("hide"),this.$modal.off("shown.bs.modal")})),this}}
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
function Ht(e){return void 0===e}var zt=Object.defineProperty,Ut=Object.getOwnPropertySymbols,Qt=Object.prototype.hasOwnProperty,Vt=Object.prototype.propertyIsEnumerable,Jt=(e,t,r)=>t in e?zt(e,t,{enumerable:!0,configurable:!0,writable:!0,value:r}):e[t]=r;
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
class Wt extends Bt{constructor(e){super(e)}buildModalContainer(e){super.buildModalContainer(e),this.message.classList.add("confirm-message"),this.message.innerHTML=e.confirmMessage,this.footer=document.createElement("div"),this.footer.classList.add("modal-footer"),this.closeButton=document.createElement("button"),this.closeButton.setAttribute("type","button"),this.closeButton.classList.add("btn","btn-outline-secondary","btn-lg"),this.closeButton.dataset.dismiss="modal",this.closeButton.innerHTML=e.closeButtonLabel,this.confirmButton=document.createElement("button"),this.confirmButton.setAttribute("type","button"),this.confirmButton.classList.add("btn",e.confirmButtonClass,"btn-lg","btn-confirm-submit"),this.confirmButton.dataset.dismiss="modal",this.confirmButton.innerHTML=e.confirmButtonLabel,this.footer.append(this.closeButton,...e.customButtons,this.confirmButton),this.content.append(this.footer)}}class Kt extends Zt{constructor(e,t,r){var o;let s;s=Ht(e.confirmCallback)?Ht(t)?()=>{console.error("No confirm callback provided for ConfirmModal component.")}:t:e.confirmCallback;super(((e,t)=>{for(var r in t||(t={}))Qt.call(t,r)&&Jt(e,r,t[r]);if(Ut)for(var r of Ut(t))Vt.call(t,r)&&Jt(e,r,t[r]);return e})({id:"confirm-modal",confirmMessage:"Are you sure?",closeButtonLabel:"Close",confirmButtonLabel:"Accept",confirmButtonClass:"btn-primary",customButtons:[],closable:!1,modalTitle:e.confirmTitle,dialogStyle:{},confirmCallback:s,closeCallback:null!=(o=e.closeCallback)?o:r},e))}initContainer(e){this.modal=new Wt(e),this.modal.confirmButton.addEventListener("click",e.confirmCallback),super.initContainer(e)}}var Xt=function(){if("undefined"!=typeof Map)return Map;function e(e,t){var r=-1;return e.some((function(e,o){return e[0]===t&&(r=o,!0)})),r}return function(){function t(){this.__entries__=[]}return Object.defineProperty(t.prototype,"size",{get:function(){return this.__entries__.length},enumerable:!0,configurable:!0}),t.prototype.get=function(t){var r=e(this.__entries__,t),o=this.__entries__[r];return o&&o[1]},t.prototype.set=function(t,r){var o=e(this.__entries__,t);~o?this.__entries__[o][1]=r:this.__entries__.push([t,r])},t.prototype.delete=function(t){var r=this.__entries__,o=e(r,t);~o&&r.splice(o,1)},t.prototype.has=function(t){return!!~e(this.__entries__,t)},t.prototype.clear=function(){this.__entries__.splice(0)},t.prototype.forEach=function(e,t){void 0===t&&(t=null);for(var r=0,o=this.__entries__;r<o.length;r++){var s=o[r];e.call(t,s[1],s[0])}},t}()}(),Yt="undefined"!=typeof window&&"undefined"!=typeof document&&window.document===document,er=void 0!==r.g&&r.g.Math===Math?r.g:"undefined"!=typeof self&&self.Math===Math?self:"undefined"!=typeof window&&window.Math===Math?window:Function("return this")(),tr="function"==typeof requestAnimationFrame?requestAnimationFrame.bind(er):function(e){return setTimeout((function(){return e(Date.now())}),1e3/60)};var rr=["top","right","bottom","left","width","height","size","weight"],or="undefined"!=typeof MutationObserver,sr=function(){function e(){this.connected_=!1,this.mutationEventsAdded_=!1,this.mutationsObserver_=null,this.observers_=[],this.onTransitionEnd_=this.onTransitionEnd_.bind(this),this.refresh=function(e,t){var r=!1,o=!1,s=0;function n(){r&&(r=!1,e()),o&&d()}function i(){tr(n)}function d(){var e=Date.now();if(r){if(e-s<2)return;o=!0}else r=!0,o=!1,setTimeout(i,t);s=e}return d}(this.refresh.bind(this),20)}return e.prototype.addObserver=function(e){~this.observers_.indexOf(e)||this.observers_.push(e),this.connected_||this.connect_()},e.prototype.removeObserver=function(e){var t=this.observers_,r=t.indexOf(e);~r&&t.splice(r,1),!t.length&&this.connected_&&this.disconnect_()},e.prototype.refresh=function(){this.updateObservers_()&&this.refresh()},e.prototype.updateObservers_=function(){var e=this.observers_.filter((function(e){return e.gatherActive(),e.hasActive()}));return e.forEach((function(e){return e.broadcastActive()})),e.length>0},e.prototype.connect_=function(){Yt&&!this.connected_&&(document.addEventListener("transitionend",this.onTransitionEnd_),window.addEventListener("resize",this.refresh),or?(this.mutationsObserver_=new MutationObserver(this.refresh),this.mutationsObserver_.observe(document,{attributes:!0,childList:!0,characterData:!0,subtree:!0})):(document.addEventListener("DOMSubtreeModified",this.refresh),this.mutationEventsAdded_=!0),this.connected_=!0)},e.prototype.disconnect_=function(){Yt&&this.connected_&&(document.removeEventListener("transitionend",this.onTransitionEnd_),window.removeEventListener("resize",this.refresh),this.mutationsObserver_&&this.mutationsObserver_.disconnect(),this.mutationEventsAdded_&&document.removeEventListener("DOMSubtreeModified",this.refresh),this.mutationsObserver_=null,this.mutationEventsAdded_=!1,this.connected_=!1)},e.prototype.onTransitionEnd_=function(e){var t=e.propertyName,r=void 0===t?"":t;rr.some((function(e){return!!~r.indexOf(e)}))&&this.refresh()},e.getInstance=function(){return this.instance_||(this.instance_=new e),this.instance_},e.instance_=null,e}(),nr=function(e,t){for(var r=0,o=Object.keys(t);r<o.length;r++){var s=o[r];Object.defineProperty(e,s,{value:t[s],enumerable:!1,writable:!1,configurable:!0})}return e},ir=function(e){return e&&e.ownerDocument&&e.ownerDocument.defaultView||er},dr=pr(0,0,0,0);function ar(e){return parseFloat(e)||0}function cr(e){for(var t=[],r=1;r<arguments.length;r++)t[r-1]=arguments[r];return t.reduce((function(t,r){return t+ar(e["border-"+r+"-width"])}),0)}function lr(e){var t=e.clientWidth,r=e.clientHeight;if(!t&&!r)return dr;var o=ir(e).getComputedStyle(e),s=function(e){for(var t={},r=0,o=["top","right","bottom","left"];r<o.length;r++){var s=o[r],n=e["padding-"+s];t[s]=ar(n)}return t}(o),n=s.left+s.right,i=s.top+s.bottom,d=ar(o.width),a=ar(o.height);if("border-box"===o.boxSizing&&(Math.round(d+n)!==t&&(d-=cr(o,"left","right")+n),Math.round(a+i)!==r&&(a-=cr(o,"top","bottom")+i)),!function(e){return e===ir(e).document.documentElement}(e)){var c=Math.round(d+n)-t,l=Math.round(a+i)-r;1!==Math.abs(c)&&(d-=c),1!==Math.abs(l)&&(a-=l)}return pr(s.left,s.top,d,a)}var ur="undefined"!=typeof SVGGraphicsElement?function(e){return e instanceof ir(e).SVGGraphicsElement}:function(e){return e instanceof ir(e).SVGElement&&"function"==typeof e.getBBox};function hr(e){return Yt?ur(e)?function(e){var t=e.getBBox();return pr(0,0,t.width,t.height)}(e):lr(e):dr}function pr(e,t,r,o){return{x:e,y:t,width:r,height:o}}var mr=function(){function e(e){this.broadcastWidth=0,this.broadcastHeight=0,this.contentRect_=pr(0,0,0,0),this.target=e}return e.prototype.isActive=function(){var e=hr(this.target);return this.contentRect_=e,e.width!==this.broadcastWidth||e.height!==this.broadcastHeight},e.prototype.broadcastRect=function(){var e=this.contentRect_;return this.broadcastWidth=e.width,this.broadcastHeight=e.height,e},e}(),fr=function(e,t){var r,o,s,n,i,d,a,c=(o=(r=t).x,s=r.y,n=r.width,i=r.height,d="undefined"!=typeof DOMRectReadOnly?DOMRectReadOnly:Object,a=Object.create(d.prototype),nr(a,{x:o,y:s,width:n,height:i,top:s,right:o+n,bottom:i+s,left:o}),a);nr(this,{target:e,contentRect:c})},vr=function(){function e(e,t,r){if(this.activeObservations_=[],this.observations_=new Xt,"function"!=typeof e)throw new TypeError("The callback provided as parameter 1 is not a function.");this.callback_=e,this.controller_=t,this.callbackCtx_=r}return e.prototype.observe=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof ir(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)||(t.set(e,new mr(e)),this.controller_.addObserver(this),this.controller_.refresh())}},e.prototype.unobserve=function(e){if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");if("undefined"!=typeof Element&&Element instanceof Object){if(!(e instanceof ir(e).Element))throw new TypeError('parameter 1 is not of type "Element".');var t=this.observations_;t.has(e)&&(t.delete(e),t.size||this.controller_.removeObserver(this))}},e.prototype.disconnect=function(){this.clearActive(),this.observations_.clear(),this.controller_.removeObserver(this)},e.prototype.gatherActive=function(){var e=this;this.clearActive(),this.observations_.forEach((function(t){t.isActive()&&e.activeObservations_.push(t)}))},e.prototype.broadcastActive=function(){if(this.hasActive()){var e=this.callbackCtx_,t=this.activeObservations_.map((function(e){return new fr(e.target,e.broadcastRect())}));this.callback_.call(e,t,e),this.clearActive()}},e.prototype.clearActive=function(){this.activeObservations_.splice(0)},e.prototype.hasActive=function(){return this.activeObservations_.length>0},e}(),gr="undefined"!=typeof WeakMap?new WeakMap:new Xt,br=function e(t){if(!(this instanceof e))throw new TypeError("Cannot call a class as a function.");if(!arguments.length)throw new TypeError("1 argument required, but only 0 present.");var r=sr.getInstance(),o=new vr(t,r,this);gr.set(this,o)};["observe","unobserve","disconnect"].forEach((function(e){br.prototype[e]=function(){var t;return(t=gr.get(this))[e].apply(t,arguments)}}));void 0!==er.ResizeObserver&&er.ResizeObserver;const _r=class extends Event{constructor(e,t={}){super(_r.parentWindowEvent),this.eventName=e,this.eventParameters=t}get name(){return this.eventName}get parameters(){return this.eventParameters}};_r.parentWindowEvent="IframeClientEvent";Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;Object.defineProperty,Object.getOwnPropertySymbols,Object.prototype.hasOwnProperty,Object.prototype.propertyIsEnumerable;
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
 */const xr=Kt,{$:yr}=window;class Ir{constructor(){this.router=new It}refresh(e){yr.getJSON(this.router.generate("admin_orders_get_prices",{orderId:e})).then((e=>{yr(nt).text(e.orderTotalFormatted),yr(tt).text(`-${e.discountsAmountFormatted}`),yr(et).toggleClass("d-none",!e.discountsAmountDisplayed),yr(Ye).text(e.productsTotalFormatted),yr(ot).text(e.shippingTotalFormatted),yr(rt).toggleClass("d-none",!e.shippingTotalDisplayed),yr(st).text(e.taxesTotalFormatted)}))}refreshProductPrices(e){yr.getJSON(this.router.generate("admin_orders_product_prices",{orderId:e})).then((e=>{e.forEach((e=>{const t=se(e.orderDetailId);let r=yr(e.quantity);e.quantity>1&&(r=r.wrap('<span class="badge badge-secondary rounded-circle"></span>')),yr(`${t} ${Ne}`).text(e.unitPrice),yr(`${t} ${Ge}`).html(r.html()),yr(`${t} ${Be}`).text(e.availableQuantity),yr(`${t} ${Ze}`).text(e.totalPrice);const o=yr(pe(e.orderDetailId));o.data("product-price-tax-incl",e.unitPriceTaxInclRaw),o.data("product-price-tax-excl",e.unitPriceTaxExclRaw),o.data("product-quantity",e.quantity)}))}))}checkOtherProductPricesMatch(e,t,r,o,s){const n=document.querySelectorAll("tr.cellProduct"),i=Number(t),d=Number(r),a=Number(e);let c=!1,l=!1;return n.forEach((e=>{const t=yr(e).attr("id");if(s&&t===`orderProduct_${s}`)return;const r=yr(`#${t} ${he}`),n=Number(r.data("order-invoice-id")),u=Number(r.data("product-id")),h=Number(r.data("combination-id"));u===i&&h===d&&a!==Number(r.data("product-price-tax-incl"))&&(!o||o&&n&&o===n?l=!0:c=!0)})),c?"invoice":l?"product":null}}
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
const{$:Pr}=window;class wr{constructor(e){this.router=new It,this.orderDetailId=e,this.productRow=Pr(`#orderProduct_${this.orderDetailId}`),this.product={},this.currencyPrecision=Pr(V).data("currencyPrecision"),this.priceTaxCalculator=new Lt,this.productEditSaveBtn=Pr(Le),this.quantityInput=Pr(Qe),this.orderPricesRefresher=new Ir,this.availableText=null,this.isOrderTaxIncluded=null,this.productEditInvoiceSelect=null,this.priceTaxIncludedInput=null,this.taxExcluded=null,this.taxIncluded=null,this.taxRate=null,this.priceTaxExcludedInput=null,this.productEditCancelBtn=null,this.quantity=null,this.priceTotalText=null,this.initialTotal=null,this.productRowEdit=null,this.productEditImage=null,this.productEditName=null,this.locationText=null}setupListener(){this.quantityInput.on("change keyup",(e=>{var t;const r=e.target,o=Number(r.value),s=parseInt(Pr(e.currentTarget).data("availableQuantity"),10)-(o-parseInt(this.quantityInput.data("previousQuantity"),10)),n=null==(t=this.availableText)?void 0:t.data("availableOutOfStock");this.quantity=o,this.availableText&&(this.availableText.text(s),this.availableText.toggleClass("text-danger font-weight-bold",s<0)),this.updateTotal();const i=o<=0||s<0&&!n;this.productEditSaveBtn.prop("disabled",i)})),this.productEditInvoiceSelect&&this.productEditInvoiceSelect.on("change",(()=>{this.productEditSaveBtn.prop("disabled",!1)})),this.priceTaxIncludedInput&&this.priceTaxIncludedInput.on("change keyup",(e=>{const t=e.target;this.taxIncluded=parseFloat(t.value),this.taxExcluded=this.priceTaxCalculator.calculateTaxExcluded(this.taxIncluded,this.taxRate,this.currencyPrecision),this.priceTaxExcludedInput&&this.priceTaxExcludedInput.val(this.taxExcluded),this.updateTotal()})),this.priceTaxExcludedInput&&this.priceTaxExcludedInput.on("change keyup",(e=>{const t=e.target;this.taxExcluded=parseFloat(t.value),this.taxIncluded=this.priceTaxCalculator.calculateTaxIncluded(this.taxExcluded,this.taxRate,this.currencyPrecision),this.priceTaxIncludedInput&&this.priceTaxIncludedInput.val(this.taxIncluded),this.updateTotal()})),this.productEditSaveBtn.on("click",(e=>{const t=Pr(e.currentTarget);window.confirm(t.data("updateMessage"))&&(t.prop("disabled",!0),this.handleEditProductWithConfirmationModal(e))})),this.productEditCancelBtn&&this.productEditCancelBtn.on("click",(()=>{Pt.emit(Et,{orderDetailId:this.orderDetailId})}))}updateTotal(){const e=this.priceTaxCalculator.calculateTotalPrice(this.quantity,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision);this.priceTotalText&&this.priceTotalText.html(e),this.productEditSaveBtn.prop("disabled",e===this.initialTotal)}displayProduct(e){this.productRowEdit=Pr(Fe).clone(!0),this.productRowEdit.attr("id",`editOrderProduct_${this.orderDetailId}`),this.productRowEdit.find("*[id]").each((function(){Pr(this).removeAttr("id")})),this.productEditSaveBtn=this.productRowEdit.find(Le),this.productEditCancelBtn=this.productRowEdit.find(Me),this.productEditInvoiceSelect=this.productRowEdit.find(Ue),this.productEditImage=this.productRowEdit.find($e),this.productEditName=this.productRowEdit.find(Ae),this.priceTaxIncludedInput=this.productRowEdit.find(ze),this.priceTaxExcludedInput=this.productRowEdit.find(He),this.quantityInput=this.productRowEdit.find(Qe),this.locationText=this.productRowEdit.find(Ve),this.availableText=this.productRowEdit.find(Je),this.priceTotalText=this.productRowEdit.find(We),this.priceTaxExcludedInput.val(window.ps_round(e.price_tax_excl,this.currencyPrecision)),this.priceTaxIncludedInput.val(window.ps_round(e.price_tax_incl,this.currencyPrecision)),this.quantityInput.val(e.quantity).data("availableQuantity",e.availableQuantity).data("previousQuantity",e.quantity),this.availableText.data("availableOutOfStock",e.availableOutOfStock),e.orderInvoiceId&&this.productEditInvoiceSelect.val(e.orderInvoiceId),this.taxRate=e.tax_rate,this.initialTotal=this.priceTaxCalculator.calculateTotalPrice(e.quantity,e.isOrderTaxIncluded?e.price_tax_incl:e.price_tax_excl,this.currencyPrecision),this.isOrderTaxIncluded=e.isOrderTaxIncluded,this.quantity=e.quantity,this.taxIncluded=e.price_tax_incl,this.taxExcluded=e.price_tax_excl,this.productEditImage.html(this.productRow.find($e).html()),this.productEditName.html(this.productRow.find(Ae).html()),this.locationText.html(e.location),this.availableText.html(e.availableQuantity),this.priceTotalText.html(this.initialTotal),this.productRow.addClass("d-none").after(this.productRowEdit.removeClass("d-none")),this.setupListener()}handleEditProductWithConfirmationModal(e){const t=Pr(`#orderProduct_${this.orderDetailId} ${he}`),r=t.data("product-id"),o=t.data("combination-id"),s=t.data("order-invoice-id");let n;if(this.priceTaxIncludedInput&&(n=this.orderPricesRefresher.checkOtherProductPricesMatch(this.priceTaxIncludedInput.val(),r,o,s,this.orderDetailId)),null===n)return void this.editProduct(Pr(e.currentTarget).data("orderId"),this.orderDetailId);const i="product"===n?this.priceTaxExcludedInput:this.productEditInvoiceSelect;if(i){new xr({id:"modal-confirm-new-price",confirmTitle:i.data("modal-edit-price-title"),confirmMessage:i.data("modal-edit-price-body"),confirmButtonLabel:i.data("modal-edit-price-apply"),closeButtonLabel:i.data("modal-edit-price-cancel")},(()=>{this.editProduct(Pr(e.currentTarget).data("orderId"),this.orderDetailId)})).show()}}editProduct(e,t){var r,o,s;const n={price_tax_incl:null==(r=this.priceTaxIncludedInput)?void 0:r.val(),price_tax_excl:null==(o=this.priceTaxExcludedInput)?void 0:o.val(),quantity:this.quantityInput.val(),invoice:null==(s=this.productEditInvoiceSelect)?void 0:s.val()};Pr.ajax({url:this.router.generate("admin_orders_update_product",{orderId:e,orderDetailId:t}),method:"POST",data:n}).then((()=>{Pt.emit(kt,{orderId:e,orderDetailId:t})}),(e=>{e.responseJSON&&e.responseJSON.message&&Pr.growl.error({message:e.responseJSON.message})}))}}
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
const{$:Tr}=window;class kr{constructor(){this.router=new It}addOrUpdateProductToList(e,t){e.length>0?e.html(Tr(t).html()):Tr(be).before(Tr(t).hide().fadeIn())}updateNumProducts(e){Tr(U).html(e)}editProductFromList(e,t,r,o,s,n,i,d,a,c){new wr(e).displayProduct({price_tax_excl:o,price_tax_incl:r,tax_rate:s,quantity:t,location:n,availableQuantity:i,availableOutOfStock:d,orderInvoiceId:a,isOrderTaxIncluded:c}),Tr(ve).addClass("d-none"),Tr(be).addClass("d-none")}moveProductsPanelToModificationPosition(e="body"){Tr(fe).addClass("d-none"),Tr(`${ve}, ${be}`).removeClass("d-none"),this.moveProductPanelToTop(e)}moveProductsPanelToRefundPosition(){this.resetAllEditRows(),Tr(`${ve}, ${be}, ${fe}`).addClass("d-none"),this.moveProductPanelToTop()}moveProductPanelToTop(e="body"){const t=Tr(H);if(t.find(z).length>0)return;Tr(z).detach().appendTo(t),t.removeClass("d-none"),this.toggleColumn(de),this.toggleColumn(ae);Tr(V).find('tr[id^="orderProduct_"]').removeClass("d-none"),Tr(J).addClass("d-none");const r=Tr(e).offset(),o=Tr(".header-toolbar").height();if(r&&o){const e=r.top-o-100;Tr("html,body").animate({scrollTop:e},"slow")}}moveProductPanelToOriginalPosition(){Tr(je).addClass("d-none"),Tr(H).addClass("d-none"),Tr(z).detach().appendTo(Z),Tr(J).removeClass("d-none"),Tr(fe).removeClass("d-none"),Tr(`${ve}, ${be}`).addClass("d-none"),this.paginate(1)}resetAddRow(){Tr(ye).val(""),Tr(_e).val(""),Tr(Pe).addClass("d-none"),Tr(we).val(""),Tr(we).prop("disabled",!1),Tr(Te).val(""),Tr(ke).val(""),Tr(Ee).val(""),Tr(Se).html(""),Tr(Oe).html(""),Tr(je).addClass("d-none"),Tr(ve).prop("disabled",!0)}resetAllEditRows(){Tr(he).each(((e,t)=>{this.resetEditRow(Tr(t).data("orderDetailId"))}))}resetEditRow(e){const t=Tr(se(e));Tr(ne(e)).remove(),t.removeClass("d-none")}paginate(e){const t=Tr(V).find('tr[id^="orderProduct_"]'),r=Tr(ue),o=Tr(K),s=parseInt(o.data("numPerPage"),10),n=Math.ceil(t.length/s),i=Math.max(1,Math.min(e,n));this.paginateUpdateControls(i),t.addClass("d-none"),r.addClass("d-none");const d=i*s;for(let e=(i-1)*s+1-1;e<Math.min(d,t.length);e+=1)Tr(t[e]).removeClass("d-none");r.each((function(){Tr(this).prev().hasClass("d-none")||Tr(this).removeClass("d-none")})),Tr(De).not(Fe).remove(),this.toggleColumn(ce),this.toggleColumn(le)}paginateUpdateControls(e){const t=Tr(K).find("li.page-item").length-3;Tr(K).find(".active").removeClass("active"),Tr(K).find(`li:has(> [data-page="${e}"])`).addClass("active"),Tr(Y).removeClass("disabled"),1===e&&Tr(Y).addClass("disabled"),Tr(X).removeClass("disabled"),e===t&&Tr(X).addClass("disabled"),this.togglePaginationControls()}updateNumPerPage(e){Tr(K).data("numPerPage",e),this.updatePaginationControls()}togglePaginationControls(){const e=Tr(K).find("li.page-item").length-3;Tr(W).toggleClass("d-none",e<=1)}toggleProductAddNewInvoiceInfo(){Tr(je).toggleClass("d-none",0!==parseInt(Tr(Ce).val(),10))}toggleColumn(e,t=null){let r=!1;null===t?Tr(e).filter("td").each((function(){if(""!==Tr(this).html())return r=!0,!1})):r=t,Tr(e).toggleClass("d-none",!r)}updatePaginationControls(){const e=Tr(K),t=e.data("numPerPage"),r=Tr(V).find('tr[id^="orderProduct_"]'),o=Math.ceil(r.length/t);e.data("numPages",o);const s=Tr(re);Tr(K).find("li:has(> [data-page])").remove(),Tr(X).before(s);for(let e=1;e<=o;e+=1){const t=s.clone();t.find("span").attr("data-page",e),t.find("span").html(e),s.before(t.removeClass("d-none"))}this.togglePaginationControls()}}
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
const{$:Er}=window;class Sr{constructor(){this.router=new It}refresh(e){Er.ajax(this.router.generate("admin_orders_get_payments",{orderId:e})).then((e=>{Er(d).remove(),Er(`${i} .card-body`).prepend(e)}),(e=>{e.responseJSON&&e.responseJSON.message&&Er.growl.error({message:e.responseJSON.message})}))}}
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
const{$:Or}=window;class Rr{constructor(){this.router=new It}refresh(e){Or.getJSON(this.router.generate("admin_orders_get_shipping",{orderId:e})).then((e=>{Or($).text(e.total),Or(A).html(e.html)}))}}
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
const{$:Cr}=window;class qr{constructor(){this.router=new It}refresh(e){Cr.getJSON(this.router.generate("admin_orders_get_invoices",{orderId:e})).then((e=>{if(!e||!e.invoices||Object.keys(e.invoices).length<=0)return;const t=Cr(n),r=Cr(Ce).find("optgroup:first"),o=Cr(Ue),s=Cr(p);r.empty(),t.empty(),o.empty(),s.empty(),Object.keys(e.invoices).forEach((n=>{const i=e.invoices[n],d=n.split(" - ")[0];r.append(`<option value="${i}">${d}</option>`),t.append(`<option value="${i}">${d}</option>`),o.append(`<option value="${i}">${d}</option>`),s.append(`<option value="${i}">${n}</option>`)}));const i=document.querySelector(Ce);i&&(i.selectedIndex=0)}))}}var jr=r(1527);
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
const{$:Lr}=window;class Mr{constructor(){this.router=new It,this.cancelProductForm=Lr(dt.form),this.orderId=this.cancelProductForm.data("orderId"),this.orderDelivered=1===parseInt(this.cancelProductForm.data("isDelivered"),10),this.isTaxIncluded=1===parseInt(this.cancelProductForm.data("isTaxIncluded"),10),this.discountsAmount=parseFloat(this.cancelProductForm.data("discountsAmount")),this.currencyFormatter=jr.NumberFormatter.build(this.cancelProductForm.data("priceSpecification")),this.useAmountInputs=!0,this.listenForInputs()}showPartialRefund(){this.hideCancelElements(),Lr(dt.toggle.partialRefund).show(),this.useAmountInputs=!0,this.initForm(Lr(dt.buttons.save).data("partialRefundLabel"),this.router.generate("admin_orders_partial_refund",{orderId:this.orderId}),"partial-refund")}showStandardRefund(){this.hideCancelElements(),Lr(dt.toggle.standardRefund).show(),this.useAmountInputs=!1,this.initForm(Lr(dt.buttons.save).data("standardRefundLabel"),this.router.generate("admin_orders_standard_refund",{orderId:this.orderId}),"standard-refund")}showReturnProduct(){this.hideCancelElements(),Lr(dt.toggle.returnProduct).show(),this.useAmountInputs=!1,this.initForm(Lr(dt.buttons.save).data("returnProductLabel"),this.router.generate("admin_orders_return_product",{orderId:this.orderId}),"return-product")}hideRefund(){this.hideCancelElements(),Lr(dt.table.actions).show()}hideCancelElements(){Lr(dt.toggle.standardRefund).hide(),Lr(dt.toggle.partialRefund).hide(),Lr(dt.toggle.returnProduct).hide(),Lr(dt.table.actions).hide()}initForm(e,t,r){this.updateVoucherRefund(),this.cancelProductForm.prop("action",t),this.cancelProductForm.removeClass("standard-refund partial-refund return-product cancel-product").addClass(r),Lr(dt.buttons.save).html(e),Lr(dt.table.header).html(e),Lr(dt.checkboxes.restock).prop("checked",this.orderDelivered),Lr(dt.checkboxes.creditSlip).prop("checked",!0),Lr(dt.checkboxes.voucher).prop("checked",!1)}listenForInputs(){Lr(document).on("change",dt.inputs.quantity,(e=>{const t=Lr(e.target),r=t.parents(dt.table.cell).find(dt.inputs.amount),o=parseInt(t.val(),10);if(o<=0)return r.val(0),void this.updateVoucherRefund();const s=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",n=parseFloat(t.data(s)),i=parseFloat(t.data("amountRefundable")),d=n*o<i?n*o:i,a=parseFloat(r.val());this.useAmountInputs&&this.updateAmountInput(t),(""===r.val()||0===a||a>d)&&(r.val(d),this.updateVoucherRefund())})),Lr(document).on("change",dt.inputs.amount,(()=>{this.updateVoucherRefund()})),Lr(document).on("change",dt.inputs.selector,(e=>{const t=Lr(e.target),r=t.parents(dt.table.cell).find(dt.inputs.quantity),o=parseInt(r.data("quantityRefundable"),10),s=parseInt(r.val(),10);t.is(":checked")?(Number.isNaN(s)||0===s)&&r.val(o):r.val(0),this.updateVoucherRefund()}))}updateAmountInput(e){const t=e.parents(dt.table.cell).find(dt.inputs.amount),r=parseInt(e.val(),10);if(r<=0)return void t.val(0);const o=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",s=parseFloat(e.data(o)),n=parseFloat(e.data("amountRefundable")),i=s*r<n?s*r:n,d=parseFloat(t.val());(""===t.val()||0===d||d>i)&&t.val(i)}getRefundAmount(){let e=0;return this.useAmountInputs?Lr(dt.inputs.amount).each(((t,r)=>{const o=parseFloat(r.value);e+=Number.isNaN(o)?0:o})):Lr(dt.inputs.quantity).each(((t,r)=>{const o=Lr(r),s=this.isTaxIncluded?"productPriceTaxIncl":"productPriceTaxExcl",n=parseFloat(o.data(s)),i=parseInt(o.val(),10);e+=i*n})),e}updateVoucherRefund(){const e=this.getRefundAmount();this.updateVoucherRefundTypeLabel(Lr(dt.radios.voucherRefundType.productPrices),e);const t=e-this.discountsAmount;this.updateVoucherRefundTypeLabel(Lr(dt.radios.voucherRefundType.productPricesVoucherExcluded),t),t<0?(Lr(dt.radios.voucherRefundType.productPricesVoucherExcluded).prop("checked",!1).prop("disabled",!0),Lr(dt.radios.voucherRefundType.productPrices).prop("checked",!0),Lr(dt.radios.voucherRefundType.negativeErrorMessage).show()):(Lr(dt.radios.voucherRefundType.productPricesVoucherExcluded).prop("disabled",!1),Lr(dt.radios.voucherRefundType.negativeErrorMessage).hide())}updateVoucherRefundTypeLabel(e,t){var r;const o=e.data("defaultLabel"),s=e.parents("label"),n=this.currencyFormatter.format(t),i=null==(r=null==s?void 0:s.get(0))?void 0:r.lastChild;i&&(i.nodeValue=`\n      ${o} ${n}`)}showCancelProductForm(){const e=this.router.generate("admin_orders_cancellation",{orderId:this.orderId});this.initForm(Lr(dt.buttons.save).data("cancelLabel"),e,"cancel-product"),this.hideCancelElements(),Lr(dt.toggle.cancelProducts).show()}}
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
const{$:Fr}=window;class Dr{constructor(){this.router=new It,this.invoiceNoteManager=new gt}refresh(e){Fr.getJSON(this.router.generate("admin_orders_get_documents",{orderId:e})).then((e=>{Fr(F).text(e.total),Fr(D).html(e.html),this.invoiceNoteManager.setupListeners()}))}}
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
const{$:$r}=window;class Ar{constructor(){this.orderDiscountsRefresher=new jt,this.orderProductManager=new Ct,this.orderProductRenderer=new kr,this.orderPricesRefresher=new Ir,this.orderPaymentsRefresher=new Sr,this.orderShippingRefresher=new Rr,this.orderDocumentsRefresher=new Dr,this.orderInvoicesRefresher=new qr,this.orderProductCancel=new Mr,this.router=new It,this.listenToEvents()}listenToEvents(){$r(C).fancybox({type:"iframe",width:"90%",height:"90%"}),$r(R).fancybox({type:"iframe",width:"90%",height:"90%"}),Pt.on(wt,(e=>{this.orderPricesRefresher.refresh(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.refreshProductsList(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId)})),Pt.on(Et,(e=>{this.orderProductRenderer.resetEditRow(e.orderDetailId);$r(De).not(Fe).length>0||this.orderProductRenderer.moveProductPanelToOriginalPosition()})),Pt.on(kt,(e=>{this.orderProductRenderer.resetEditRow(e.orderDetailId),this.orderPricesRefresher.refresh(e.orderId),this.orderPricesRefresher.refreshProductPrices(e.orderId),this.refreshProductsList(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderInvoicesRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId),this.listenForProductDelete(),this.listenForProductEdit(),this.resetToolTips();$r(De).not(Fe).length>0||this.orderProductRenderer.moveProductPanelToOriginalPosition()})),Pt.on(Tt,(e=>{this.orderProductRenderer.resetAddRow(),this.orderPricesRefresher.refreshProductPrices(e.orderId),this.orderPricesRefresher.refresh(e.orderId),this.refreshProductsList(e.orderId),this.orderPaymentsRefresher.refresh(e.orderId),this.orderDiscountsRefresher.refresh(e.orderId),this.orderInvoicesRefresher.refresh(e.orderId),this.orderDocumentsRefresher.refresh(e.orderId),this.orderShippingRefresher.refresh(e.orderId),this.orderProductRenderer.moveProductPanelToOriginalPosition()}))}listenForProductDelete(){$r(Q).off("click").on("click",(e=>this.orderProductManager.handleDeleteProductEvent(e)))}resetToolTips(){$r(he).pstooltip(),$r(Q).pstooltip()}listenForProductEdit(){$r(he).off("click").on("click",(e=>{const t=$r(e.currentTarget);this.orderProductRenderer.moveProductsPanelToModificationPosition(),this.orderProductRenderer.editProductFromList(t.data("orderDetailId"),t.data("productQuantity"),t.data("productPriceTaxIncl"),t.data("productPriceTaxExcl"),t.data("taxRate"),t.data("location"),t.data("availableQuantity"),t.data("availableOutOfStock"),t.data("orderInvoiceId"),t.data("isOrderTaxIncluded"))}))}listenForProductPack(){$r(Xe.modal).on("show.bs.modal",(e=>{const t=$r(e.relatedTarget).data("packItems");$r(Xe.rows).remove(),t.forEach((e=>{const t=$r(Xe.template).clone();t.attr("id",`productpack_${e.id}`).removeClass("d-none"),t.find(Xe.product.img).attr("src",e.imagePath),t.find(Xe.product.name).html(e.name),t.find(Xe.product.link).attr("href",this.router.generate("admin_product_form",{id:e.id})),""!==e.reference?t.find(Xe.product.ref).append(e.reference):t.find(Xe.product.ref).remove(),""!==e.supplierReference?t.find(Xe.product.supplierRef).append(e.supplierReference):t.find(Xe.product.supplierRef).remove(),e.quantity>1?t.find(`${Xe.product.quantity} span`).html(e.quantity):t.find(Xe.product.quantity).html(e.quantity),t.find(Xe.product.availableQuantity).html(e.availableQuantity),$r(Xe.template).before(t)}))}))}listenForProductAdd(){$r(me).on("click",(()=>{this.orderProductRenderer.toggleProductAddNewInvoiceInfo(),this.orderProductRenderer.moveProductsPanelToModificationPosition(_e)})),$r(ge).on("click",(()=>this.orderProductRenderer.moveProductPanelToOriginalPosition()))}listenForProductPagination(){$r(K).on("click",ee,(e=>{e.preventDefault();const t=$r(e.currentTarget);Pt.emit(St,{numPage:t.data("page")})})),$r(X).on("click",(e=>{e.preventDefault();if($r(e.currentTarget).hasClass("disabled"))return;const t=this.getActivePage();Pt.emit(St,{numPage:parseInt($r(t).html(),10)+1})})),$r(Y).on("click",(e=>{e.preventDefault();if($r(e.currentTarget).hasClass("disabled"))return;const t=this.getActivePage();Pt.emit(St,{numPage:parseInt($r(t).html(),10)-1})})),$r(oe).on("change",(e=>{e.preventDefault();const t=$r(e.currentTarget),r=parseInt(t.val(),10);Pt.emit(Ot,{numPerPage:r})})),Pt.on(St,(e=>{this.orderProductRenderer.paginate(e.numPage),this.listenForProductDelete(),this.listenForProductEdit(),this.resetToolTips()})),Pt.on(Ot,(e=>{this.orderProductRenderer.updateNumPerPage(e.numPerPage),Pt.emit(St,{numPage:1}),$r.ajax({url:this.router.generate("admin_orders_configure_product_pagination"),method:"POST",data:{numPerPage:e.numPerPage}})}))}listenForRefund(){$r(dt.buttons.partialRefund).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showPartialRefund()})),$r(dt.buttons.standardRefund).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showStandardRefund()})),$r(dt.buttons.returnProduct).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showReturnProduct()})),$r(dt.buttons.abort).on("click",(()=>{this.orderProductRenderer.moveProductPanelToOriginalPosition(),this.orderProductCancel.hideRefund()}))}listenForCancelProduct(){$r(dt.buttons.cancelProducts).on("click",(()=>{this.orderProductRenderer.moveProductsPanelToRefundPosition(),this.orderProductCancel.showCancelProductForm()}))}getActivePage(){return $r(K).find(".active span").get(0)}refreshProductsList(e){$r(pt).show();const t=$r(K).data("numPerPage"),r=$r(ie).length,o=parseInt($r(te).html(),10);$r.ajax(this.router.generate("admin_orders_get_products",{orderId:e})).done((e=>{$r(V).find(ie).remove(),$r(ue).remove(),$r(`${V} tbody`).prepend(e),$r(pt).hide();const s=$r(ie).length,n=Math.ceil(s/t);this.orderProductRenderer.updateNumProducts(s),this.orderProductRenderer.updatePaginationControls();let i=1,d="";r>s?(d=r-s==1?window.translate_javascripts["The product was successfully removed."]:window.translate_javascripts["[1] products were successfully removed."].replace("[1]",r-s),i=1===n?1:o):r<s&&(d=s-r==1?window.translate_javascripts["The product was successfully added."]:window.translate_javascripts["[1] products were successfully added."].replace("[1]",s-r),i=1),""!==d&&$r.growl.notice({title:"",message:d}),Pt.emit(St,{numPage:i}),this.resetToolTips()})).fail((()=>{$r.growl.error({title:"",message:"Failed to reload the products list. Please reload the page"})}))}}
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
const{$:Nr}=window;class Gr{constructor(e){this.activeSearchRequest=null,this.router=new It,this.input=e,this.results=[],this.searchTimeoutId=void 0,this.dropdownMenu=Nr(xe),this.onItemClickedCallback=()=>{}}listenForSearch(){this.input.on("click",(e=>{e.stopImmediatePropagation(),this.updateResults(this.results)})),this.input.on("keyup",(e=>this.delaySearch(e.currentTarget))),Nr(document).on("click",(()=>this.dropdownMenu.hide()))}delaySearch(e){clearTimeout(this.searchTimeoutId),e.value.length<2||(this.searchTimeoutId=setTimeout((()=>{this.search(e.value,Nr(e).data("currency"),Nr(e).data("order"))}),300))}search(e,t,r){const o={search_phrase:e};t&&(o.currency_id=t),r&&(o.order_id=r),null!==this.activeSearchRequest&&this.activeSearchRequest.abort(),this.activeSearchRequest=Nr.get(this.router.generate("admin_orders_products_search",o)),this.activeSearchRequest.then((e=>this.updateResults(e))).always((()=>{this.activeSearchRequest=null}))}updateResults(e){this.dropdownMenu.empty(),!e||!e.products||Object.keys(e.products).length<=0?this.dropdownMenu.hide():(this.results=e.products,Object.values(this.results).forEach((e=>{const t=Nr(`<a class="dropdown-item" data-id="${e.productId}" href="#">${e.name}</a>`);t.on("click",(e=>{e.preventDefault(),this.onItemClicked(Nr(e.target).data("id"))})),this.dropdownMenu.append(t)})),this.dropdownMenu.show())}onItemClicked(e){const t=this.results.filter((t=>t.productId===e));0!==t.length&&(this.input.val(t[0].name),this.onItemClickedCallback(t[0]))}}
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
const{$:Br}=window;class Zr{constructor(){this.router=new It,this.productAddActionBtn=Br(ve),this.productIdInput=Br(ye),this.combinationsBlock=Br(Pe),this.combinationsSelect=Br(we),this.priceTaxIncludedInput=Br(ke),this.priceTaxExcludedInput=Br(Te),this.taxRateInput=Br(Ie),this.quantityInput=Br(Ee),this.availableText=Br(Se),this.locationText=Br(Oe),this.totalPriceText=Br(Re),this.invoiceSelect=Br(Ce),this.freeShippingSelect=Br(qe),this.productAddMenuBtn=Br(me),this.available=null,this.setupListener(),this.product={},this.currencyPrecision=Br(V).data("currencyPrecision"),this.priceTaxCalculator=new Lt,this.orderProductRenderer=new kr,this.orderPricesRefresher=new Ir,this.isOrderTaxIncluded=Br(be).data("isOrderTaxIncluded"),this.taxExcluded=null,this.taxIncluded=null}setupListener(){this.combinationsSelect.on("change",(e=>{const t=window.ps_round(Br(e.currentTarget).find(":selected").data("priceTaxExcluded"),this.currencyPrecision);this.priceTaxExcludedInput.val(t),this.taxExcluded=parseFloat(t);const r=window.ps_round(Br(e.currentTarget).find(":selected").data("priceTaxIncluded"),this.currencyPrecision);this.priceTaxIncludedInput.val(r),this.taxIncluded=parseFloat(r),this.locationText.html(Br(e.currentTarget).find(":selected").data("location")),this.available=Br(e.currentTarget).find(":selected").data("stock"),this.quantityInput.trigger("change"),this.orderProductRenderer.toggleColumn(de)})),this.quantityInput.on("change keyup",(e=>{if(null!==this.available){const t=e.target,r=Number(t.value),o=this.available-r,s=this.availableText.data("availableOutOfStock");this.availableText.text(o),this.availableText.toggleClass("text-danger font-weight-bold",o<0);const n=r<=0||o<0&&!s;this.productAddActionBtn.prop("disabled",n),this.invoiceSelect.prop("disabled",!s&&o<0),this.taxIncluded=parseFloat(this.priceTaxIncludedInput.val()),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))}})),this.productIdInput.on("change",(()=>{this.productAddActionBtn.removeAttr("disabled"),this.invoiceSelect.removeAttr("disabled")})),this.priceTaxIncludedInput.on("change keyup",(e=>{const t=e.target;this.taxIncluded=parseFloat(t.value),this.taxExcluded=this.priceTaxCalculator.calculateTaxExcluded(this.taxIncluded,this.taxRateInput.val(),this.currencyPrecision);const r=parseInt(this.quantityInput.val(),10);this.priceTaxExcludedInput.val(this.taxExcluded),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))})),this.priceTaxExcludedInput.on("change keyup",(e=>{const t=e.target;this.taxExcluded=parseFloat(t.value),this.taxIncluded=this.priceTaxCalculator.calculateTaxIncluded(this.taxExcluded,this.taxRateInput.val(),this.currencyPrecision);const r=parseInt(this.quantityInput.val(),10);this.priceTaxIncludedInput.val(this.taxIncluded),this.totalPriceText.html(this.priceTaxCalculator.calculateTotalPrice(r,this.isOrderTaxIncluded?this.taxIncluded:this.taxExcluded,this.currencyPrecision))})),this.productAddActionBtn.on("click",(e=>this.confirmNewInvoice(e))),this.invoiceSelect.on("change",(()=>this.orderProductRenderer.toggleProductAddNewInvoiceInfo()))}setProduct(e){if(e){this.productIdInput.val(e.productId).trigger("change");const t=window.ps_round(e.priceTaxExcl,this.currencyPrecision);this.priceTaxExcludedInput.val(t),this.taxExcluded=parseFloat(t);const r=window.ps_round(e.priceTaxIncl,this.currencyPrecision);this.priceTaxIncludedInput.val(r),this.taxIncluded=parseFloat(r),this.taxRateInput.val(e.taxRate),this.locationText.html(e.location),this.available=e.stock,this.availableText.data("availableOutOfStock",e.availableOutOfStock),this.quantityInput.val(1),this.quantityInput.trigger("change"),this.setCombinations(e.combinations),this.orderProductRenderer.toggleColumn(de)}}setCombinations(e){this.combinationsSelect.empty(),Object.values(e).forEach((e=>{this.combinationsSelect.append(`<option value="${e.attributeCombinationId}" data-price-tax-excluded="${e.priceTaxExcluded}" data-price-tax-included="${e.priceTaxIncluded}" data-stock="${e.stock}" data-location="${e.location}">${e.attribute}</option>`)})),this.combinationsBlock.toggleClass("d-none",0===Object.keys(e).length),Object.keys(e).length>0&&this.combinationsSelect.trigger("change")}addProduct(e){this.productAddActionBtn.prop("disabled",!0),this.invoiceSelect.prop("disabled",!0),this.combinationsSelect.prop("disabled",!0);const t={product_id:this.productIdInput.val(),combination_id:Br(":selected",this.combinationsSelect).val(),price_tax_incl:this.priceTaxIncludedInput.val(),price_tax_excl:this.priceTaxExcludedInput.val(),quantity:this.quantityInput.val(),invoice_id:this.invoiceSelect.val(),free_shipping:this.freeShippingSelect.prop("checked")};Br.ajax({url:this.router.generate("admin_orders_add_product",{orderId:e}),method:"POST",data:t}).then((r=>{Pt.emit(Tt,{orderId:e,orderProductId:t.product_id,newRow:r})}),(e=>{this.productAddActionBtn.prop("disabled",!1),this.invoiceSelect.prop("disabled",!1),this.combinationsSelect.prop("disabled",!1),e.responseJSON&&e.responseJSON.message&&Br.growl.error({message:e.responseJSON.message})}))}confirmNewInvoice(e){const t=parseInt(this.invoiceSelect.val(),10),r=Br(e.currentTarget).data("orderId");if(0===t){new xr({id:"modal-confirm-new-invoice",confirmTitle:this.invoiceSelect.data("modal-title"),confirmMessage:this.invoiceSelect.data("modal-body"),confirmButtonLabel:this.invoiceSelect.data("modal-apply"),closeButtonLabel:this.invoiceSelect.data("modal-cancel")},(()=>{this.confirmNewPrice(r,t)})).show()}else this.addProduct(r)}confirmNewPrice(e,t){const r=Br(":selected",this.combinationsSelect).val(),o=void 0===r?0:r;if("invoice"===this.orderPricesRefresher.checkOtherProductPricesMatch(this.priceTaxIncludedInput.val(),this.productIdInput.val(),o,t)){new xr({id:"modal-confirm-new-price",confirmTitle:this.invoiceSelect.data("modal-edit-price-title"),confirmMessage:this.invoiceSelect.data("modal-edit-price-body"),confirmButtonLabel:this.invoiceSelect.data("modal-edit-price-apply"),closeButtonLabel:this.invoiceSelect.data("modal-edit-price-cancel")},(()=>{this.addProduct(e)})).show()}else this.addProduct(e)}}
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
const{$:Hr}=window;class zr{constructor(){this.wrapperSelector=".js-text-with-length-counter",this.textSelector=".js-countable-text",this.inputSelector=".js-countable-input",Hr(document).on("input",`${this.wrapperSelector} ${this.inputSelector}`,(e=>{const t=Hr(e.currentTarget),r=t.val(),o=t.data("max-length")-r.length;t.closest(this.wrapperSelector).find(this.textSelector).text(o)}))}}
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
const{$:Ur}=window;class Qr{constructor(){this.$orderMessageChangeWarning=Ur(M),this.$messagesContainer=Ur(j)}listenForPredefinedMessageSelection(){this.handlePredefinedMessageSelection()}listenForFullMessagesOpen(){this.onFullMessagesOpen()}handlePredefinedMessageSelection(){Ur(document).on("change",q,(e=>{const t=Ur(e.currentTarget).val();if(!t)return;const r=this.$messagesContainer.find(`div[data-id=${t}]`).text().trim(),o=Ur(L),s=o.val();(null==s?void 0:s.trim())===r||o.val()&&!window.confirm(this.$orderMessageChangeWarning.text())||(o.val(r),o.trigger("input"))}))}onFullMessagesOpen(){Ur(document).on("click",B,(()=>this.scrollToMsgListBottom()))}scrollToMsgListBottom(){const e=Ur(N),t=document.querySelector(G),r=window.setInterval((()=>{e.hasClass("show")&&t&&(t.scrollTop=null==t?void 0:t.scrollHeight,clearInterval(r))}),10)}}
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
const{$:Vr}=window;Vr((()=>{const r="free_shipping";new ft,new zr;const o=new Ar,s=new Gr(Vr(_e)),n=new Zr;o.listenForProductPack(),o.listenForProductDelete(),o.listenForProductEdit(),o.listenForProductAdd(),o.listenForProductPagination(),o.listenForRefund(),o.listenForCancelProduct(),s.listenForSearch(),s.onItemClickedCallback=e=>n.setProduct(e),Vr(t).on("click",(e=>{Vr(e.currentTarget).closest("tr").next(":first").toggleClass("d-none")})),function(){const e=Vr(u);Vr(l).on("input",(()=>{e.prop("disabled",!1)}))}(),function(){const e=Vr(ht);Vr(ut).on("input",(()=>{e.prop("disabled",!1)}))}(),function(){const e=Vr(y),t=Vr(P);Vr(I).on("change",(r=>{const o=Vr(r.currentTarget),s=Vr("option:selected",o),n=o.val();t.css("background-color",s.data("background-color")),t.toggleClass("is-bright",void 0!==s.data("is-bright")),e.prop("disabled",parseInt(n,10)===e.data("orderStatusId"))}))}(),new gt;const i=new Qr;i.listenForPredefinedMessageSelection(),i.listenForFullMessagesOpen(),Vr(a).on("click",(e=>{e.preventDefault(),function(){const e=Vr(c),t=Vr(a),r=t.hasClass("is-opened");r?(t.removeClass("is-opened"),e.addClass("d-none")):(t.addClass("is-opened"),e.removeClass("d-none"));t.find(".material-icons").text(r?"add":"remove")}()})),Vr(ct).on("click",(e=>{e.preventDefault(),function(){const e=Vr(lt),t=Vr(ct),r=t.hasClass("is-opened");t.toggleClass("is-opened",!r),e.toggleClass("d-none",r);t.find(".material-icons").text(r?"add":"remove")}()})),Vr(at).on("click",(()=>{const t=document.title;document.title=Vr(e).data("orderTitle"),window.print(),document.title=t})),function(){const e=Vr(h),t=e.find("form"),o=e.find(p),s=e.find(x),n=t.find(v),i=n.closest(".form-group");e.on("shown.bs.modal",(()=>{Vr(b).prop("disabled",!0)})),t.find(m).on("keyup",(e=>{const t=Vr(e.currentTarget).val();Vr(b).prop("disabled",0===t.trim().length)})),t.find(_).on("change",(e=>{const t=Vr(e.currentTarget).is(":checked");o.prop("disabled",t)})),t.find(f).on("change",(e=>{const o=Vr(e.currentTarget).val(),d=t.find(g);"amount"===o?(s.removeClass("d-none"),d.html(d.data("currencySymbol"))):s.addClass("d-none"),"percent"===o&&d.html("%"),n.prop("disabled",o===r),i.toggleClass("d-none",o===r)}))}(),function(){const e=Vr(E);Vr(S).on("click",(t=>{e.find(O).val(Vr(t.currentTarget).data("addressType"))}))}(),Vr(it).find(".nav-tabs li:first-child a").tab("show")}))})(),window.order_view=o})();