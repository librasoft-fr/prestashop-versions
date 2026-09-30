<div class="klaviyops-admin-prompt">
    <div class="klaviyops-admin-prompt-content">
        <div class="klaviyops-admin-prompt-content-inner">
            <div class="klaviyops-admin-prompt-content-logo">
                <img
                    src="{$klaviyo_edition_logo|escape:'htmlall':'UTF-8'}"
                    alt="{l s='PrestaShop Automation with Klaviyo'}"
                    height="62"
                    width="200"
                    decoding="async"
                    loading="lazy"
                >
            </div>
            <div class="klaviyops-admin-prompt-content-text">
                {if $heading_text !== null}
                    <div class="h1">{$heading_text|escape:'htmlall':'UTF-8'}</div>
                {/if}

                {if $sub_heading_text !== null}
                    <div class="h2">{$sub_heading_text|escape:'htmlall':'UTF-8'}</div>
                {/if}

                <p>{$info_text|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
        <div class="klaviyops-admin-prompt-content-cta">
            <a href="{$klaviyo_link|escape:'htmlall':'UTF-8'}" class="btn" target="_blank" rel="noopener">
                {l s='Learn more'}
            </a>
            <div class="klaviyops-admin-prompt-content-cta-link-wrapper">
                {l s='Already have an account ?'}
                <a href="{$klaviyo_module_link|escape:'htmlall':'UTF-8'}" class="klaviyops-admin-prompt-content-cta-link">{l s='Get started here.'}</a>
            </div>
        </div>
    </div>
</div>
