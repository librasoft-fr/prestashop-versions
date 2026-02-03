{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{block name='order_products_table'}
  <div class="box hidden-sm-down">
    <table id="order-products" class="table table-bordered">
      <thead class="thead-default">
        <tr>
          <th>{l s='Product' d='Shop.Theme.Catalog'}</th>
          <th>{l s='Quantity' d='Shop.Theme.Catalog'}</th>
          <th>{l s='Unit price' d='Shop.Theme.Catalog'}</th>
          <th>{l s='Total price' d='Shop.Theme.Catalog'}</th>
        </tr>
      </thead>
      {foreach $order->order_shipments['physical_products'] item=shipment}
        {foreach from=$shipment['products'] item=product}
          {include file='./order-detail-product-line-no-return.tpl' product=$product carrier_name=$shipment['carrier']['name']}
        {/foreach}
      {/foreach}
      {foreach $order->order_shipments['virtual_products'] item=product}
        {include file='./order-detail-product-line-no-return.tpl' product=$product}
      {/foreach}
      <tfoot>
        {foreach $order.subtotals as $line}
          {if $line.value}
            <tr class="text-xs-right line-{$line.type}">
              <td colspan="3">{$line.label}</td>
              <td>{$line.value}</td>
            </tr>
          {/if}
        {/foreach}
        <tr class="text-xs-right line-{$order.totals.total.type}">
          <td colspan="3">{$order.totals.total.label}</td>
          <td>{$order.totals.total.value}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="order-items hidden-md-up box">
    {foreach $order->order_shipments['physical_products'] item=shipment}
      {foreach from=$shipment['products'] item=product}
        {include file='./order-detail-product-line-no-return-mobile.tpl' product=$product carrier_name=$shipment['carrier']['name']}
      {/foreach}
    {/foreach}
    {foreach $order->order_shipments['virtual_products'] item=product}
      {include file='./order-detail-product-line-no-return-mobile.tpl' product=$product}
    {/foreach}
  </div>
  <div class="order-totals hidden-md-up box">
    {foreach $order.subtotals as $line}
      {if $line.value}
        <div class="order-total row">
          <div class="col-xs-8"><strong>{$line.label}</strong></div>
          <div class="col-xs-4 text-xs-right">{$line.value}</div>
        </div>
      {/if}
    {/foreach}
    <div class="order-total row">
      <div class="col-xs-8"><strong>{$order.totals.total.label}</strong></div>
      <div class="col-xs-4 text-xs-right">{$order.totals.total.value}</div>
    </div>
  </div>
{/block}
