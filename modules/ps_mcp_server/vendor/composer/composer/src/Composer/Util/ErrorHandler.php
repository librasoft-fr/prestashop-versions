<?php declare(strict_types=1);



namespace Composer\Util;

use Composer\IO\IOInterface;


class ErrorHandler
{
    
    private static $io;

    
    private static $hasShownDeprecationNotice = 0;

    
    public static function handle(int $level, string $message, string $file, int $line): bool
    {
        $isDeprecationNotice = $level === E_DEPRECATED || $level === E_USER_DEPRECATED;

        
        if (!$isDeprecationNotice && 0 === (error_reporting() & $level)) {
            return true;
        }

        if (filter_var(ini_get('xdebug.scream'), FILTER_VALIDATE_BOOLEAN)) {
            $message .= "\n\nWarning: You have xdebug.scream enabled, the warning above may be".
            "\na legitimately suppressed error that you were not supposed to see.";
        }

        if (!$isDeprecationNotice) {
            
            
            if (in_array($level, [E_WARNING, E_USER_WARNING], true) && str_contains($message, 'should either be used or intentionally ignored by casting it as (void)')) {
                self::outputWarning('Ignored new PHP warning but it should be reported and fixed: '.$message.' in '.$file.':'.$line, true);

                return true;
            }

            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        if (self::$io !== null) {
            if (self::$hasShownDeprecationNotice > 0 && !self::$io->isVerbose()) {
                if (self::$hasShownDeprecationNotice === 1) {
                    self::$io->writeError('<warning>More deprecation notices were hidden, run again with `-v` to show them.</warning>');
                    self::$hasShownDeprecationNotice = 2;
                }

                return true;
            }
            self::$hasShownDeprecationNotice = 1;
            self::outputWarning('Deprecation Notice: '.$message.' in '.$file.':'.$line);
        }

        return true;
    }

    
    public static function register(?IOInterface $io = null): void
    {
        set_error_handler([__CLASS__, 'handle']);
        error_reporting(E_ALL);
        self::$io = $io;
    }

    private static function outputWarning(string $message, bool $outputEvenWithoutIO = false): void
    {
        if (self::$io !== null) {
            self::$io->writeError('<warning>'.$message.'</warning>');
            if (self::$io->isVerbose()) {
                self::$io->writeError('<warning>Stack trace:</warning>');
                self::$io->writeError(array_filter(array_map(static function ($a): ?string {
                    if (isset($a['line'], $a['file'])) {
                        return '<warning> '.$a['file'].':'.$a['line'].'</warning>';
                    }

                    return null;
                }, array_slice(debug_backtrace(), 2)), static function (?string $line) {
                    return $line !== null;
                }));
            }

            return;
        }

        if ($outputEvenWithoutIO) {
            if (defined('STDERR') && is_resource(STDERR)) {
                fwrite(STDERR, 'Warning: '.$message.PHP_EOL);
            } else {
                echo 'Warning: '.$message.PHP_EOL;
            }
        }
    }
}
