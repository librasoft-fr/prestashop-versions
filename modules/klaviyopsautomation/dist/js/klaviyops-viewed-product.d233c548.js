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
*/(function(){window.addEventListener("load",(function(){var e=window.klaviyo||[],t={$value:klProduct.eventValue,ProductName:klProduct.ProductName,ProductID:klProduct.ProductID,Price:klProduct.Price,PriceInclTax:klProduct.PriceInclTax,SpecialPrice:klProduct.SpecialPrice,Categories:klProduct.Categories,Tags:klProduct.Tags,ImageURL:klProduct.Image,URL:klProduct.Link,ShopID:klProduct.ShopID,LangID:klProduct.LangID,external_catalog_id:klProduct.external_catalog_id,integration_key:klProduct.integration_key};e.push(["track","Viewed Product",t]),e.push(["trackViewedItem",{Title:t.ProductName,ItemId:t.ProductID,Categories:t.Categories,ImageUrl:t.ImageURL,Url:t.URL,Metadata:{Price:t.Price}}])}))})();