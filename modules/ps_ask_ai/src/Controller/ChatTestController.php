<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Controller;

use PrestaShop\Module\PsAskAi\Config\PsAskAiConfig;
use PrestaShop\Module\PsAskAi\Service\ConversationService;
use PrestaShop\Module\PsAskAi\Service\NeuronAiService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChatTestController extends FrameworkBundleAdminController
{
    public function __construct(
        private readonly NeuronAiService $neuronAiService,
        private readonly ConversationService $conversationService,
    ) {
    }

    /**
     * DEV/TEST ONLY. Persist an unanswered user message to a thread (creating
     * the thread if none is given) so the sandbox can reproduce the "orphan
     * trailing turn" state on demand and exercise healing / prompt folding.
     * Runs no LLM turn. Gated behind _PS_MODE_DEV_ so it is inert in prod.
     */
    public function orphanAction(Request $request): JsonResponse
    {
        if (!defined('_PS_MODE_DEV_') || !_PS_MODE_DEV_) {
            return new JsonResponse(['error' => 'Not available outside dev mode.'], 403);
        }

        $prompt = trim((string) $request->request->get('prompt', ''));
        if ($prompt === '') {
            return new JsonResponse(['error' => 'Empty prompt'], 400);
        }

        $employee = \Context::getContext()->employee ?? null;
        $shop = \Context::getContext()->shop ?? null;
        if ($employee === null || $shop === null) {
            return new JsonResponse(['error' => 'No employee/shop context.'], 403);
        }

        $requestedThreadId = trim((string) $request->request->get('thread', ''));
        $conversation = $requestedThreadId !== ''
            ? $this->conversationService->getByThreadId($requestedThreadId)
            : $this->conversationService->create((int) $employee->id, (int) $shop->id);

        if ($conversation === null
            || $conversation->getIdEmployee() !== (int) $employee->id
            || $conversation->getIdShop() !== (int) $shop->id
            || $conversation->getArchivedAt() !== null
        ) {
            return new JsonResponse(['error' => 'Conversation not found or not accessible.'], 404);
        }

        $this->neuronAiService->appendOrphanUserMessage($conversation->getThreadId(), $prompt);

        return new JsonResponse(['threadId' => $conversation->getThreadId()]);
    }

    public function indexAction(): Response
    {
        $streamUrl = $this->generateUrl('ps_ask_ai_stream');
        $resumeUrl = $this->generateUrl('ps_ask_ai_stream_resume');
        $historyUrl = $this->generateUrl('ps_ask_ai_history');

        // DEV/TEST ONLY orphan-injection lever. Only wire the URL + button when
        // dev mode is on, so the affordance is absent in prod.
        $isDev = defined('_PS_MODE_DEV_') && _PS_MODE_DEV_;
        $orphanUrl = $isDev ? $this->generateUrl('ps_ask_ai_chat_test_orphan') : '';
        $orphanButton = $isDev
            ? '<button id="simulateOrphan" type="button" title="DEV: persist the current text as an unanswered user message (no turn), then reload to test orphan resume" style="font:inherit;font-size:12px;padding:3px 8px;border:1px solid #e0b400;border-radius:4px;background:#fff8e1;color:#6b5300;cursor:pointer;">Simulate orphan</button>'
            : '';

        $activeProvider = (string) \Configuration::get(PsAskAiConfig::CONFIG_ACTIVE_PROVIDER);
        $providerFilter = $activeProvider !== '' ? $activeProvider : null;

        // Models hidden from the test chat only (still available in the BO drawer).
        $hiddenModelKeys = ['claude-haiku-4-5'];

        // getAvailableModels/getDefaultModelKey require a provider and do a live
        // (file-cached) intersection; with no provider configured there are no
        // models to offer.
        $storedKey = $activeProvider !== '' ? ((string) \Configuration::get(PsAskAiConfig::CONFIG_API_KEY) ?: null) : null;
        $defaultModelKey = $providerFilter !== null
            ? $this->neuronAiService->getDefaultModelKey($providerFilter, $storedKey)
            : null;
        $availableModels = $providerFilter !== null
            ? $this->neuronAiService->getAvailableModels($providerFilter, $storedKey)
            : [];
        $modelOptions = '';
        foreach ($availableModels as $key => $config) {
            if (in_array($key, $hiddenModelKeys, true)) {
                continue;
            }
            $modelOptions .= sprintf(
                '<option value="%s" data-provider="%s"%s>%s</option>',
                htmlspecialchars($key, ENT_QUOTES),
                htmlspecialchars($config['provider'], ENT_QUOTES),
                $key === $defaultModelKey ? ' selected' : '',
                htmlspecialchars($config['label'], ENT_QUOTES)
            );
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ps_ask_ai - Chat</title>
    <style>
        :root { color-scheme: light; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 0; background: #f7f8fa; color: #222; }
        .app { max-width: 760px; margin: 16px auto; height: calc(100vh - 32px); display: flex; flex-direction: column; background: #fff; border: 1px solid #e1e4e8; border-radius: 8px; overflow: hidden; }
        header { padding: 12px 16px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 8px; }
        header h1 { font-size: 16px; margin: 0; }
        header select { font: inherit; font-size: 12px; padding: 3px 6px; border: 1px solid #ddd; border-radius: 4px; background: #fff; }
        header select:disabled { opacity: 0.6; cursor: not-allowed; }
        header input.key { font: inherit; font-size: 12px; padding: 3px 6px; border: 1px solid #ddd; border-radius: 4px; background: #fff; width: 160px; }
        header input.key:disabled { opacity: 0.6; cursor: not-allowed; }
        header .meta { font-size: 12px; color: #888; margin-left: auto; }
        #status { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; background: #eee; color: #555; }
        #status.thinking  { background: #fff3cd; color: #856404; }
        #status.answering { background: #d1ecf1; color: #0c5460; }
        #status.waiting   { background: #ffe5b4; color: #6b3e00; }
        #status.done      { background: #d4edda; color: #155724; }
        #status.error     { background: #f8d7da; color: #721c24; }
        .chat { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
        .msg { max-width: 75%; padding: 10px 14px; border-radius: 16px; white-space: pre-wrap; word-wrap: break-word; line-height: 1.4; }
        .msg.user      { align-self: flex-end; background: #0a84ff; color: #fff; border-bottom-right-radius: 4px; }
        .msg.assistant { align-self: flex-start; background: #f0f1f3; color: #222; border-bottom-left-radius: 4px; }
        .msg.assistant.thinking::after { content: '...'; display: inline-block; margin-left: 4px; opacity: 0.6; animation: blink 1.2s infinite; }
        @keyframes blink { 50% { opacity: 0.15; } }
        /* Markdown-rendered assistant content: drop pre-wrap (block elements
           carry their own spacing) and add minimal element styling. */
        .msg.assistant.markdown { white-space: normal; }
        .msg.assistant.markdown > :first-child { margin-top: 0; }
        .msg.assistant.markdown > :last-child { margin-bottom: 0; }
        .msg.assistant.markdown p { margin: 0 0 8px; }
        .msg.assistant.markdown ul, .msg.assistant.markdown ol { margin: 0 0 8px; padding-left: 20px; }
        .msg.assistant.markdown li { margin: 2px 0; }
        .msg.assistant.markdown pre { background: #e6e8eb; padding: 8px 10px; border-radius: 6px; overflow-x: auto; margin: 0 0 8px; }
        .msg.assistant.markdown code { background: #e6e8eb; padding: 1px 4px; border-radius: 4px; font-size: 12px; }
        .msg.assistant.markdown pre code { background: none; padding: 0; }
        .msg.assistant.markdown table { border-collapse: collapse; margin: 0 0 8px; }
        .msg.assistant.markdown th, .msg.assistant.markdown td { border: 1px solid #d6d9dd; padding: 4px 8px; text-align: left; }
        .msg.assistant.markdown a { color: #0a84ff; }
        .msg.assistant.markdown h1, .msg.assistant.markdown h2, .msg.assistant.markdown h3 { margin: 8px 0 6px; font-size: 15px; }
        .msg.thought { align-self: flex-start; max-width: 75%; background: #f6f4ee; border: 1px dashed #d6cfb8; border-radius: 12px; padding: 8px 12px; font-style: italic; color: #6b5d3c; font-size: 13px; white-space: pre-wrap; word-wrap: break-word; }
        .msg.thought .label { display: block; font-weight: 600; font-style: normal; font-size: 11px; color: #8c794c; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .sys { align-self: center; font-size: 12px; color: #888; padding: 2px 8px; }
        .sys.error { color: #b00020; }
        .approval { align-self: stretch; max-width: 100%; background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; padding: 12px; font-size: 13px; color: #333; }
        .approval h3 { margin: 0 0 8px; font-size: 13px; color: #6b3e00; }
        .approval .action { margin: 6px 0; padding: 8px; background: #fff; border: 1px solid #eee; border-radius: 6px; }
        .approval .action .name { font-weight: 600; }
        .approval .action pre { background: #f6f7f9; padding: 6px; margin: 4px 0; font-size: 11px; max-height: 100px; overflow: auto; border-radius: 4px; }
        .approval .action label { display: inline-block; margin-right: 12px; }
        .approval .action input[type=text] { width: 100%; box-sizing: border-box; margin-top: 4px; padding: 4px 6px; font: inherit; font-size: 12px; }
        .approval button { margin-top: 6px; padding: 6px 12px; font-size: 13px; background: #0a84ff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .approval button:disabled { opacity: 0.5; cursor: not-allowed; }
        footer { border-top: 1px solid #eee; padding: 8px; display: flex; flex-direction: column; gap: 6px; }
        footer .row { display: flex; gap: 8px; align-items: flex-end; }
        footer textarea { flex: 1; resize: none; min-height: 36px; max-height: 140px; padding: 8px; border: 1px solid #ddd; border-radius: 18px; font: inherit; line-height: 1.4; }
        footer textarea:focus { outline: none; border-color: #0a84ff; }
        footer button { padding: 8px 16px; background: #0a84ff; color: #fff; border: none; border-radius: 18px; cursor: pointer; font-weight: 500; }
        footer button:disabled { opacity: 0.5; cursor: not-allowed; }
        footer .opts { font-size: 12px; color: #666; padding: 0 8px; }
        footer .opts label { cursor: pointer; user-select: none; }
        footer .opts label.disabled { opacity: 0.4; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="app">
        <header>
            <h1>ps_ask_ai chat</h1>
            <select id="model" title="model">
                {$modelOptions}
            </select>
            <input id="apikey" class="key" type="password" autocomplete="off" placeholder="API key (optional)" title="overrides the env-configured key for this thread; leave blank to use the server's env">
            <span class="meta" id="threadLabel"></span>
            <button id="newThread" type="button" title="start a fresh thread" style="font:inherit;font-size:12px;padding:3px 8px;border:1px solid #ddd;border-radius:4px;background:#fff;cursor:pointer;">New</button>
            {$orphanButton}
            <span id="status">idle</span>
        </header>
        <div class="chat" id="chat"></div>
        <footer>
            <div class="row">
                <textarea id="p" placeholder="Type a message... (enter to send, shift+enter for newline)" rows="1"></textarea>
                <button id="send">Send</button>
            </div>
            <div class="opts">
                <label id="urlctxLabel"><input type="checkbox" id="urlctx"> follow URLs in this message (uses url_context — Gemini only, disables shop tools for this turn)</label>
            </div>
        </footer>
    </div>

    <!-- Same Markdown renderer the real Vue UI uses (markdown-it), so assistant
         replies render identically here. Default options → source HTML escaped. -->
    <script src="https://cdn.jsdelivr.net/npm/markdown-it@14.1.0/dist/markdown-it.min.js"></script>
    <script>
    (function () {
        const STREAM_URL = '{$streamUrl}';
        const RESUME_URL = '{$resumeUrl}';
        const HISTORY_URL = '{$historyUrl}';
        const ORPHAN_URL = '{$orphanUrl}';
        const THREAD_KEY = 'ps_ask_ai_demo_thread';

        // The server creates a fresh conversation on the first turn (when no
        // `thread` param is sent) and emits the id back via the `thread` SSE
        // event. We store it here, persist it to localStorage so a reload
        // resumes the same thread, and reuse it on subsequent turns.
        let threadId = null;

        const chatEl = document.getElementById('chat');
        const promptEl = document.getElementById('p');
        const sendBtn = document.getElementById('send');
        const statusEl = document.getElementById('status');
        const urlCtxEl = document.getElementById('urlctx');
        const urlCtxLabelEl = document.getElementById('urlctxLabel');
        const modelEl = document.getElementById('model');
        const apiKeyEl = document.getElementById('apikey');
        const threadLabelEl = document.getElementById('threadLabel');
        threadLabelEl.textContent = 'thread: (pending)';

        // markdown-it, same as the real Vue UI (new MarkdownIt() with defaults).
        // Null if the CDN script failed to load — we then fall back to plain text.
        const md = (typeof window.markdownit === 'function') ? window.markdownit() : null;

        // Render markdown into an assistant bubble (or plain text as a fallback).
        function renderAssistant(el, rawText) {
            if (md) {
                el.classList.add('markdown');
                el.innerHTML = md.render(rawText);
            } else {
                el.textContent = rawText;
            }
        }

        // url_context is a Gemini-only provider tool — disable the checkbox
        // when the selected model uses another provider.
        function applyModelConstraints() {
            const opt = modelEl.options[modelEl.selectedIndex];
            const provider = opt ? opt.dataset.provider : '';
            const supportsUrlCtx = provider === 'gemini';
            urlCtxEl.disabled = !supportsUrlCtx;
            if (!supportsUrlCtx) urlCtxEl.checked = false;
            urlCtxLabelEl.classList.toggle('disabled', !supportsUrlCtx);
        }
        modelEl.addEventListener('change', applyModelConstraints);
        applyModelConstraints();

        const state = {
            source: null,
            assistantEl: null,    // current assistant bubble being filled
            thoughtEl: null,      // current "thinking" bubble (reasoning content)
            thoughtBodyEl: null,  // the text node we append reasoning chunks into
            approvalEl: null,     // current approval block waiting on user
            assistantRaw: '',     // accumulated raw markdown of the current assistant bubble
            interactive: true,
            lastMetaSig: null,    // signature of last-rendered tool list (skip re-rendering identical ones)
            modelLocked: false,   // once a turn has been sent, model is frozen for the rest of this thread
        };

        function setStatus(s, label) {
            statusEl.className = s;
            statusEl.textContent = label;
        }

        function scrollDown() {
            chatEl.scrollTop = chatEl.scrollHeight;
        }

        function addUser(text) {
            const el = document.createElement('div');
            el.className = 'msg user';
            el.textContent = text;
            chatEl.appendChild(el);
            scrollDown();
        }

        function ensureAssistantBubble() {
            if (state.assistantEl) return state.assistantEl;
            const el = document.createElement('div');
            el.className = 'msg assistant thinking';
            el.textContent = '';
            chatEl.appendChild(el);
            state.assistantEl = el;
            state.assistantRaw = '';
            scrollDown();
            return el;
        }

        function ensureThoughtBubble() {
            if (state.thoughtEl) return state.thoughtBodyEl;
            const el = document.createElement('div');
            el.className = 'msg thought';
            const label = document.createElement('span');
            label.className = 'label';
            label.textContent = 'thinking';
            const body = document.createElement('span');
            body.className = 'body';
            el.appendChild(label);
            el.appendChild(body);
            chatEl.appendChild(el);
            state.thoughtEl = el;
            state.thoughtBodyEl = body;
            scrollDown();
            return body;
        }

        function appendToThought(text) {
            const body = ensureThoughtBubble();
            body.textContent += text;
            scrollDown();
        }

        function appendToAssistant(text) {
            const el = ensureAssistantBubble();
            el.classList.remove('thinking');
            // Accumulate the raw markdown and re-render the whole bubble each
            // chunk (same as the Vue UI re-rendering md.render(message.text)).
            state.assistantRaw += text;
            renderAssistant(el, state.assistantRaw);
            scrollDown();
        }

        // A finished assistant bubble (no streaming animation) — used when
        // hydrating a thread's history on reload.
        function addAssistant(text) {
            const el = document.createElement('div');
            el.className = 'msg assistant';
            renderAssistant(el, text);
            chatEl.appendChild(el);
            scrollDown();
        }

        function addSystem(text, className) {
            const el = document.createElement('div');
            el.className = 'sys' + (className ? ' ' + className : '');
            el.textContent = text;
            chatEl.appendChild(el);
            scrollDown();
        }

        function closeStream() {
            if (state.source) { state.source.close(); state.source = null; }
        }

        function setSending(sending) {
            sendBtn.disabled = sending;
            promptEl.disabled = sending;
            // Lock the model selector while a turn is in flight, and keep
            // it locked once the conversation has started — switching
            // providers mid-thread mixes message formats (reasoning blocks,
            // provider tools) and breaks history replay. The API key follows
            // the same lock since it's tied to the chosen provider.
            modelEl.disabled = sending || state.modelLocked;
            apiKeyEl.disabled = sending || state.modelLocked;
            if (!sending) promptEl.focus();
        }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }

        function attachListeners(src) {
            src.addEventListener('thread', (ev) => {
                try {
                    const data = JSON.parse(ev.data) || {};
                    if (data.threadId) {
                        threadId = data.threadId;
                        try { localStorage.setItem(THREAD_KEY, threadId); } catch (_) {}
                        threadLabelEl.textContent = 'thread: ' + String(threadId).slice(0, 8);
                    }
                } catch (_) {}
            });

            src.addEventListener('meta', (ev) => {
                const data = JSON.parse(ev.data);
                const tools = (data && Array.isArray(data.tools)) ? data.tools : [];
                const signature = tools.join(',');
                // Only render when the tool set changes between turns —
                // avoids spamming the chat with the same line on every reply.
                if (signature === state.lastMetaSig) return;
                state.lastMetaSig = signature;
                if (tools.length) {
                    addSystem('~ tools available: ' + tools.join(', '));
                } else {
                    addSystem('~ no tools available');
                }
            });

            src.addEventListener('reasoning', (ev) => {
                setStatus('thinking', 'thinking');
                let content = '';
                try { content = (JSON.parse(ev.data) || {}).content || ''; } catch (_) {}
                if (content) appendToThought(content);
            });

            src.addEventListener('text', (ev) => {
                const data = JSON.parse(ev.data);
                if (statusEl.className !== 'answering') setStatus('answering', 'answering');
                appendToAssistant(data.content);
            });

            src.addEventListener('tool-call', (ev) => {
                const data = JSON.parse(ev.data);
                addSystem('> calling ' + data.name);
            });

            src.addEventListener('tool-result', (ev) => {
                const data = JSON.parse(ev.data);
                addSystem('< result from ' + data.name);
            });

            src.addEventListener('approval-needed', (ev) => {
                const data = JSON.parse(ev.data);
                closeStream();
                renderApproval(data.workflowId, data.actions || []);
                setStatus('waiting', 'awaiting approval');
            });

            src.addEventListener('error-event', (ev) => {
                const data = JSON.parse(ev.data);
                addSystem('error: ' + (data.message || 'unknown'), 'error');
                finish('error', 'error');
            });

            src.addEventListener('done', () => {
                // Don't finalize if we paused for approval — the approval flow
                // will resume into the same assistant bubble.
                if (statusEl.className === 'waiting') return;
                finish('done', 'done');
            });

            src.onerror = () => {
                if (!src || src.readyState === EventSource.CLOSED) return;
                if (statusEl.className === 'waiting') return; // expected close after approval-needed
                addSystem('connection error', 'error');
                finish('error', 'error');
            };
        }

        function finish(s, label) {
            closeStream();
            setStatus(s, label);
            // Mark the bubble as no longer thinking so the dots stop animating.
            if (state.assistantEl) state.assistantEl.classList.remove('thinking');
            state.assistantEl = null; // next user message starts a new bubble
            state.assistantRaw = '';
            state.thoughtEl = null;   // next turn gets its own thinking bubble
            state.thoughtBodyEl = null;
            setSending(false);
        }

        function renderApproval(workflowId, actions) {
            const box = document.createElement('div');
            box.className = 'msg approval';
            box.innerHTML = '<h3>The assistant wants to call ' + actions.length + ' tool(s). Approve?</h3>';

            const form = document.createElement('form');
            actions.forEach((action, i) => {
                const row = document.createElement('div');
                row.className = 'action';
                row.innerHTML = ''
                    + '<div class="name">' + escapeHtml(action.name) + '</div>'
                    + '<pre>' + escapeHtml(action.description || '(no arguments)') + '</pre>'
                    + '<label><input type="radio" name="dec_' + i + '" value="approve" checked> approve</label>'
                    + '<label><input type="radio" name="dec_' + i + '" value="reject"> reject</label>'
                    + '<input type="text" name="fb_' + i + '" placeholder="reason if rejected (optional)">';
                form.appendChild(row);
            });
            const submit = document.createElement('button');
            submit.type = 'submit';
            submit.textContent = 'Submit decisions';
            form.appendChild(submit);
            box.appendChild(form);
            chatEl.appendChild(box);
            state.approvalEl = box;
            scrollDown();

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const params = new URLSearchParams();
                params.set('workflowId', workflowId);
                if (threadId) params.set('thread', threadId);
                params.set('model', modelEl.value);
                if (apiKeyEl.value) params.set('api_key', apiKeyEl.value);
                const summary = [];
                actions.forEach((action, i) => {
                    const dec = form.querySelector('input[name="dec_' + i + '"]:checked').value;
                    const fb = form.querySelector('input[name="fb_' + i + '"]').value;
                    params.append('decisions[' + i + '][id]', action.id);
                    params.append('decisions[' + i + '][decision]', dec);
                    if (fb) params.append('decisions[' + i + '][feedback]', fb);
                    summary.push(action.name + ': ' + dec);
                });

                // Collapse the approval block into a short summary so the chat
                // stays readable and we know decisions were submitted.
                box.innerHTML = '<div class="sys">decisions: ' + escapeHtml(summary.join(' | ')) + '</div>';
                state.approvalEl = null;
                setStatus('thinking', 'thinking');
                openSource(RESUME_URL, params);
            });
        }

        function openSource(baseUrl, params) {
            const u = new URL(baseUrl, window.location.origin);
            if (params instanceof URLSearchParams) {
                params.forEach((v, k) => u.searchParams.append(k, v));
            }
            state.source = new EventSource(u.toString());
            attachListeners(state.source);
        }

        function send() {
            const prompt = promptEl.value.trim();
            if (!prompt) return;
            const useUrlCtx = urlCtxEl.checked;
            promptEl.value = '';
            addUser(prompt);
            if (useUrlCtx) {
                addSystem('(this turn uses url_context — shop tools disabled)');
                urlCtxEl.checked = false; // one-shot
            }
            state.modelLocked = true;
            modelEl.title = 'model locked — refresh the page to start a new thread with a different model';
            setSending(true);
            setStatus('thinking', 'thinking');

            const params = new URLSearchParams();
            params.set('prompt', prompt);
            if (threadId) params.set('thread', threadId);
            params.set('model', modelEl.value);
            if (apiKeyEl.value) params.set('api_key', apiKeyEl.value);
            if (useUrlCtx) params.set('url_context', '1');
            openSource(STREAM_URL, params);
        }

        // Re-run an unanswered prompt without adding a second user bubble (the
        // bubble is already on screen from hydration). The backend drops the
        // orphan and dedups when the resent text matches, so no duplicate user
        // message reaches the thread.
        function resumePending(text) {
            state.modelLocked = true;
            modelEl.title = 'model locked — use New to start a new thread with a different model';
            setSending(true);
            setStatus('thinking', 'thinking');

            const params = new URLSearchParams();
            params.set('prompt', text);
            if (threadId) params.set('thread', threadId);
            params.set('model', modelEl.value);
            if (apiKeyEl.value) params.set('api_key', apiKeyEl.value);
            openSource(STREAM_URL, params);
        }

        // Rebuild the visible transcript from the server's history payload
        // ({ role, text, name? } entries; tool results are already filtered out
        // server-side).
        function hydrate(messages) {
            for (const m of messages) {
                if (m.role === 'user') {
                    addUser(m.text || '');
                } else if (m.role === 'assistant') {
                    if (m.name) addSystem('> called ' + m.name);
                    else if (m.text) addAssistant(m.text);
                }
            }
        }

        async function init() {
            let stored = null;
            try { stored = localStorage.getItem(THREAD_KEY); } catch (_) {}
            if (!stored) { promptEl.focus(); return; }

            threadId = stored;
            threadLabelEl.textContent = 'thread: ' + String(stored).slice(0, 8);

            let data = null;
            try {
                // HISTORY_URL already carries a Symfony ?_token=… — append via
                // the URL API, never string concat, or the token breaks.
                const u = new URL(HISTORY_URL, window.location.origin);
                u.searchParams.set('thread', stored);
                const res = await fetch(u.toString(), { credentials: 'same-origin' });
                if (res.ok) data = await res.json();
            } catch (_) {}

            if (!data || !Array.isArray(data.messages)) {
                // Thread gone or not accessible anymore — forget it, start fresh.
                try { localStorage.removeItem(THREAD_KEY); } catch (_) {}
                threadId = null;
                threadLabelEl.textContent = 'thread: (pending)';
                promptEl.focus();
                return;
            }

            hydrate(data.messages);

            // A loaded thread freezes the model, same as after the first send.
            state.modelLocked = true;
            modelEl.title = 'model locked — use New to start a new thread with a different model';

            const last = data.messages[data.messages.length - 1];
            if (last && last.role === 'user') {
                // Case 1: previous turn left a user message unanswered — resume
                // it automatically so the page reloads straight into thinking.
                addSystem('~ resuming unanswered message');
                resumePending(last.text || '');
            } else {
                setSending(false); // apply the model lock to the selectors
            }
        }

        sendBtn.addEventListener('click', send);
        promptEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtn.disabled) send();
            }
        });

        document.getElementById('newThread').addEventListener('click', () => {
            try { localStorage.removeItem(THREAD_KEY); } catch (_) {}
            window.location.reload();
        });

        // DEV: persist the current text as an unanswered user message and stop
        // (no turn). Reload afterwards to see the orphan auto-resume (Case 1),
        // or type a different message to see it folded in (Case 2).
        const simulateOrphanBtn = document.getElementById('simulateOrphan');
        if (simulateOrphanBtn) {
            simulateOrphanBtn.addEventListener('click', async () => {
                const text = promptEl.value.trim();
                if (!text) { promptEl.focus(); return; }
                simulateOrphanBtn.disabled = true;
                try {
                    // ORPHAN_URL already carries a Symfony ?_token=… — append via
                    // the URL API, never string concat, or the token breaks.
                    const u = new URL(ORPHAN_URL, window.location.origin);
                    const body = new URLSearchParams();
                    body.set('prompt', text);
                    if (threadId) body.set('thread', threadId);
                    const res = await fetch(u.toString(), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                    });
                    const data = res.ok ? await res.json() : null;
                    if (!data || !data.threadId) {
                        addSystem('simulate orphan failed (HTTP ' + res.status + ')', 'error');
                        return;
                    }
                    threadId = data.threadId;
                    try { localStorage.setItem(THREAD_KEY, threadId); } catch (_) {}
                    threadLabelEl.textContent = 'thread: ' + String(threadId).slice(0, 8);
                    promptEl.value = '';
                    addUser(text);
                    addSystem('~ orphan persisted — reload to auto-resume, or send another message to merge');
                    state.modelLocked = true;
                    setSending(false);
                } catch (e) {
                    addSystem('simulate orphan error: ' + (e && e.message ? e.message : 'unknown'), 'error');
                } finally {
                    simulateOrphanBtn.disabled = false;
                }
            });
        }

        init();
    })();
    </script>
</body>
</html>
HTML;

        return new Response($html);
    }
}
