{*
 * Since PrestaShop 9, PrestaShop does not escape HTML for some type of flashes messages
 * So, we handle display of flashes messages in this template to be able to display HTML content in flashes messages
 *}
{foreach from=$klaviyoFlashes item="flashes" key="flashType"}
    {foreach from=$flashes item="message"}
        <div class="bootstrap">
            <div class="alert alert-{$flashType}">
                {if $flashType !== 'success'}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                {/if}

                <ul class="list-unstyled">
                    <li>{$message nofilter}</li>
                </ul>
            </div>
        </div>
    {/foreach}
{/foreach}

<div class="klaviyo-container">
    {$psAccounts nofilter}

    <div id="klaviyo-config">
        {$form nofilter}
        {$couponConfig nofilter}
        {$bisConfig nofilter}
        {$orderStatusMapForm nofilter}
        {$couponsGenerator nofilter}
    </div>

    <script src="{$chunkVendorJs|escape:'htmlall':'UTF-8'}"></script>
    <script src="{$adminConfigJs|escape:'htmlall':'UTF-8'}"></script>
</div>
