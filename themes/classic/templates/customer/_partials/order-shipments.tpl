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
          <td>{$line.date_add}</td>
          <td>{$line.carrier_name}</td>
          <td>{$line.package_weight}</td>
          <td>{$line.package_cost}</td>
          <td>
            {if $line.carrier_tracking_url}
              <a href="{$line.carrier_tracking_url}" target="_blank">{$line.tracking_number}</a>
            {elseif $line.tracking_number}
              {$line.tracking_number}
            {else}
              -
            {/if}
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
  <div class="hidden-md-up shipping-lines">
   {foreach from=$order.shipping item=line}
      <div class="shipping-line">
        <ul>
          <li>
            <strong>{l s='Date' d='Shop.Theme.Global'}</strong> {$line.date_add}
          </li>
          <li>
            <strong>{l s='Carrier' d='Shop.Theme.Checkout'}</strong> {$line.carrier_name}
          </li>
          <li>
            <strong>{l s='Weight' d='Shop.Theme.Checkout'}</strong> {$line.package_weight}
          </li>
          <li>
            <strong>{l s='Shipping cost' d='Shop.Theme.Checkout'}</strong> {$line.package_cost}
          </li>
          <li>
            <strong>{l s='Tracking number' d='Shop.Theme.Checkout'}</strong>
            {if $line.carrier_tracking_url}
              <a href="{$line.carrier_tracking_url}" target="_blank">{$line.tracking_number}</a>
            {elseif $line.tracking_number}
              {$line.tracking_number}
            {else}
              -
            {/if}
          </li>
        </ul>
      </div>
    {/foreach}
  </div>
</div>
