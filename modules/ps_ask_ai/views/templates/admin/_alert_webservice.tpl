{**
 * Webservice alert — two variants driven by $webserviceAlertVariant:
 *   'disabled' → warning banner before the config card
 *   'enabled'  → confirmation banner after the config card
 * Requires: $psWebserviceAdminUrl, $psWebserviceEnabledOnce
 *
 * The 'disabled' CTA opens the shared Vue store-access modal (mounted by the
 * BO header bundle) via a window CustomEvent — no redirect, no duplicated
 * modal markup. The 'enabled' variant keeps the native AdminWebservice link.
 *}

{if $webserviceAlertVariant === 'disabled'}
<div class="ps-ask-ai-alert">
    <div class="ps-ask-ai-alert__icon"><i class="icon-key"></i></div>
    <div class="ps-ask-ai-alert__body">
        <strong class="ps-ask-ai-alert__title">{l s='Allow Ask AI to act on your store' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</strong>
        <span class="ps-ask-ai-alert__desc">{l s='Ask AI needs access to read and write your store data (products, orders, customers). Enable store access to get started.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
    </div>
    <button
        type="button"
        class="ps-ask-ai-alert__btn"
        data-ps-ask-ai-store-access
        data-revoked="{if $psWebserviceEnabledOnce}1{else}0{/if}"
    >
        {if $psWebserviceEnabledOnce}
            {l s='Re-enable store access' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        {else}
            {l s='Enable store access' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        {/if}
    </button>
</div>
{else}
<div class="ps-ask-ai-alert" style="margin-top:12px;">
    <div class="ps-ask-ai-alert__icon"><i class="icon-key"></i></div>
    <div class="ps-ask-ai-alert__body">
        <strong class="ps-ask-ai-alert__title">
            {l s='Allow Ask AI to act on your store' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
            <i class="icon-check-circle" style="color:#207f4b; font-size:14px; margin-left:4px; vertical-align:middle;"></i>
        </strong>
        <span class="ps-ask-ai-alert__desc">{l s='Ask AI has access to your store data (products, orders, customers).' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
    </div>
    <a href="{$psWebserviceAdminUrl|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener" class="ps-ask-ai-alert__btn ps-ask-ai-alert__btn--secondary">
        {l s='Disable store access' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
    </a>
</div>
{/if}
