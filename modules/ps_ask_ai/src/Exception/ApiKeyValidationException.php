<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of PrestaShop AskAI.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Exception;

/**
 * Thrown when a provider API-key probe fails. Carries a machine-readable
 * $errorType so analytics (Segment "AskAI Config Save Failed") can report a
 * stable category instead of a free-text message.
 */
class ApiKeyValidationException extends \RuntimeException
{
    public const ERROR_INVALID_API_KEY = 'invalid_api_key';
    public const ERROR_QUOTA_EXCEEDED = 'quota_exceeded';
    public const ERROR_TIMEOUT = 'timeout';
    public const ERROR_UNKNOWN = 'unknown';

    public function __construct(
        string $message,
        public readonly string $errorType = self::ERROR_UNKNOWN,
    ) {
        parent::__construct($message);
    }

    /**
     * Map an HTTP probe outcome to an error type.
     *
     * @param int $status HTTP status code (0 when no response)
     * @param bool $connectFailed true when the request never reached the provider
     */
    public static function fromProbe(int $status, bool $connectFailed): string
    {
        if ($connectFailed || $status === 0) {
            return self::ERROR_TIMEOUT;
        }
        if ($status === 401 || $status === 403) {
            return self::ERROR_INVALID_API_KEY;
        }
        if ($status === 429) {
            return self::ERROR_QUOTA_EXCEEDED;
        }

        return self::ERROR_UNKNOWN;
    }
}
