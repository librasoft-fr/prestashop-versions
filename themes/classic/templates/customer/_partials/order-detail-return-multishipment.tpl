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
  <form id="order-return-form" class="js-order-return-form" action="{$urls.pages.order_follow}" method="post">

    <div class="box hidden-sm-down">
      <table id="order-products" class="table table-bordered return">
        <thead class="thead-default">
          <tr>
            <th class="head-checkbox"><input type="checkbox"/></th>
            <th>{l s='Product' d='Shop.Theme.Catalog'}</th>
            <th>{l s='Quantity' d='Shop.Theme.Catalog'}</th>
            <th>{l s='Returned' d='Shop.Theme.Customeraccount'}</th>
            <th>{l s='Unit price' d='Shop.Theme.Catalog'}</th>
            <th>{l s='Total price' d='Shop.Theme.Catalog'}</th>
          </tr>
        </thead>
        {foreach $order->order_shipments['physical_products'] item=shipment}
          {foreach from=$shipment['products'] item=product}
            {include file='./order-detail-product-line-return.tpl' product=$product carrier_name=$shipment['carrier']['name']}
          {/foreach}
        {/foreach}
        {foreach $order->order_shipments['virtual_products'] item=product}
          {include file='./order-detail-product-line-return.tpl' product=$product}
        {/foreach}
        <tfoot>
          {foreach $order.subtotals as $line}
            {if $line.value}
              <tr class="text-xs-right line-{$line.type}">
                <td colspan="5">{$line.label}</td>
                <td colspan="2">{$line.value}</td>
              </tr>
            {/if}
          {/foreach}
          <tr class="text-xs-right line-{$order.totals.total.type}">
            <td colspan="5">{$order.totals.total.label}</td>
            <td colspan="2">{$order.totals.total.value}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="order-items hidden-md-up box">
      {foreach $order->order_shipments['physical_products'] item=shipment}
        {foreach from=$shipment['products'] item=product}
          {include file='./order-detail-product-line-return-mobile.tpl' product=$product carrier_name=$shipment['carrier']['name']}
        {/foreach}
      {/foreach}
      {foreach $order->order_shipments['virtual_products'] item=product}
        {include file='./order-detail-product-line-return-mobile.tpl' product=$product}
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

    <div class="box">
      <header>
        <h3>{l s='Merchandise return' d='Shop.Theme.Customeraccount'}</h3>
        <p>{l s='If you wish to return one or more products, please mark the corresponding boxes and provide an explanation for the return. When complete, click the button below.' d='Shop.Theme.Customeraccount'}</p>
      </header>
      <section class="form-fields">
        <div class="form-group">
          <textarea cols="67" rows="3" name="returnText" class="form-control"></textarea>
        </div>
      </section>
      <footer class="form-footer">
        <input type="hidden" name="id_order" value="{$order.details.id}">
        <button class="form-control-submit btn btn-primary" type="submit" name="submitReturnMerchandise">
          {l s='Request a return' d='Shop.Theme.Customeraccount'}
        </button>
      </footer>
    </div>

  </form>
{/block}
