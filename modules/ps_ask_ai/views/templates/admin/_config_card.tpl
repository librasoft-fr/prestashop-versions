{**
 * AsKAI configuration card — provider / API key form.
 *}
<div class="psai-card" id="ps-ask-ai-config-card">
    <div class="psai-card__header">
        <i class="icon-cog" style="font-size:18px; color:#3f3f3d;"></i>
        <span style="font-weight:bold; font-size:16px; color:#1d1d1b;">{l s='AsKAI configuration' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
        {if !$isAccountLinked}
            <i class="icon-lock" style="font-size:14px; color:#9ca3af; margin-left:2px;"></i>
        {/if}
    </div>

    {if !$isAccountLinked}
    {* Locked state — account not linked *}
    <div style="padding:20px 24px; border-top:1px solid #eeeeee;">
        <p style="font-size:14px; color:#9ca3af; margin:0;">
            {l s='After signing in, choose your AI provider and add your API key to activate Ask AI.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </p>
    </div>

    {else}
    <div class="psai-card__body">

        {if !$aiIsConfigured}
        <p id="psai-guidance" style="font-size:13px; color:#5e5e5e; margin:0;">
            {l s='Choose your AI provider and paste your API key to get started.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
        </p>
        {/if}

        {if $aiConfigSuccess}
        <div class="puik-alert puik-alert--success" role="alert">
            <div class="puik-alert__container">
                <div class="puik-alert__content">
                    <span class="material-symbols-rounded puik-alert__icon" aria-hidden="true">check_circle</span>
                    <div class="puik-alert__text">
                        <span class="puik-alert__description">{l s='Configuration saved successfully.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        {if $aiProviderQuotaError}
        <div class="puik-alert puik-alert--warning" role="alert">
            <div class="puik-alert__container">
                <div class="puik-alert__content">
                    <span class="material-symbols-rounded puik-alert__icon" aria-hidden="true">warning</span>
                    <div class="puik-alert__text">
                        <span class="puik-alert__description">
                            {l s='Your API key works, but you\'ve run out of credits on %s.' sprintf=[$aiApiKeyErrorProvider] mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        {/if}

        <form id="ps-ask-ai-config-form" method="post" action="">
        <input type="hidden" name="submitPsAskAiConfig" value="1">
        <input type="hidden" id="psai-action" name="ps_ask_ai_action" value="save">
        <div style="display:flex; flex-direction:column; gap:16px;">

            <!-- API key error (DB flag or live validation) -->
            <div id="psai-key-error" class="puik-alert puik-alert--danger" role="alert" data-msg-template="{l s='We couldn\'t connect to %s with this API key. Check your key or try again later.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}"{if !$aiApiKeyError} style="display:none;"{/if}>
                <div class="puik-alert__container">
                    <div class="puik-alert__content">
                        <span class="material-symbols-rounded puik-alert__icon" aria-hidden="true">error</span>
                        <div class="puik-alert__text">
                            <span class="puik-alert__description" id="psai-key-error-msg">{if $aiApiKeyError}{l s='We couldn\'t connect to %s with this API key. Check your key or try again later.' sprintf=[$aiApiKeyErrorProvider] mod='ps_ask_ai' d='Modules.Psaskai.Admin'}{/if}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider -->
            <div class="psai-field">
                <label class="psai-label" for="ps_ask_ai_provider">{l s='Provider' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</label>
                <select id="ps_ask_ai_provider" name="ps_ask_ai_provider" class="psai-input psai-select" {if $aiIsConfigured}disabled{/if}>
                    <option value="">{l s='Select a provider...' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</option>
                    {foreach from=$aiProviders item=prov}
                    <option value="{$prov.id|escape:'htmlall':'UTF-8'}" {if $prov.id === $aiCurrentProvider}selected{/if}>
                        {$prov.label|escape:'htmlall':'UTF-8'}
                    </option>
                    {/foreach}
                </select>
                {if $aiIsConfigured}
                <input type="hidden" name="ps_ask_ai_provider" value="{$aiCurrentProvider|escape:'htmlall':'UTF-8'}">
                {/if}
            </div>

            <!-- API Key -->
            <div class="psai-field" id="psai-api-key-section"{if !$aiIsConfigured} style="display:none;"{/if}>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <label class="psai-label">{l s='API Key' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</label>
                    <button type="button" id="psai-api-key-help-toggle" class="psai-help-toggle" style="display:none;" aria-expanded="false">{l s='Where do I find my key?' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</button>
                </div>

                <!-- Provider-specific API key help — expanded by toggle -->
                <div id="psai-api-key-help" style="display:none;">
                    <div id="psai-help-openai" class="psai-provider-help" style="display:none;">
                        <ol>
                            <li>{l s='Go to' mod='ps_ask_ai' d='Modules.Psaskai.Admin'} <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com/api-keys</a> {l s='and sign in.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Click Create new secret key, give it a name, then confirm.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Copy the key immediately — it will not be shown again.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                        </ol>
                        <p class="psai-provider-help__warning">⚠️ {l s='A payment method must be set up on your OpenAI account for the key to work.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</p>
                    </div>
                    <div id="psai-help-anthropic" class="psai-provider-help" style="display:none;">
                        <ol>
                            <li>{l s='Go to' mod='ps_ask_ai' d='Modules.Psaskai.Admin'} <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener">console.anthropic.com/settings/keys</a> {l s='and sign in.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Click Create Key, give it a name.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Copy the key (starts with sk-ant-...) — it will not be shown again.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                        </ol>
                        <p class="psai-provider-help__warning">⚠️ {l s='A payment method must be set up for the key to work.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</p>
                    </div>
                    <div id="psai-help-gemini" class="psai-provider-help" style="display:none;">
                        <ol>
                            <li>{l s='Go to' mod='ps_ask_ai' d='Modules.Psaskai.Admin'} <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">aistudio.google.com/app/apikey</a> {l s='and sign in with Google.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Click Create API key, then choose a project.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Copy the key (starts with AIza...).' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                        </ol>
                        <p class="psai-provider-help__warning">⚠️ {l s='A payment method must be set up for the key to work.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</p>
                    </div>
                    <div id="psai-help-mistral" class="psai-provider-help" style="display:none;">
                        <ol>
                            <li>{l s='Go to' mod='ps_ask_ai' d='Modules.Psaskai.Admin'} <a href="https://console.mistral.ai" target="_blank" rel="noopener">console.mistral.ai</a> {l s='and sign in.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='In the left menu, go to API Keys → Create new key, give it a name.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                            <li>{l s='Copy the key immediately — it will not be shown again.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</li>
                        </ol>
                        <p class="psai-provider-help__warning">⚠️ {l s='A payment method must be set up for the key to work.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</p>
                    </div>
                </div>

                {if $aiIsConfigured}
                <div class="psai-masked-wrapper" id="psai-masked-group">
                    <input type="text" class="psai-input psai-input--readonly" value="{$aiCurrentApiKeyMasked|escape:'htmlall':'UTF-8'}" readonly>
                    <button type="button" class="psai-input__replace" id="psai-replace-btn">{l s='Replace' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</button>
                </div>
                {/if}

                <div id="psai-new-key-group"{if $aiIsConfigured} style="display:none;"{/if}>
                    <div id="psai-replace-alert" class="puik-alert puik-alert--info" role="alert" style="display:none; margin-bottom:8px;">
                        <div class="puik-alert__container">
                            <div class="puik-alert__content">
                                <span class="material-symbols-rounded puik-alert__icon" aria-hidden="true">info</span>
                                <div class="puik-alert__text">
                                    <span class="puik-alert__description">{l s='Your new key will replace the existing one when you save.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="psai-password-wrapper">
                        <input type="password" id="ps_ask_ai_api_key" name="ps_ask_ai_api_key" class="psai-input"
                            placeholder="{l s='Enter your API key...' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}" autocomplete="off">
                        <span id="psai-reveal-btn" class="psai-input__reveal" role="button" tabindex="0"
                            aria-pressed="false" aria-label="{l s='Show API key' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}">
                            <span class="material-symbols-rounded">visibility_off</span>
                        </span>
                    </div>
                    <p class="psai-help" style="margin-top:6px;">
                        {l s='Your API key is stored securely and never shared. It will be masked after saving.' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="psai-actions">
                <button type="button" id="psai-cancel-btn" class="psai-btn psai-btn--secondary" style="display:none;">
                    {l s='Cancel' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
                </button>
                {if $aiIsConfigured}
                <button type="button" id="psai-change-btn" class="psai-btn psai-btn--secondary">
                    {l s='Change provider' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
                </button>
                {/if}
                <button type="submit" id="psai-save-btn" class="psai-btn psai-btn--primary"{if $aiIsConfigured} style="display:none;"{/if} disabled>
                    {l s='Save' mod='ps_ask_ai' d='Modules.Psaskai.Admin'}
                </button>
            </div>

        </div>
        </form>
    </div>
    {/if}
</div>
