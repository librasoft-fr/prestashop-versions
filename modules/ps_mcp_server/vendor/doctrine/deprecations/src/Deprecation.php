<?php

declare(strict_types=1);

namespace Doctrine\Deprecations;

use Psr\Log\LoggerInterface;

use function array_key_exists;
use function array_reduce;
use function assert;
use function debug_backtrace;
use function sprintf;
use function str_replace;
use function strpos;
use function strrpos;
use function substr;
use function trigger_error;

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const DIRECTORY_SEPARATOR;
use const E_USER_DEPRECATED;


class Deprecation
{
    private const TYPE_NONE               = 0;
    private const TYPE_TRACK_DEPRECATIONS = 1;
    private const TYPE_TRIGGER_ERROR      = 2;
    private const TYPE_PSR_LOGGER         = 4;

    
    private static $type;

    
    private static $logger;

    
    private static $ignoredPackages = [];

    
    private static $triggeredDeprecations = [];

    
    private static $ignoredLinks = [];

    
    private static $deduplication = true;

    
    public static function trigger(string $package, string $link, string $message, ...$args): void
    {
        $type = self::$type ?? self::getTypeFromEnv();

        if ($type === self::TYPE_NONE) {
            return;
        }

        if (isset(self::$ignoredLinks[$link])) {
            return;
        }

        if (array_key_exists($link, self::$triggeredDeprecations)) {
            self::$triggeredDeprecations[$link]++;
        } else {
            self::$triggeredDeprecations[$link] = 1;
        }

        if (self::$deduplication === true && self::$triggeredDeprecations[$link] > 1) {
            return;
        }

        if (isset(self::$ignoredPackages[$package])) {
            return;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $message = sprintf($message, ...$args);

        self::delegateTriggerToBackend($message, $backtrace, $link, $package);
    }

    
    public static function triggerIfCalledFromOutside(string $package, string $link, string $message, ...$args): void
    {
        $type = self::$type ?? self::getTypeFromEnv();

        if ($type === self::TYPE_NONE) {
            return;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        
        if (isset($backtrace[1]['file'], $backtrace[0]['file']) && strpos($backtrace[1]['file'], DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR) === false) {
            $path = DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $package) . DIRECTORY_SEPARATOR;

            if (strpos($backtrace[0]['file'], $path) === false) {
                return;
            }

            if (strpos($backtrace[1]['file'], $path) !== false) {
                return;
            }
        }

        if (isset(self::$ignoredLinks[$link])) {
            return;
        }

        if (array_key_exists($link, self::$triggeredDeprecations)) {
            self::$triggeredDeprecations[$link]++;
        } else {
            self::$triggeredDeprecations[$link] = 1;
        }

        if (self::$deduplication === true && self::$triggeredDeprecations[$link] > 1) {
            return;
        }

        if (isset(self::$ignoredPackages[$package])) {
            return;
        }

        $message = sprintf($message, ...$args);

        self::delegateTriggerToBackend($message, $backtrace, $link, $package);
    }

    
    private static function delegateTriggerToBackend(string $message, array $backtrace, string $link, string $package): void
    {
        $type = self::$type ?? self::getTypeFromEnv();

        if (($type & self::TYPE_PSR_LOGGER) > 0) {
            $context = [
                'file' => $backtrace[0]['file'] ?? null,
                'line' => $backtrace[0]['line'] ?? null,
                'package' => $package,
                'link' => $link,
            ];

            assert(self::$logger !== null);

            self::$logger->notice($message, $context);
        }

        if (! (($type & self::TYPE_TRIGGER_ERROR) > 0)) {
            return;
        }

        $message .= sprintf(
            ' (%s:%d called by %s:%d, %s, package %s)',
            self::basename($backtrace[0]['file'] ?? 'native code'),
            $backtrace[0]['line'] ?? 0,
            self::basename($backtrace[1]['file'] ?? 'native code'),
            $backtrace[1]['line'] ?? 0,
            $link,
            $package
        );

        @trigger_error($message, E_USER_DEPRECATED);
    }

    
    private static function basename(string $filename): string
    {
        $pos = strrpos($filename, DIRECTORY_SEPARATOR);

        if ($pos === false) {
            return $filename;
        }

        return substr($filename, $pos + 1);
    }

    public static function enableTrackingDeprecations(): void
    {
        self::$type  = self::$type ?? self::getTypeFromEnv();
        self::$type |= self::TYPE_TRACK_DEPRECATIONS;
    }

    public static function enableWithTriggerError(): void
    {
        self::$type  = self::$type ?? self::getTypeFromEnv();
        self::$type |= self::TYPE_TRIGGER_ERROR;
    }

    public static function enableWithPsrLogger(LoggerInterface $logger): void
    {
        self::$type   = self::$type ?? self::getTypeFromEnv();
        self::$type  |= self::TYPE_PSR_LOGGER;
        self::$logger = $logger;
    }

    public static function withoutDeduplication(): void
    {
        self::$deduplication = false;
    }

    public static function disable(): void
    {
        self::$type          = self::TYPE_NONE;
        self::$logger        = null;
        self::$deduplication = true;
        self::$ignoredLinks  = [];

        foreach (self::$triggeredDeprecations as $link => $count) {
            self::$triggeredDeprecations[$link] = 0;
        }
    }

    public static function ignorePackage(string $packageName): void
    {
        self::$ignoredPackages[$packageName] = true;
    }

    public static function ignoreDeprecations(string ...$links): void
    {
        foreach ($links as $link) {
            self::$ignoredLinks[$link] = true;
        }
    }

    public static function getUniqueTriggeredDeprecationsCount(): int
    {
        return array_reduce(self::$triggeredDeprecations, static function (int $carry, int $count) {
            return $carry + $count;
        }, 0);
    }

    
    public static function getTriggeredDeprecations(): array
    {
        return self::$triggeredDeprecations;
    }

    
    private static function getTypeFromEnv(): int
    {
        switch ($_SERVER['DOCTRINE_DEPRECATIONS'] ?? $_ENV['DOCTRINE_DEPRECATIONS'] ?? null) {
            case 'trigger':
                self::$type = self::TYPE_TRIGGER_ERROR;
                break;

            case 'track':
                self::$type = self::TYPE_TRACK_DEPRECATIONS;
                break;

            default:
                self::$type = self::TYPE_NONE;
                break;
        }

        return self::$type;
    }
}
