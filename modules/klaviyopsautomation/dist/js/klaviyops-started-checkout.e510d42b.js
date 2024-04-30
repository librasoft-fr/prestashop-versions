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
*/(function(){function t(t){var e={ajax:!0,token:klStartedCheckout.token,email:t,cartId:klStartedCheckout.cartId};$.ajax({type:"POST",data:e,url:klStartedCheckout.baseUrl,success:function(t){localStorage.getItem("klaviyops_debug")&&console.log(t)}})}function e(t){cvalue=btoa(JSON.stringify(t));var e=new Date;e.setTime(e.getTime()+63072e6);var a="expires="+e.toUTCString();document.cookie="__kla_id="+cvalue+";"+a+"; path=/"}window.addEventListener("load",(function(){klStartedCheckout.email?t(klStartedCheckout.email):$(klStartedCheckout.emailInputSelector).on("change",(function(){var a=$(this).val();e({$email:a}),t(a)}))}))})();