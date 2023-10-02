(()=>{var t={5158:(t,i,e)=>{"use strict";e.d(i,{Z:()=>r});const r=
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
class{constructor(t){this.message=t,this.name="LocalizationException"}}},9475:(t,i,e)=>{"use strict";e.d(i,{Z:()=>n});var r=e(5158);
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
 */const n=class{constructor(t,i,e,r,n,s,o,a,c,p,u){this.decimal=t,this.group=i,this.list=e,this.percentSign=r,this.minusSign=n,this.plusSign=s,this.exponential=o,this.superscriptingExponent=a,this.perMille=c,this.infinity=p,this.nan=u,this.validateData()}getDecimal(){return this.decimal}getGroup(){return this.group}getList(){return this.list}getPercentSign(){return this.percentSign}getMinusSign(){return this.minusSign}getPlusSign(){return this.plusSign}getExponential(){return this.exponential}getSuperscriptingExponent(){return this.superscriptingExponent}getPerMille(){return this.perMille}getInfinity(){return this.infinity}getNan(){return this.nan}validateData(){if(!this.decimal||"string"!=typeof this.decimal)throw new r.Z("Invalid decimal");if(!this.group||"string"!=typeof this.group)throw new r.Z("Invalid group");if(!this.list||"string"!=typeof this.list)throw new r.Z("Invalid symbol list");if(!this.percentSign||"string"!=typeof this.percentSign)throw new r.Z("Invalid percentSign");if(!this.minusSign||"string"!=typeof this.minusSign)throw new r.Z("Invalid minusSign");if(!this.plusSign||"string"!=typeof this.plusSign)throw new r.Z("Invalid plusSign");if(!this.exponential||"string"!=typeof this.exponential)throw new r.Z("Invalid exponential");if(!this.superscriptingExponent||"string"!=typeof this.superscriptingExponent)throw new r.Z("Invalid superscriptingExponent");if(!this.perMille||"string"!=typeof this.perMille)throw new r.Z("Invalid perMille");if(!this.infinity||"string"!=typeof this.infinity)throw new r.Z("Invalid infinity");if(!this.nan||"string"!=typeof this.nan)throw new r.Z("Invalid nan")}}},6965:(t,i,e)=>{"use strict";e.d(i,{Z:()=>s});var r=e(5158),n=e(9475);const s=
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
class{constructor(t,i,e,s,o,a,c,p){if(this.positivePattern=t,this.negativePattern=i,this.symbol=e,this.maxFractionDigits=s,this.minFractionDigits=s<o?s:o,this.groupingUsed=a,this.primaryGroupSize=c,this.secondaryGroupSize=p,!this.positivePattern||"string"!=typeof this.positivePattern)throw new r.Z("Invalid positivePattern");if(!this.negativePattern||"string"!=typeof this.negativePattern)throw new r.Z("Invalid negativePattern");if(!(this.symbol&&this.symbol instanceof n.Z))throw new r.Z("Invalid symbol");if("number"!=typeof this.maxFractionDigits)throw new r.Z("Invalid maxFractionDigits");if("number"!=typeof this.minFractionDigits)throw new r.Z("Invalid minFractionDigits");if("boolean"!=typeof this.groupingUsed)throw new r.Z("Invalid groupingUsed");if("number"!=typeof this.primaryGroupSize)throw new r.Z("Invalid primaryGroupSize");if("number"!=typeof this.secondaryGroupSize)throw new r.Z("Invalid secondaryGroupSize")}getSymbol(){return this.symbol}getPositivePattern(){return this.positivePattern}getNegativePattern(){return this.negativePattern}getMaxFractionDigits(){return this.maxFractionDigits}getMinFractionDigits(){return this.minFractionDigits}isGroupingUsed(){return this.groupingUsed}getPrimaryGroupSize(){return this.primaryGroupSize}getSecondaryGroupSize(){return this.secondaryGroupSize}}},3368:(t,i,e)=>{"use strict";e.d(i,{Z:()=>o});var r=e(5158),n=e(6965);class s extends n.Z{constructor(t,i,e,n,s,o,a,c,p,u){if(super(t,i,e,n,s,o,a,c),this.currencySymbol=p,this.currencyCode=u,!this.currencySymbol||"string"!=typeof this.currencySymbol)throw new r.Z("Invalid currencySymbol");if(!this.currencyCode||"string"!=typeof this.currencyCode)throw new r.Z("Invalid currencyCode")}static getCurrencyDisplay(){return"symbol"}getCurrencySymbol(){return this.currencySymbol}getCurrencyCode(){return this.currencyCode}}const o=s},1658:(t,i,e)=>{var r="[object Symbol]",n=/[\\^$.*+?()[\]{}|]/g,s=RegExp(n.source),o="object"==typeof e.g&&e.g&&e.g.Object===Object&&e.g,a="object"==typeof self&&self&&self.Object===Object&&self,c=o||a||Function("return this")(),p=Object.prototype.toString,u=c.Symbol,g=u?u.prototype:void 0,l=g?g.toString:void 0;function h(t){if("string"==typeof t)return t;if(function(t){return"symbol"==typeof t||function(t){return!!t&&"object"==typeof t}(t)&&p.call(t)==r}(t))return l?l.call(t):"";var i=t+"";return"0"==i&&1/t==-Infinity?"-0":i}t.exports=function(t){var i;return(t=null==(i=t)?"":h(i))&&s.test(t)?t.replace(n,"\\$&"):t}}},i={};function e(r){var n=i[r];if(void 0!==n)return n.exports;var s=i[r]={exports:{}};return t[r](s,s.exports,e),s.exports}e.d=(t,i)=>{for(var r in i)e.o(i,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:i[r]})},e.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),e.o=(t,i)=>Object.prototype.hasOwnProperty.call(t,i),e.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var r={};(()=>{"use strict";e.r(r),e.d(r,{NumberFormatter:()=>a,NumberSpecification:()=>n.Z,NumberSymbol:()=>t.Z,PriceSpecification:()=>i.Z});var t=e(9475),i=e(3368),n=e(6965);
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
const s=e(1658);class o{constructor(t){this.numberSpecification=t}format(t,i){void 0!==i&&(this.numberSpecification=i);const e=Math.abs(t).toFixed(this.numberSpecification.getMaxFractionDigits());let[r,n]=this.extractMajorMinorDigits(e);r=this.splitMajorGroups(r),n=this.adjustMinorDigitsZeroes(n);let s=r;n&&(s+="."+n);const o=this.getCldrPattern(t<0);return s=this.addPlaceholders(s,o),s=this.replaceSymbols(s),s=this.performSpecificReplacements(s),s}extractMajorMinorDigits(t){const i=t.toString().split(".");return[i[0],void 0===i[1]?"":i[1]]}splitMajorGroups(t){if(!this.numberSpecification.isGroupingUsed())return t;const i=t.split("").reverse();let e=[];for(e.push(i.splice(0,this.numberSpecification.getPrimaryGroupSize()));i.length;)e.push(i.splice(0,this.numberSpecification.getSecondaryGroupSize()));e=e.reverse();const r=[];return e.forEach((t=>{r.push(t.reverse().join(""))})),r.join(",")}adjustMinorDigitsZeroes(t){let i=t;return i.length>this.numberSpecification.getMaxFractionDigits()&&(i=i.replace(/0+$/,"")),i.length<this.numberSpecification.getMinFractionDigits()&&(i=i.padEnd(this.numberSpecification.getMinFractionDigits(),"0")),i}getCldrPattern(t){return t?this.numberSpecification.getNegativePattern():this.numberSpecification.getPositivePattern()}replaceSymbols(t){const i=this.numberSpecification.getSymbol(),e={};return e["."]=i.getDecimal(),e[","]=i.getGroup(),e["-"]=i.getMinusSign(),e["%"]=i.getPercentSign(),e["+"]=i.getPlusSign(),this.strtr(t,e)}strtr(t,i){const e=Object.keys(i).map(s);return t.split(RegExp(`(${e.join("|")})`)).map((t=>i[t]||t)).join("")}addPlaceholders(t,i){return i.replace(/#?(,#+)*0(\.[0#]+)*/,t)}performSpecificReplacements(t){return this.numberSpecification instanceof i.Z?t.split("¤").join(this.numberSpecification.getCurrencySymbol()):t}static build(e){let r,s;return r=void 0!==e.numberSymbols?new t.Z(...e.numberSymbols):new t.Z(...e.symbol),s=e.currencySymbol?new i.Z(e.positivePattern,e.negativePattern,r,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize,e.currencySymbol,e.currencyCode):new n.Z(e.positivePattern,e.negativePattern,r,parseInt(e.maxFractionDigits,10),parseInt(e.minFractionDigits,10),e.groupingUsed,e.primaryGroupSize,e.secondaryGroupSize),new o(s)}}const a=o})
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
 */(),window.cldr=r})();