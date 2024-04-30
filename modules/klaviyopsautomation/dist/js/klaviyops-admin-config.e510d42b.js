/**
* Klaviyo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Commercial License
* you can't distribute, modify or sell this code
*
* DISCLAIMER
*
* Do not edit or add to this file
* If you need help please contact extensions@klaviyo.com
*
* @author    Klaviyo
* @copyright Klaviyo
* @license   commercial
*/(function(){"use strict";var n={129:function(n,t,r){var o=r(144),e=r(674),i=r.n(e),u=function(){var n=this,t=n._self._c;return n.psAccountsError?n._e():t("div",{staticClass:"ps-accounts"},[t("prestashop-accounts")],1)},c=[],s={props:{psAccountsError:Boolean},mounted(){this.psAccountsError||i().init(),!this.psAccountsError&&i().isOnboardingCompleted()||document.getElementById("klaviyo-config").classList.add("restricted")}},f=s,a=r(1),l=(0,a.Z)(f,u,c,!1,null,null,null),p=l.exports;const d=!1,v=document.getElementById("klaviyops-admin-config-vuejs");o.ZP.config.productionTip=!1,o.ZP.config.devtools=d,v&&(o.ZP.use(i()),v&&new o.ZP({render:n=>n(p,{props:JSON.parse(v.dataset.vue)})}).$mount(v)),$('input[name="KLAVIYO_REAL_TIME_EVENT_ENABLE"]').on("change",(function(){const n=$(this).is(":checked")&&1==$(this).val();$('input[name="KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE"]').prop("disabled",!n),n||$("#KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE_off").prop("checked",!0)}))}},t={};function r(o){var e=t[o];if(void 0!==e)return e.exports;var i=t[o]={exports:{}};return n[o].call(i.exports,i,i.exports,r),i.exports}r.m=n,function(){var n=[];r.O=function(t,o,e,i){if(!o){var u=1/0;for(a=0;a<n.length;a++){o=n[a][0],e=n[a][1],i=n[a][2];for(var c=!0,s=0;s<o.length;s++)(!1&i||u>=i)&&Object.keys(r.O).every((function(n){return r.O[n](o[s])}))?o.splice(s--,1):(c=!1,i<u&&(u=i));if(c){n.splice(a--,1);var f=e();void 0!==f&&(t=f)}}return t}i=i||0;for(var a=n.length;a>0&&n[a-1][2]>i;a--)n[a]=n[a-1];n[a]=[o,e,i]}}(),function(){r.n=function(n){var t=n&&n.__esModule?function(){return n["default"]}:function(){return n};return r.d(t,{a:t}),t}}(),function(){r.d=function(n,t){for(var o in t)r.o(t,o)&&!r.o(n,o)&&Object.defineProperty(n,o,{enumerable:!0,get:t[o]})}}(),function(){r.g=function(){if("object"===typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(n){if("object"===typeof window)return window}}()}(),function(){r.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)}}(),function(){var n={233:0};r.O.j=function(t){return 0===n[t]};var t=function(t,o){var e,i,u=o[0],c=o[1],s=o[2],f=0;if(u.some((function(t){return 0!==n[t]}))){for(e in c)r.o(c,e)&&(r.m[e]=c[e]);if(s)var a=s(r)}for(t&&t(o);f<u.length;f++)i=u[f],r.o(n,i)&&n[i]&&n[i][0](),n[i]=0;return r.O(a)},o=self["webpackChunk"]=self["webpackChunk"]||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))}();var o=r.O(void 0,[998],(function(){return r(129)}));o=r.O(o)})();