<div class="klaviyo-container">
    {$psAccounts nofilter}

    <div id="klaviyo-config">
        {$form nofilter}
        {$orderStatusMapForm nofilter}
        {$couponsGenerator nofilter}
    </div>

    <script src="{$chunkVendorJs|escape:'htmlall':'UTF-8'}"></script>
    <script src="{$adminConfigJs|escape:'htmlall':'UTF-8'}"></script>
</div>
