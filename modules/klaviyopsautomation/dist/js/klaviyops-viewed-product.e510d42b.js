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
*/(function(){window.addEventListener("load",(function(){var e=window.klaviyo||[],r={$value:klProduct.eventValue,ProductName:klProduct.ProductName,ProductID:klProduct.ProductID,Price:klProduct.Price,PriceInclTax:klProduct.PriceInclTax,SpecialPrice:klProduct.SpecialPrice,Categories:klProduct.Categories,Tags:klProduct.Tags,ImageURL:klProduct.Image,URL:klProduct.Link,ShopID:klProduct.ShopID,LangID:klProduct.LangID};e.push(["track","Viewed Product",r]),e.push(["trackViewedItem",{Title:r.ProductName,ItemId:r.ProductID,Categories:r.Categories,ImageUrl:r.ImageURL,Url:r.URL,Metadata:{Price:r.Price}}])}))})();