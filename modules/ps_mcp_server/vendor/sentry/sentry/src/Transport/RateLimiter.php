<?php

declare(strict_types=1);

namespace Sentry\Transport;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sentry\EventType;
use Sentry\HttpClient\Response;

final class RateLimiter
{
    
    public const DATA_CATEGORY_PROFILE = 'profile';

    
    private const DATA_CATEGORY_ERROR = 'error';

    
    private const DATA_CATEGORY_LOG_ITEM = 'log_item';

    
    private const DATA_CATEGORY_CHECK_IN = 'monitor';

    
    private const RATE_LIMITS_HEADER = 'X-Sentry-Rate-Limits';

    
    private const RETRY_AFTER_HEADER = 'Retry-After';

    
    private const DEFAULT_RETRY_AFTER_SECONDS = 60;

    
    private $rateLimits = [];

    
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function handleResponse(Response $response): bool
    {
        $now = time();

        if ($response->hasHeader(self::RATE_LIMITS_HEADER)) {
            foreach (explode(',', $response->getHeaderLine(self::RATE_LIMITS_HEADER)) as $limit) {
                
                $parameters = explode(':', $limit, 5);

                $retryAfter = $now + (ctype_digit($parameters[0]) ? (int) $parameters[0] : self::DEFAULT_RETRY_AFTER_SECONDS);

                foreach (explode(';', $parameters[1]) as $category) {
                    $this->rateLimits[$category ?: 'all'] = $retryAfter;

                    $this->logger->warning(
                        \sprintf('Rate limited exceeded for category "%s", backing off until "%s".', $category, gmdate(\DATE_ATOM, $retryAfter))
                    );
                }
            }

            return $this->rateLimits !== [];
        }

        if ($response->hasHeader(self::RETRY_AFTER_HEADER)) {
            $retryAfter = $now + $this->parseRetryAfterHeader($now, $response->getHeaderLine(self::RETRY_AFTER_HEADER));

            $this->rateLimits['all'] = $retryAfter;

            $this->logger->warning(
                \sprintf('Rate limited exceeded for all categories, backing off until "%s".', gmdate(\DATE_ATOM, $retryAfter))
            );

            return true;
        }

        return false;
    }

    
    public function isRateLimited($eventType): bool
    {
        return $this->getDisabledUntil($eventType) > time();
    }

    
    public function getDisabledUntil($eventType): int
    {
        $eventType = $eventType instanceof EventType ? (string) $eventType : $eventType;

        if ($eventType === 'event') {
            $eventType = self::DATA_CATEGORY_ERROR;
        } elseif ($eventType === 'log') {
            $eventType = self::DATA_CATEGORY_LOG_ITEM;
        } elseif ($eventType === 'check_in') {
            $eventType = self::DATA_CATEGORY_CHECK_IN;
        }

        return max($this->rateLimits['all'] ?? 0, $this->rateLimits[$eventType] ?? 0);
    }

    private function parseRetryAfterHeader(int $currentTime, string $header): int
    {
        if (preg_match('/^\d+$/', $header) === 1) {
            return (int) $header;
        }

        $headerDate = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::RFC1123, $header);

        if ($headerDate !== false && $headerDate->getTimestamp() >= $currentTime) {
            return $headerDate->getTimestamp() - $currentTime;
        }

        return self::DEFAULT_RETRY_AFTER_SECONDS;
    }
}
