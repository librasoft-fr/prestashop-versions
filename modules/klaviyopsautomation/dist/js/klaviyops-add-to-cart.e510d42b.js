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
*/(function(){function e(){var e=window.klaviyo||[];$.ajax({url:"/klaviyo/events/add-to-cart",type:"POST",dataType:"json",success:function(o){"undefined"==typeof o.errors||o.errors||e.push(["track","Added to Cart",o.data]),localStorage.getItem("klaviyops_debug")&&console.log(o)}})}"undefined"!=typeof prestashop&&$(document).ready((function(){prestashop.on("updateCart",e)}))})();