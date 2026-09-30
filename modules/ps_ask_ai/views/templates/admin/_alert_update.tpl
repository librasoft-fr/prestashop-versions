{**
 * Module update banner — shown when a newer ps_ask_ai version is available
 * (detection via ps_mbo, see UpdateAvailabilityService).
 *
 * Styled 1:1 with the playground ConfigPage banner (PUiK "info" look): all
 * layout/colors live in configure.css (.psai-update-banner*), icons use the
 * Material Symbols Rounded font already loaded on the config page (same as the
 * config card), the purple version tag is the "update" semantic slot.
 *
 * The install state machine lives in configure.js (#psai-update-banner):
 * idle → downloading → installing → brief terminal bar → refresh prompt /
 * persistent error block. Same flow as the drawer pill, vanilla JS here.
 * Requires: $aiUpdateCurrentVersion, $aiUpdateTargetVersion, $aiUpdateInstallUrl
 *}
<div
    id="psai-update-banner"
    class="psai-update-banner"
    role="alert"
    data-install-url="{$aiUpdateInstallUrl|escape:'htmlall':'UTF-8'}"
    data-target-version="{$aiUpdateTargetVersion|escape:'htmlall':'UTF-8'}"
>
    <span class="material-symbols-rounded psai-update-banner__icon" id="psai-update-icon" aria-hidden="true">info</span>

    {* Idle: available + version tag + install + dismiss *}
    <div class="psai-update-banner__row" id="psai-update-idle">
        <span>{l s='New version available' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
        <span class="psai-update-tag">{$aiUpdateCurrentVersion|escape:'htmlall':'UTF-8'} &rarr; {$aiUpdateTargetVersion|escape:'htmlall':'UTF-8'}</span>
        <span class="psai-update-banner__spacer"></span>
        <button type="button" class="btn btn-primary btn-sm" id="psai-update-install-btn">
            {l s='Install' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </button>
        <button type="button" class="psai-update-banner__close" id="psai-update-dismiss-btn" aria-label="{l s='Dismiss the update notification' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}">
            <span class="material-symbols-rounded" aria-hidden="true">close</span>
        </button>
    </div>

    {* Downloading / installing: simulated progress (no real % from MBO) *}
    <div class="psai-update-banner__progress" id="psai-update-progress" style="display:none;">
        <span id="psai-update-progress-label" data-downloading="{l s='Downloading…' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}" data-installing="{l s='Installing…' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}" data-installed="{l s='Installed' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}" data-failed="{l s='Failed' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}"></span>
        <div class="progress psai-update-banner__bar">
            <div class="progress-bar" id="psai-update-progress-bar" role="progressbar" style="width:0%;" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    {* Success: installed + manual refresh (never automatic) *}
    <div class="psai-update-banner__row" id="psai-update-success" style="display:none;">
        <span>{l s='Update installed' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
        <span class="psai-update-banner__spacer"></span>
        <button type="button" class="btn btn-primary btn-sm" id="psai-update-refresh-btn">
            {l s='Refresh the page' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </button>
    </div>

    {* Error: persistent recovery block (no toast) *}
    <div class="psai-update-banner__row" id="psai-update-error" style="display:none;">
        <span>{l s='The update failed.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
        <span class="psai-update-banner__spacer"></span>
        <button type="button" class="btn btn-primary btn-sm" id="psai-update-retry-btn">
            {l s='Retry' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </button>
    </div>
</div>
