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
*/(function(){"use strict";var n={129:function(n,t,e){var o=e(144),r=e(674),i=e.n(r),c=function(){var n=this,t=n._self._c;return n.psAccountsError?n._e():t("div",{staticClass:"ps-accounts"},[t("prestashop-accounts")],1)},s=[],u={props:{psAccountsError:Boolean},mounted(){this.psAccountsError||i().init(),!this.psAccountsError&&i().isOnboardingCompleted()||document.getElementById("klaviyo-config").classList.add("restricted")}},a=u,l=e(1),f=(0,l.Z)(a,c,s,!1,null,null,null),p=f.exports;const d=!1,_=document.getElementById("klaviyops-admin-config-vuejs");o.ZP.config.productionTip=!1,o.ZP.config.devtools=d,_&&(o.ZP.use(i()),_&&new o.ZP({render:n=>n(p,{props:JSON.parse(_.dataset.vue)})}).$mount(_)),$('input[name="KLAVIYO_REAL_TIME_EVENT_ENABLE"]').on("change",(function(){const n=$(this).is(":checked")&&1==$(this).val();$('input[name="KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE"]').prop("disabled",!n),n||$("#KLAVIYO_TRANSACTIONAL_EMAIL_ENABLE_off").prop("checked",!0)})),$(".kl_tinymce_no_paragraph").length>0&&tinySetup({editor_selector:"kl_tinymce_no_paragraph",force_br_newlines:!0,force_p_newlines:!1,forced_root_block:"",plugins:"code,colorpicker,paste,link",toolbar1:"code,colorpicker,bold,italic,underline,strikethrough,link",toolbar2:"",valid_elements:"span[style|class],strong,em,br,a[*]",paste_as_text:!0,paste_text_sticky:!0})}},t={};function e(o){var r=t[o];if(void 0!==r)return r.exports;var i=t[o]={exports:{}};return n[o].call(i.exports,i,i.exports,e),i.exports}e.m=n,function(){var n=[];e.O=function(t,o,r,i){if(!o){var c=1/0;for(l=0;l<n.length;l++){o=n[l][0],r=n[l][1],i=n[l][2];for(var s=!0,u=0;u<o.length;u++)(!1&i||c>=i)&&Object.keys(e.O).every((function(n){return e.O[n](o[u])}))?o.splice(u--,1):(s=!1,i<c&&(c=i));if(s){n.splice(l--,1);var a=r();void 0!==a&&(t=a)}}return t}i=i||0;for(var l=n.length;l>0&&n[l-1][2]>i;l--)n[l]=n[l-1];n[l]=[o,r,i]}}(),function(){e.n=function(n){var t=n&&n.__esModule?function(){return n["default"]}:function(){return n};return e.d(t,{a:t}),t}}(),function(){e.d=function(n,t){for(var o in t)e.o(t,o)&&!e.o(n,o)&&Object.defineProperty(n,o,{enumerable:!0,get:t[o]})}}(),function(){e.g=function(){if("object"===typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(n){if("object"===typeof window)return window}}()}(),function(){e.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)}}(),function(){var n={233:0};e.O.j=function(t){return 0===n[t]};var t=function(t,o){var r,i,c=o[0],s=o[1],u=o[2],a=0;if(c.some((function(t){return 0!==n[t]}))){for(r in s)e.o(s,r)&&(e.m[r]=s[r]);if(u)var l=u(e)}for(t&&t(o);a<c.length;a++)i=c[a],e.o(n,i)&&n[i]&&n[i][0](),n[i]=0;return e.O(l)},o=self["webpackChunk"]=self["webpackChunk"]||[];o.forEach(t.bind(null,0)),o.push=t.bind(null,o.push.bind(o))}();var o=e.O(void 0,[998],(function(){return e(129)}));o=e.O(o)})();