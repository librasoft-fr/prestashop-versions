{**
 * Copyright (c) 2026 PrestaShop SA
 * This file is part of PrestaShop AskAI.
 *}

<link rel="stylesheet" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/configure.css">

{if $configFeedback}{$configFeedback nofilter}{/if}

<div
    id="ps-ask-ai-page"
    data-is-configured="{if $aiIsConfigured}true{else}false{/if}"
    data-current-provider="{$aiCurrentProvider|escape:'htmlall':'UTF-8'}"
    data-test-key-url="{$testKeyUrl|escape:'htmlall':'UTF-8'}"
    data-track-url="{$trackUrl|escape:'htmlall':'UTF-8'}"
>

    {if !$isAccountLinked}
    <div id="ps-accounts-container" style="margin-top:20px;">
        <prestashop-accounts></prestashop-accounts>
    </div>
    {/if}

    {if $aiUpdateAvailable}
        {include file="module:ps_ask_ai/views/templates/admin/_alert_update.tpl"}
    {/if}

    {if $isAccountLinked && $psMaintenanceModeEnabled}
        {include file="module:ps_ask_ai/views/templates/admin/_alert_maintenance.tpl"}
    {/if}

    {if $isAccountLinked && !$psWebserviceEnabled}
        {assign var="webserviceAlertVariant" value="disabled"}
        {include file="module:ps_ask_ai/views/templates/admin/_alert_webservice.tpl"}
    {/if}

    {include file="module:ps_ask_ai/views/templates/admin/_config_card.tpl"}

    {if $isAccountLinked && $psWebserviceEnabled}
        {assign var="webserviceAlertVariant" value="enabled"}
        {include file="module:ps_ask_ai/views/templates/admin/_alert_webservice.tpl"}
    {/if}

    {if $isAccountLinked}
    <div id="ps-accounts-container" style="margin-top:20px;">
        <prestashop-accounts></prestashop-accounts>
    </div>
    {/if}

    {if $urlAccountsCdn}
    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel="preload"></script>
    <script>
    {literal}
        document.addEventListener('DOMContentLoaded', function () {
            if (window.psaccountsVue) { window.psaccountsVue.init(); }
        });
    {/literal}
    </script>
    {/if}

</div>

<script src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/configure.js" defer></script>
