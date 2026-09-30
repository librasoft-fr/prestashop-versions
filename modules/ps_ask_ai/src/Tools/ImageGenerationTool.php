<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of PrestaShop AskAI.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use Psr\Http\Message\ResponseInterface;

/**
 * Local tool that generates images via Gemini Imagen or OpenAI DALL-E 3,
 * saves the result to the shop's tmp image directory, and returns a public URL.
 *
 * All constructor arguments are plain strings so the class survives PHP's
 * serialize() when neuron-ai persists a WorkflowInterrupt to FilePersistence.
 */
class ImageGenerationTool extends Tool
{
    public const NAME = 'generate_image';

    private const GEMINI_IMAGE_MODEL = 'imagen-4.0-generate-001';
    private const OPENAI_IMAGE_MODEL = 'dall-e-3';

    /** HTTP status codes worth retrying — transient provider capacity / rate limits. */
    private const RETRYABLE_STATUS = [429, 500, 502, 503, 504];
    private const MAX_RETRIES = 3;
    private const RETRY_BASE_DELAY_MS = 1000;

    public function __construct(
        private readonly string $provider,
        private readonly string $apiKey,
        private readonly string $saveDir,
        private readonly string $shopBaseUrl,
    ) {
        parent::__construct(
            name: 'generate_image',
            description: 'Generate an image from a text description. Returns a public URL to the generated image. Use this tool when the user explicitly wants to create a visual or needs an image for a product.',
            properties: [
                ToolProperty::make('prompt', PropertyType::STRING, 'Detailed visual description of the image to generate.', true),
                ToolProperty::make('aspect_ratio', PropertyType::STRING, 'Desired aspect ratio: "square" (1:1, default), "landscape" (16:9), or "portrait" (9:16).'),
            ]
        );
    }

    public function __invoke(string $prompt, ?string $aspect_ratio = 'square'): string
    {
        $ratio = $aspect_ratio ?? 'square';

        try {
            [$base64, $mimeType] = match ($this->provider) {
                'gemini' => $this->callGeminiImagen($prompt, $ratio),
                'openai' => $this->callOpenAiDalle($prompt, $ratio),
                default => throw new \RuntimeException(sprintf('Image generation not supported for provider: %s', $this->provider)),
            };
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Image generation API call failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->saveAndGetUrl($base64, $mimeType);
    }

    /**
     * @return array{string, string} [base64, mimeType]
     *
     * @throws GuzzleException
     */
    private function callGeminiImagen(string $prompt, string $aspectRatio): array
    {
        $geminiRatio = match ($aspectRatio) {
            'landscape' => '16:9',
            'portrait' => '9:16',
            default => '1:1',
        };

        $client = new Client(['timeout' => 90.0]);
        $response = $this->requestWithRetry(fn (): ResponseInterface => $client->post(
            sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:predict?key=%s',
                self::GEMINI_IMAGE_MODEL,
                $this->apiKey
            ),
            [
                RequestOptions::JSON => [
                    'instances' => [['prompt' => $prompt]],
                    'parameters' => ['sampleCount' => 1, 'aspectRatio' => $geminiRatio],
                ],
            ]
        ));

        /** @var array<string, mixed> $data */
        $data = json_decode((string) $response->getBody(), true);
        $prediction = $data['predictions'][0] ?? null;
        if (!is_array($prediction) || empty($prediction['bytesBase64Encoded'])) {
            throw new \RuntimeException('Gemini Imagen returned no image data.');
        }

        return [(string) $prediction['bytesBase64Encoded'], (string) ($prediction['mimeType'] ?? 'image/png')];
    }

    /**
     * @return array{string, string} [base64, mimeType]
     *
     * @throws GuzzleException
     */
    private function callOpenAiDalle(string $prompt, string $aspectRatio): array
    {
        $size = match ($aspectRatio) {
            'landscape' => '1792x1024',
            'portrait' => '1024x1792',
            default => '1024x1024',
        };

        $client = new Client(['timeout' => 90.0]);
        $response = $this->requestWithRetry(fn (): ResponseInterface => $client->post('https://api.openai.com/v1/images/generations', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $this->apiKey],
            RequestOptions::JSON => [
                'model' => self::OPENAI_IMAGE_MODEL,
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'response_format' => 'b64_json',
            ],
        ]));

        /** @var array<string, mixed> $data */
        $data = json_decode((string) $response->getBody(), true);
        $item = $data['data'][0] ?? null;
        if (!is_array($item) || empty($item['b64_json'])) {
            throw new \RuntimeException('OpenAI DALL-E returned no image data.');
        }

        return [(string) $item['b64_json'], 'image/png'];
    }

    /**
     * Run a Guzzle request, retrying on transient provider errors (429/5xx)
     * with exponential backoff. Non-retryable errors and the final attempt
     * rethrow the original GuzzleException.
     *
     * @param callable(): ResponseInterface $request
     *
     * @throws GuzzleException
     */
    private function requestWithRetry(callable $request): ResponseInterface
    {
        $attempt = 0;
        while (true) {
            try {
                return $request();
            } catch (BadResponseException $e) {
                $status = $e->getResponse()->getStatusCode();
                if (!in_array($status, self::RETRYABLE_STATUS, true) || $attempt >= self::MAX_RETRIES) {
                    throw $e;
                }
                // Exponential backoff: 1s, 2s, 4s.
                usleep(self::RETRY_BASE_DELAY_MS * (2 ** $attempt) * 1000);
                ++$attempt;
            }
        }
    }

    private function saveAndGetUrl(string $base64, string $mimeType): string
    {
        if (!is_dir($this->saveDir) && !@mkdir($this->saveDir, 0775, true) && !is_dir($this->saveDir)) {
            throw new \RuntimeException(sprintf('Cannot create image directory: %s', $this->saveDir));
        }

        // Purge images older than 30 days (best-effort, same lifecycle as user uploads)
        foreach ((array) glob($this->saveDir . '*') as $old) {
            if (is_file((string) $old) && time() - filemtime((string) $old) > 86400 * 30) {
                @unlink((string) $old);
            }
        }

        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'png',
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $imageBytes = base64_decode($base64, true);
        if ($imageBytes === false) {
            throw new \RuntimeException('Failed to decode base64 image data from provider.');
        }
        if (file_put_contents($this->saveDir . $filename, $imageBytes) === false) {
            throw new \RuntimeException(sprintf('Cannot write image file: %s', $this->saveDir . $filename));
        }

        return $this->shopBaseUrl . '/img/tmp/ps_ask_ai/' . $filename;
    }
}
