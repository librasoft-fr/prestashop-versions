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
import{x as a}from"./sentry-96f4e833.js";import{i as l}from"./is-plan-event-enabled-a83d33b8.js";import"./dom-utils-d77254b8.js";function c(r,u){var i,n;if(!r||!Object.keys(r))return{};var o=r.integrations?Object.keys(r.integrations).filter(function(e){return r.integrations[e]===!1}):[],s=[];return((i=u.remotePlugins)!==null&&i!==void 0?i:[]).forEach(function(e){o.forEach(function(t){(e.name.includes(t)||t.includes(e.name))&&s.push(e.name)})}),((n=u.remotePlugins)!==null&&n!==void 0?n:[]).reduce(function(e,t){return t.settings.subscriptions&&s.includes(t.name)&&t.settings.subscriptions.forEach(function(f){return e["".concat(t.name," ").concat(f.partnerAction)]=!1}),e},{})}function v(r,u){function i(n){var o=r,s=n.event.event;if(o&&s){var e=o[s];if(l(o,e)){var t=c(e,u);n.updateEvent("integrations",a(a(a({},n.event.integrations),e==null?void 0:e.integrations),t))}else return n.updateEvent("integrations",a(a({},n.event.integrations),{All:!1,"Segment.io":!0})),n}return n}return{name:"Schema Filter",version:"0.1.0",isLoaded:function(){return!0},load:function(){return Promise.resolve()},type:"before",page:i,alias:i,track:i,identify:i,group:i}}export{v as schemaFilter};
