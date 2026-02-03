<div class="box">
  <table class="table table-striped table-bordered hidden-sm-down">
    <thead class="thead-default">
      <tr>
        <th>{l s='Date' d='Shop.Theme.Global'}</th>
        <th>{l s='Carrier' d='Shop.Theme.Checkout'}</th>
        <th>{l s='Weight' d='Shop.Theme.Checkout'}</th>
        <th>{l s='Shipping cost' d='Shop.Theme.Checkout'}</th>
        <th>{l s='Tracking number' d='Shop.Theme.Checkout'}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$order.shipping item=line}
        <tr>
          <td>{$line.shipping_date}</td>
          <td>{$line.carrier_name}</td>
          <td>{$line.shipping_weight}</td>
          <td>{$line.shipping_cost}</td>
          <td>{$line.tracking nofilter}</td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  <div class="hidden-md-up shipping-lines">
   {foreach from=$order.shipping item=line}
      <div class="shipping-line">
        <ul>
          <li>
            <strong>{l s='Date' d='Shop.Theme.Global'}</strong> {$line.shipping_date}
          </li>
          <li>
            <strong>{l s='Carrier' d='Shop.Theme.Checkout'}</strong> {$line.carrier_name}
          </li>
          <li>
            <strong>{l s='Weight' d='Shop.Theme.Checkout'}</strong> {$line.shipping_weight}
          </li>
          <li>
            <strong>{l s='Shipping cost' d='Shop.Theme.Checkout'}</strong> {$line.shipping_cost}
          </li>
          <li>
            <strong>{l s='Tracking number' d='Shop.Theme.Checkout'}</strong> {$line.tracking nofilter}
          </li>
        </ul>
      </div>
    {/foreach}
  </div>
</div>
