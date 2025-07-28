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
*/(function(){!function(){if(!window.klaviyo){window._klOnsite=window._klOnsite||[];try{window.klaviyo=new Proxy({},{get:function(n,i){return"push"===i?function(){var n;(n=window._klOnsite).push.apply(n,arguments)}:function(){for(var n=arguments.length,o=new Array(n),t=0;t<n;t++)o[t]=arguments[t];var w="function"==typeof o[o.length-1]?o.pop():void 0,u=new Promise((function(n){window._klOnsite.push([i].concat(o,[function(i){w&&w(i),n(i)}]))}));return u}}})}catch(n){window.klaviyo=window.klaviyo||[],window.klaviyo.push=function(){var n;(n=window._klOnsite).push.apply(n,arguments)}}}}(),null!==klCustomer&&klaviyo.push(["identify",klCustomer])})();