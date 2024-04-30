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
*/(function(){!function(){if(!window.klaviyo){window._klOnsite=window._klOnsite||[];try{window.klaviyo=new Proxy({},{get:function(i,n){return"push"===n?function(){var i;(i=window._klOnsite).push.apply(i,arguments)}:function(){for(var i=arguments.length,o=new Array(i),t=0;t<i;t++)o[t]=arguments[t];var e="function"==typeof o[o.length-1]?o.pop():void 0,a=new Promise((function(i){window._klOnsite.push([n].concat(o,[function(n){e&&e(n),i(n)}]))}));return a}}})}catch(i){window.klaviyo=window.klaviyo||[],window.klaviyo.push=function(){var i;(i=window._klOnsite).push.apply(i,arguments)}}}}(),klCustomer.email&&(klaviyo.push(["identify",{$email:klCustomer.email}]),klCustomer.firstName&&klaviyo.push(["identify",{$first_name:klCustomer.firstName}]),klCustomer.lastName&&klaviyo.push(["identify",{$last_name:klCustomer.lastName}]))})();