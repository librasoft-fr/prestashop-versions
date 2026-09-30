<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Service\Mcp;

use NeuronAI\MCP\McpException;
use NeuronAI\MCP\McpTransportInterface;

/**
 * MCP HTTP transport using raw curl (bypasses Guzzle to avoid version
 * conflicts with other modules' shipped Guzzle copies). Forces DNS
 * resolution of the shop host to 127.0.0.1 so server-to-server calls stay
 * on the loopback interface — while keeping the public Host so PrestaShop
 * URL routing matches.
 */
class LoopbackHttpTransport implements McpTransportInterface
{
    /** @var array<string, mixed> */
    protected array $config;
    protected ?string $sessionId = null;
    /** @var array{status:int, headers:array<string,string>, body:string}|null */
    protected ?array $lastResponse = null;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function connect(): void
    {
        if (!isset($this->config['url'])) {
            throw new McpException('URL is required for HTTP transport');
        }
        if (!filter_var($this->config['url'], \FILTER_VALIDATE_URL)) {
            throw new McpException('Invalid URL format');
        }

        // On resume after an interrupt, McpConnector rebuilds the client and
        // thus calls connect() + initialize() afresh. If this transport was
        // unserialized with a stale sessionId, that id leaks into the new
        // initialize request via the Mcp-Session-Id header — the server
        // doesn't recognize it and replies with an error (id=null), which
        // McpClient surfaces as "Invalid response ID". Clear it so init is
        // truly fresh.
        $this->sessionId = null;
        $this->lastResponse = null;
    }

    public function send(array $data): void
    {
        $url = (string) $this->config['url'];
        $timeout = (int) ($this->config['timeout'] ?? 10);

        $headers = [
            'Accept: application/json, text/event-stream',
            'Content-Type: application/json',
            'User-Agent: ps_ask_ai/1.0 (loopback)',
        ];
        if (isset($this->config['token'])) {
            $headers[] = 'Authorization: Bearer ' . $this->config['token'];
        }
        if ($this->sessionId !== null) {
            $headers[] = 'Mcp-Session-Id: ' . $this->sessionId;
        }

        try {
            $payload = json_encode($data, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new McpException('Failed to encode JSON: ' . $e->getMessage(), 0, $e);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            \CURLOPT_URL => $url,
            \CURLOPT_POST => true,
            \CURLOPT_POSTFIELDS => $payload,
            \CURLOPT_HTTPHEADER => $headers,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HEADER => true,
            \CURLOPT_FOLLOWLOCATION => false,
            \CURLOPT_TIMEOUT => $timeout,
            \CURLOPT_CONNECTTIMEOUT => $timeout,
        ]);

        $resolve = $this->config['resolve'] ?? [];
        if ($resolve !== []) {
            curl_setopt($ch, \CURLOPT_RESOLVE, $resolve);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new McpException('HTTP request failed: ' . $err);
        }

        $status = curl_getinfo($ch, \CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($ch, \CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr((string) $raw, 0, $headerSize);
        $body = substr((string) $raw, $headerSize);

        if ($status === 401) {
            throw new McpException('Authentication failed: Invalid or expired token');
        }
        if ($status === 403) {
            throw new McpException('Authorization failed: Insufficient permissions');
        }

        $parsedHeaders = $this->parseHeaders($rawHeaders);
        if (isset($parsedHeaders['mcp-session-id'])) {
            $this->sessionId = $parsedHeaders['mcp-session-id'];
        }

        $this->lastResponse = [
            'status' => (int) $status,
            'headers' => $parsedHeaders,
            'body' => $body,
        ];
    }

    public function receive(): array
    {
        if ($this->lastResponse === null) {
            throw new McpException('No response available. Call send() first.');
        }

        $body = $this->lastResponse['body'];
        $this->lastResponse = null;

        if ($body === '') {
            throw new McpException('Empty response body');
        }

        try {
            return json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $json = $this->parseSSEResponse($body);
            try {
                return json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new McpException('Invalid JSON response: ' . $e->getMessage(), 0, $e);
            }
        }
    }

    public function disconnect(): void
    {
        $this->sessionId = null;
        $this->lastResponse = null;
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(string $raw): array
    {
        $out = [];
        foreach (explode("\r\n", $raw) as $line) {
            $pos = strpos($line, ':');
            if ($pos === false) {
                continue;
            }
            $out[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
        }

        return $out;
    }

    private function parseSSEResponse(string $sse): string
    {
        // The MCP server may send server-initiated notifications (e.g.
        // notifications/tools/list_changed) as SSE events BEFORE the actual
        // JSON-RPC response to the client's request. Those notifications have
        // no "id" field — only responses do. We must not return a notification
        // to McpClient::receive(), which expects the response and would crash
        // accessing $response['id'] on a notification payload.
        //
        // Strategy: collect all data: lines, return the last one. The ps_mcp_server
        // always emits pending notifications first, then the response — so the
        // last data: line is always the JSON-RPC response to the current request.
        $last = null;
        foreach (explode("\n", $sse) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, ':')) {
                continue;
            }
            if (str_starts_with($line, 'data: ')) {
                $last = substr($line, 6);
            }
        }
        if ($last !== null) {
            return $last;
        }
        throw new McpException('No JSON data found in SSE response');
    }
}
