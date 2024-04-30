<div
    id="klaviyops-admin-config-vuejs"
    data-vue="{$vueData|json_encode|escape:'htmlall':'UTF-8'}"
></div>

{if !$vueData.psAccountsError}
    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" type="text/javascript"></script>
{/if}
