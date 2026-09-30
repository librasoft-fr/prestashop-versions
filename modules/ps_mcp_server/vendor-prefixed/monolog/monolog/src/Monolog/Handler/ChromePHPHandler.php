<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\ChromePHPFormatter;
use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Logger;
use PsMcpServerDeps\Monolog\Utils;

class ChromePHPHandler extends AbstractProcessingHandler
{
    use WebRequestRecognizerTrait;

    protected const VERSION = '4.0';

    protected const HEADER_NAME = 'X-ChromeLogger-Data';

    protected const USER_AGENT_REGEX = '{\b(?:Chrome/\d+(?:\.\d+)*|HeadlessChrome|Firefox/(?:4[3-9]|[5-9]\d|\d{3,})(?:\.\d)*)\b}';

    protected static $initialized = false;

    protected static $overflowed = false;

    protected static $json = [
        'version' => self::VERSION,
        'columns' => ['label', 'log', 'backtrace', 'type'],
        'rows' => [],
    ];

    protected static $sendHeaders = true;

    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        if (!function_exists('json_encode')) {
            throw new \RuntimeException('PHP\'s json extension is required to use PsMcpServerDeps\Monolog\'s ChromePHPHandler');
        }
    }

    public function handleBatch(array $records): void
    {
        if (!$this->isWebRequest()) {
            return;
        }

        $messages = [];

        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }

            $message = $this->processRecord($record);
            $messages[] = $message;
        }

        if (!empty($messages)) {
            $messages = $this->getFormatter()->formatBatch($messages);
            self::$json['rows'] = array_merge(self::$json['rows'], $messages);
            $this->send();
        }
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new ChromePHPFormatter();
    }

    protected function write(array $record): void
    {
        if (!$this->isWebRequest()) {
            return;
        }

        self::$json['rows'][] = $record['formatted'];

        $this->send();
    }

    protected function send(): void
    {
        if (self::$overflowed || !self::$sendHeaders) {
            return;
        }

        if (!self::$initialized) {
            self::$initialized = true;

            self::$sendHeaders = $this->headersAccepted();
            if (!self::$sendHeaders) {
                return;
            }

            self::$json['request_uri'] = $_SERVER['REQUEST_URI'] ?? '';
        }

        $json = Utils::jsonEncode(self::$json, Utils::DEFAULT_JSON_FLAGS & ~JSON_UNESCAPED_UNICODE, true);
        $data = base64_encode($json);
        if (strlen($data) > 3 * 1024) {
            self::$overflowed = true;

            $record = [
                'message' => 'Incomplete logs, chrome header size limit reached',
                'context' => [],
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'channel' => 'monolog',
                'datetime' => new \DateTimeImmutable(),
                'extra' => [],
            ];
            self::$json['rows'][count(self::$json['rows']) - 1] = $this->getFormatter()->format($record);
            $json = Utils::jsonEncode(self::$json, Utils::DEFAULT_JSON_FLAGS & ~JSON_UNESCAPED_UNICODE, true);
            $data = base64_encode($json);
        }

        if (trim($data) !== '') {
            $this->sendHeader(static::HEADER_NAME, $data);
        }
    }

    protected function sendHeader(string $header, string $content): void
    {
        if (!headers_sent() && self::$sendHeaders) {
            header(sprintf('%s: %s', $header, $content));
        }
    }

    protected function headersAccepted(): bool
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        return preg_match(static::USER_AGENT_REGEX, $_SERVER['HTTP_USER_AGENT']) === 1;
    }
}
