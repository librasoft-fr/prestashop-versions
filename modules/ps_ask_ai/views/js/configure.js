/**
 * Copyright (c) 2026 PrestaShop SA
 * This file is part of PrestaShop AskAI.
 *
 * Loaded with <script defer> — DOM is guaranteed ready on execution.
 * PHP state is passed via data-* attributes on #ps-ask-ai-page.
 */
(function () {
    var page = document.getElementById('ps-ask-ai-page');
    if (!page) { return; }

    var isConfigured    = page.dataset.isConfigured === 'true';
    var currentProvider = page.dataset.currentProvider || '';

    var providerSelect      = document.getElementById('ps_ask_ai_provider');
    var apiKeySection       = document.getElementById('psai-api-key-section');
    var maskedGroup         = document.getElementById('psai-masked-group');
    var newKeyGroup         = document.getElementById('psai-new-key-group');
    var replaceAlert        = document.getElementById('psai-replace-alert');
    var saveBtn             = document.getElementById('psai-save-btn');
    var replaceBtn          = document.getElementById('psai-replace-btn');
    var cancelBtn           = document.getElementById('psai-cancel-btn');
    var changeBtn           = document.getElementById('psai-change-btn');
    var apiKeyInput         = document.getElementById('ps_ask_ai_api_key');
    var guidance            = document.getElementById('psai-guidance');
    var providerHiddenInput = document.querySelector('#ps-ask-ai-config-form input[type="hidden"][name="ps_ask_ai_provider"]');

    var changeProviderMode = false;

    function resetReveal() {
        var revealBtn  = document.getElementById('psai-reveal-btn');
        var revealIcon = revealBtn ? revealBtn.querySelector('.material-symbols-rounded') : null;
        if (!apiKeyInput || !revealBtn || !revealIcon) { return; }
        apiKeyInput.type = 'password';
        revealIcon.textContent = 'visibility_off';
        revealBtn.setAttribute('aria-pressed', 'false');
    }

    function updateSaveBtn() {
        if (!saveBtn) { return; }
        var provider = providerSelect ? providerSelect.value : currentProvider;
        var key      = apiKeyInput ? apiKeyInput.value.trim() : '';
        saveBtn.disabled = (provider === '' || key === '');
    }

    function showApiKeySection(provider) {
        if (!apiKeySection) { return; }
        apiKeySection.style.display = provider ? '' : 'none';
        if (guidance) { guidance.style.display = provider ? 'none' : ''; }
        updateSaveBtn();
    }

    function restoreConfiguredState() {
        changeProviderMode = false;
        if (providerSelect)      { providerSelect.value = currentProvider; providerSelect.disabled = true; }
        if (providerHiddenInput) { providerHiddenInput.disabled = false; }
        if (maskedGroup)         { maskedGroup.style.display  = ''; }
        if (newKeyGroup)         { newKeyGroup.style.display  = 'none'; }
        if (replaceAlert)        { replaceAlert.style.display = 'none'; }
        if (saveBtn)             { saveBtn.style.display      = 'none'; }
        if (cancelBtn)           { cancelBtn.style.display    = 'none'; }
        if (changeBtn)           { changeBtn.style.display    = ''; }
        if (apiKeyInput)         { apiKeyInput.value = ''; }
        resetReveal();
    }

    var trackUrl = page.dataset.trackUrl || '';

    // Fire Event 2 (AskAI Provider Selected) server-side. Fire-and-forget;
    // never block the UI on analytics. The injected URL already carries the
    // CSRF ?_token=, so params MUST be appended via the URL API — string
    // concatenation would corrupt the token.
    function trackProviderSelected(provider) {
        if (!trackUrl || !provider) { return; }
        try {
            var url = new URL(trackUrl, window.location.origin);
            var body = new URLSearchParams();
            body.set('event', 'provider_selected');
            body.set('provider', provider);
            fetch(url.toString(), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).catch(function () { /* analytics is best-effort */ });
        } catch (e) { /* analytics is best-effort */ }
    }

    if (providerSelect) {
        providerSelect.addEventListener('change', function () {
            if (this.value) { trackProviderSelected(this.value); }
            if (!isConfigured || changeProviderMode) { showApiKeySection(this.value); }
            updateProviderHelp(this.value);
        });
    }

    updateProviderHelp(isConfigured ? currentProvider : (providerSelect ? providerSelect.value : ''));
    if (apiKeyInput) { apiKeyInput.addEventListener('input', updateSaveBtn); }

    if (replaceBtn) {
        replaceBtn.addEventListener('click', function () {
            if (maskedGroup)  { maskedGroup.style.display  = 'none'; }
            if (newKeyGroup)  { newKeyGroup.style.display  = ''; }
            if (replaceAlert) { replaceAlert.style.display = ''; }
            if (saveBtn)      { saveBtn.style.display = ''; saveBtn.disabled = true; }
            if (cancelBtn)    { cancelBtn.style.display = ''; }
            if (changeBtn)    { changeBtn.style.display = 'none'; }
            updateSaveBtn();
        });
    }

    if (cancelBtn) { cancelBtn.addEventListener('click', restoreConfiguredState); }

    if (changeBtn) {
        changeBtn.addEventListener('click', function () {
            changeProviderMode = true;
            if (providerSelect)      { providerSelect.value = ''; providerSelect.disabled = false; }
            if (providerHiddenInput) { providerHiddenInput.disabled = true; }
            if (apiKeySection)  { apiKeySection.style.display  = ''; }
            if (maskedGroup)    { maskedGroup.style.display    = 'none'; }
            if (newKeyGroup)    { newKeyGroup.style.display    = ''; }
            if (replaceAlert)   { replaceAlert.style.display   = 'none'; }
            if (apiKeyInput)    { apiKeyInput.value = ''; }
            if (guidance) { guidance.style.display = 'none'; }
            if (saveBtn)  { saveBtn.style.display = ''; saveBtn.disabled = true; }
            if (cancelBtn){ cancelBtn.style.display = ''; }
            changeBtn.style.display = 'none';
            resetReveal();
        });
    }

    var testKeyUrl   = page.dataset.testKeyUrl || '';
    var form         = document.getElementById('ps-ask-ai-config-form');
    var keyErrorEl   = document.getElementById('psai-key-error');
    var keyErrorMsg  = document.getElementById('psai-key-error-msg');
    var saveBtnLabel = saveBtn ? saveBtn.textContent.trim() : '';

    var providerLabels = {
        'gemini':    'Google (Gemini)',
        'anthropic': 'Anthropic (Claude)',
        'openai':    'OpenAI (ChatGPT)',
        'mistral':   'Mistral'
    };

    function showKeyError(provider) {
        if (!keyErrorEl || !keyErrorMsg) { return; }
        var label = providerLabels[provider] || provider;
        // Translated template ("...%s...") comes from the tpl data attribute so
        // the message stays localized; fall back to English if it's missing.
        var template = keyErrorEl.dataset.msgTemplate
            || "We couldn't connect to %s with this API key. Check your key or try again later.";
        keyErrorMsg.textContent = template.replace('%s', label);
        keyErrorEl.style.display = '';
    }

    function hideKeyError() {
        if (keyErrorEl) { keyErrorEl.style.display = 'none'; }
    }

    if (form && testKeyUrl) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            hideKeyError();

            var provider = providerSelect ? providerSelect.value : currentProvider;
            var key      = apiKeyInput ? apiKeyInput.value.trim() : '';

            if (saveBtn) { saveBtn.disabled = true; }

            var url = new URL(testKeyUrl, window.location.origin);
            url.searchParams.set('provider', provider);
            if (key) { url.searchParams.set('api_key', key); }

            fetch(url.toString(), { credentials: 'same-origin' })
                .then(function (res) {
                    return res.json().then(function (data) { return { ok: res.ok, data: data }; });
                })
                .then(function (result) {
                    if (result.ok && result.data.valid) {
                        form.submit();
                    } else {
                        showKeyError(provider);
                        if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = saveBtnLabel; }
                    }
                })
                .catch(function (err) {
                    showKeyError(provider);
                    if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = saveBtnLabel; }
                });
        });
    }

    // PE-1766: the maintenance banner CTA disables maintenance mode in place
    // (no redirect to AdminMaintenance) and removes the banner on success.
    var maintenanceBtn   = document.getElementById('psai-maintenance-disable-btn');
    var maintenanceAlert = document.getElementById('psai-maintenance-alert');

    if (maintenanceBtn) {
        maintenanceBtn.addEventListener('click', function () {
            var url = maintenanceBtn.dataset.url || '';
            if (!url) { return; }
            maintenanceBtn.disabled = true;
            fetch(url, { method: 'POST', credentials: 'same-origin' })
                .then(function (res) {
                    if (!res.ok) { throw new Error('HTTP ' + res.status); }
                    if (maintenanceAlert) { maintenanceAlert.remove(); }
                    window.dispatchEvent(new CustomEvent('ps-ask-ai:maintenance-disabled'));
                })
                .catch(function () {
                    maintenanceBtn.disabled = false;
                });
        });
    }

    var apiKeyHelp = document.getElementById('psai-api-key-help');
    var apiKeyHelpToggle = document.getElementById('psai-api-key-help-toggle');
    var helpPanels = {
        'openai':    document.getElementById('psai-help-openai'),
        'anthropic': document.getElementById('psai-help-anthropic'),
        'gemini':    document.getElementById('psai-help-gemini'),
        'mistral':   document.getElementById('psai-help-mistral'),
    };

    function updateProviderHelp(provider) {
        if (!apiKeyHelp) { return; }
        var hasPanel = provider && helpPanels[provider];
        if (apiKeyHelpToggle) {
            apiKeyHelpToggle.style.display = hasPanel ? '' : 'none';
            apiKeyHelpToggle.setAttribute('aria-expanded', 'false');
        }
        apiKeyHelp.style.display = 'none';
        Object.keys(helpPanels).forEach(function (key) {
            if (helpPanels[key]) { helpPanels[key].style.display = (key === provider) ? '' : 'none'; }
        });
    }

    if (apiKeyHelpToggle) {
        apiKeyHelpToggle.addEventListener('click', function () {
            var isOpen = apiKeyHelp.style.display !== 'none';
            apiKeyHelp.style.display = isOpen ? 'none' : '';
            apiKeyHelpToggle.setAttribute('aria-expanded', String(!isOpen));
        });
    }

    // Store-access CTA: open the shared Vue modal (mounted by the BO header
    // bundle) rather than duplicating it here or redirecting to AdminWebservice.
    var storeAccessBtn = document.querySelector('[data-ps-ask-ai-store-access]');
    if (storeAccessBtn) {
        storeAccessBtn.addEventListener('click', function () {
            window.dispatchEvent(new CustomEvent('ps-ask-ai:open-store-access', {
                detail: { revoked: storeAccessBtn.dataset.revoked === '1' }
            }));
        });
    }

    var revealBtn  = document.getElementById('psai-reveal-btn');
    var revealIcon = revealBtn ? revealBtn.querySelector('.material-symbols-rounded') : null;

    function toggleReveal() {
        if (!apiKeyInput || !revealIcon) { return; }
        var isVisible    = apiKeyInput.type === 'text';
        apiKeyInput.type = isVisible ? 'password' : 'text';
        revealIcon.textContent = isVisible ? 'visibility_off' : 'visibility';
        revealBtn.setAttribute('aria-pressed', String(!isVisible));
    }

    if (revealBtn) {
        revealBtn.addEventListener('click', toggleReveal);
        revealBtn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleReveal(); }
        });
    }

    // --------------------------- Module update banner --------------------------- //
    // Same install machine as the drawer pill (useUpdateInstall.ts), vanilla:
    // idle → downloading → installing (simulated progress, no real % exposed)
    // → brief terminal bar (green/red) → refresh prompt / persistent error block.
    // installUrl is the CORE admin_module_manage_action route (action=upgrade),
    // not a ps_mbo controller: no ps_ask_ai PHP runs during the file swap, and
    // MBO's real download engine still runs underneath via its
    // actionBeforeUpgradeModule hook (independent of this URL's controller).
    (function initUpdateBanner() {
        var banner = document.getElementById('psai-update-banner');
        if (!banner) { return; }

        var DISMISS_KEY        = 'ps-ask-ai-update-banner-dismissed';
        var TICK_MS            = 80;
        var TICK_INCREMENT     = 8;
        var INSTALLING_HOLD_AT = 90;
        var TERMINAL_HOLD_MS   = 700;

        var installUrl    = banner.dataset.installUrl || '';
        var targetVersion = banner.dataset.targetVersion || '';

        // Dismiss is per tab session, scoped to the target version: a later
        // update re-surfaces the banner even within the same session.
        if (!installUrl || sessionStorage.getItem(DISMISS_KEY) === targetVersion) {
            banner.style.display = 'none';
            return;
        }

        var idleRow     = document.getElementById('psai-update-idle');
        var progressRow = document.getElementById('psai-update-progress');
        var successRow  = document.getElementById('psai-update-success');
        var errorRow    = document.getElementById('psai-update-error');
        var icon        = document.getElementById('psai-update-icon');
        var bar         = document.getElementById('psai-update-progress-bar');
        var barLabel    = document.getElementById('psai-update-progress-label');
        var installBtn  = document.getElementById('psai-update-install-btn');
        var dismissBtn  = document.getElementById('psai-update-dismiss-btn');
        var refreshBtn  = document.getElementById('psai-update-refresh-btn');
        var retryBtn    = document.getElementById('psai-update-retry-btn');
        var configCard  = document.getElementById('ps-ask-ai-config-card');

        var running = false;
        var timer   = null;

        function show(el, on) { if (el) { el.style.display = on ? '' : 'none'; } }

        // Freeze the config form while the module's files are being swapped.
        function lockCard(locked) {
            if (configCard) { configCard.classList.toggle('psai-card--locked', locked); }
        }

        function setIcon(name, variant) {
            if (!icon) { return; }
            icon.textContent = name;
            icon.classList.remove('text-success', 'text-danger');
            if (variant) { icon.classList.add('text-' + variant); }
        }

        function setBar(value) {
            if (!bar) { return; }
            bar.style.width = value + '%';
            bar.setAttribute('aria-valuenow', String(value));
        }

        function ramp(target) {
            return new Promise(function (resolve) {
                clearInterval(timer);
                var value = 0;
                setBar(0);
                timer = setInterval(function () {
                    value = Math.min(target, value + TICK_INCREMENT);
                    setBar(value);
                    if (value >= target) { clearInterval(timer); resolve(); }
                }, TICK_MS);
            });
        }

        function requestUpgrade() {
            // module_name is already embedded in installUrl's path — no body needed.
            return fetch(installUrl, {
                method: 'POST',
                credentials: 'same-origin'
            })
                .then(function (res) {
                    if (!res.ok) { return 'error'; }
                    return res.json().then(function (data) {
                        // Core response is keyed by module name: { ps_ask_ai: { status, msg } }.
                        var entry = data && data['ps_ask_ai'];
                        return entry && entry.status === true ? 'success' : 'error';
                    });
                })
                .catch(function () { return 'error'; });
        }

        function run() {
            if (running) { return; }
            running = true;
            lockCard(true);
            show(idleRow, false); show(errorRow, false); show(successRow, false);
            show(progressRow, true);
            setIcon('info', '');
            if (bar) { bar.classList.remove('bg-success', 'bg-danger'); }

            var request = requestUpgrade();

            if (barLabel) { barLabel.textContent = barLabel.dataset.downloading; }
            ramp(100)
                .then(function () {
                    if (barLabel) { barLabel.textContent = barLabel.dataset.installing; }
                    return ramp(INSTALLING_HOLD_AT);
                })
                .then(function () { return request; })
                .then(function (outcome) {
                    var success = outcome === 'success';
                    // Brief terminal bar (green/red), then hand off.
                    setBar(100);
                    if (barLabel) { barLabel.textContent = success ? barLabel.dataset.installed : barLabel.dataset.failed; }
                    if (bar) { bar.classList.add(success ? 'bg-success' : 'bg-danger'); }
                    setTimeout(function () {
                        running = false;
                        show(progressRow, false);
                        if (success) {
                            // Card stays frozen: a manual refresh is required
                            // (never automatic) before touching the module again.
                            setIcon('check_circle', 'success');
                            show(successRow, true);
                        } else {
                            setIcon('error', 'danger');
                            show(errorRow, true);
                            lockCard(false);
                        }
                    }, TERMINAL_HOLD_MS);
                });
        }

        if (installBtn) { installBtn.addEventListener('click', run); }
        if (retryBtn)   { retryBtn.addEventListener('click', run); }
        if (refreshBtn) { refreshBtn.addEventListener('click', function () { window.location.reload(); }); }
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function () {
                sessionStorage.setItem(DISMISS_KEY, targetVersion);
                banner.style.display = 'none';
            });
        }
    })();
})();
