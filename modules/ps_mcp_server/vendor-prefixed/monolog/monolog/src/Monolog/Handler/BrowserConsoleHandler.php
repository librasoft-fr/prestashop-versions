<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\LineFormatter;
use PsMcpServerDeps\Monolog\Logger;
use PsMcpServerDeps\Monolog\Utils;

class BrowserConsoleHandler extends AbstractProcessingHandler
{
    protected static $initialized = false;

    protected static $records = [];

    protected const FORMAT_HTML = 'html';
    protected const FORMAT_JS = 'js';
    protected const FORMAT_UNKNOWN = 'unknown';

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter('[[%channel%]]{macro: autolabel} [[%level_name%]]{font-weight: bold} %message%');
    }

    protected function write(array $record): void
    {
        static::$records[] = $record;

        if (!static::$initialized) {
            static::$initialized = true;
            $this->registerShutdownFunction();
        }
    }

    public static function send(): void
    {
        $format = static::getResponseFormat();
        if ($format === self::FORMAT_UNKNOWN) {
            return;
        }

        if (\count(static::$records)) {
            if ($format === self::FORMAT_HTML) {
                static::writeOutput('<script>' . static::generateScript() . '</script>');
            } elseif ($format === self::FORMAT_JS) {
                static::writeOutput(static::generateScript());
            }
            static::resetStatic();
        }
    }

    public function close(): void
    {
        self::resetStatic();
    }

    public function reset()
    {
        parent::reset();

        self::resetStatic();
    }

    public static function resetStatic(): void
    {
        static::$records = [];
    }

    protected function registerShutdownFunction(): void
    {
        if (PHP_SAPI !== 'cli') {
            register_shutdown_function(['PsMcpServerDeps\Monolog\Handler\BrowserConsoleHandler', 'send']);
        }
    }

    protected static function writeOutput(string $str): void
    {
        echo $str;
    }

    protected static function getResponseFormat(): string
    {
        foreach (\headers_list() as $header) {
            if (\stripos($header, 'content-type:') === 0) {
                return static::getResponseFormatFromContentType($header);
            }
        }

        return self::FORMAT_HTML;
    }

    protected static function getResponseFormatFromContentType(string $contentType): string
    {
        if (\stripos($contentType, 'application/javascript') !== false || \stripos($contentType, 'text/javascript') !== false) {
            return self::FORMAT_JS;
        }

        if (\stripos($contentType, 'text/html') !== false) {
            return self::FORMAT_HTML;
        }

        return self::FORMAT_UNKNOWN;
    }

    private static function generateScript(): string
    {
        $script = [];
        foreach (static::$records as $record) {
            $context = static::dump('Context', $record['context']);
            $extra = static::dump('Extra', $record['extra']);

            if (empty($context) && empty($extra)) {
                $script[] = static::call_array(static::getConsoleMethodForLevel($record['level']), static::handleStyles($record['formatted']));
            } else {
                $script = array_merge(
                    $script,
                    [static::call_array('groupCollapsed', static::handleStyles($record['formatted']))],
                    $context,
                    $extra,
                    [static::call('groupEnd')]
                );
            }
        }

        return "(function (c) {if (c && c.groupCollapsed) {\n" . implode("\n", $script) . "\n}})(console);";
    }

    private static function getConsoleMethodForLevel(int $level): string
    {
        return [
            Logger::DEBUG => 'debug',
            Logger::INFO => 'info',
            Logger::NOTICE => 'info',
            Logger::WARNING => 'warn',
            Logger::ERROR => 'error',
            Logger::CRITICAL => 'error',
            Logger::ALERT => 'error',
            Logger::EMERGENCY => 'error',
        ][$level] ?? 'log';
    }

    private static function handleStyles(string $formatted): array
    {
        $args = [];
        $format = '%c' . $formatted;
        preg_match_all('/\[\[(.*?)\]\]\{([^}]*)\}/s', $format, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach (array_reverse($matches) as $match) {
            $args[] = '"font-weight: normal"';
            $args[] = static::quote(static::handleCustomStyles($match[2][0], $match[1][0]));

            $pos = $match[0][1];
            $format = Utils::substr($format, 0, $pos) . '%c' . $match[1][0] . '%c' . Utils::substr($format, $pos + strlen($match[0][0]));
        }

        $args[] = static::quote('font-weight: normal');
        $args[] = static::quote($format);

        return array_reverse($args);
    }

    private static function handleCustomStyles(string $style, string $string): string
    {
        static $colors = ['blue', 'green', 'red', 'magenta', 'orange', 'black', 'grey'];
        static $labels = [];

        $style = preg_replace_callback('/macro\s*:(.*?)(?:;|$)/', function (array $m) use ($string, &$colors, &$labels) {
            if (trim($m[1]) === 'autolabel') {
                if (!isset($labels[$string])) {
                    $labels[$string] = $colors[\count($labels) % \count($colors)];
                }
                $color = $labels[$string];

                return "background-color: $color; color: white; border-radius: 3px; padding: 0 2px 0 2px";
            }

            return $m[1];
        }, $style);

        if (null === $style) {
            $pcreErrorCode = preg_last_error();
            throw new \RuntimeException('Failed to run preg_replace_callback: ' . $pcreErrorCode . ' / ' . Utils::pcreLastErrorMessage($pcreErrorCode));
        }

        return $style;
    }

    private static function dump(string $title, array $dict): array
    {
        $script = [];
        $dict = array_filter($dict);
        if (empty($dict)) {
            return $script;
        }
        $script[] = static::call('log', static::quote('%c%s'), static::quote('font-weight: bold'), static::quote($title));
        foreach ($dict as $key => $value) {
            $value = json_encode($value);
            if (empty($value)) {
                $value = static::quote('');
            }
            $script[] = static::call('log', static::quote('%s: %o'), static::quote((string) $key), $value);
        }

        return $script;
    }

    private static function quote(string $arg): string
    {
        return '"' . addcslashes($arg, "\"\n\\") . '"';
    }

    private static function call(...$args): string
    {
        $method = array_shift($args);
        if (!is_string($method)) {
            throw new \UnexpectedValueException('Expected the first arg to be a string, got: ' . var_export($method, true));
        }

        return static::call_array($method, $args);
    }

    private static function call_array(string $method, array $args): string
    {
        return 'c.' . $method . '(' . implode(', ', $args) . ');';
    }
}
