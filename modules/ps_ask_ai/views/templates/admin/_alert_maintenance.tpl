{**
 * Maintenance mode warning.
 * Requires: $psMaintenanceAdminUrl, $maintenanceDisableUrl
 *}
<div id="psai-maintenance-alert" class="puik-alert puik-alert--warning" role="alert" style="margin-top:20px;">
    <div class="puik-alert__container">
        <div class="puik-alert__content">
            <span class="material-symbols-rounded puik-alert__icon" aria-hidden="true">warning</span>
            <div class="puik-alert__text">
                <h4 class="puik-alert__title">{l s='Your store is in maintenance mode' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</h4>
                <p class="puik-alert__description">{l s='Ask AI cannot access your store data while maintenance mode is enabled. Disable it to use the assistant.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</p>
            </div>
        </div>
        {if $maintenanceDisableUrl}
        <button type="button" id="psai-maintenance-disable-btn" class="puik-alert__button" data-url="{$maintenanceDisableUrl|escape:'htmlall':'UTF-8'}">
            {l s='Disable maintenance mode' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </button>
        {else}
        <a href="{$psMaintenanceAdminUrl|escape:'htmlall':'UTF-8'}" target="_blank" rel="noopener" class="puik-alert__button">
            {l s='Disable maintenance mode' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </a>
        {/if}
    </div>
</div>
