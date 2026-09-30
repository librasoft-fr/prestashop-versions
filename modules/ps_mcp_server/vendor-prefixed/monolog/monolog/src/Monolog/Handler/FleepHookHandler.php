<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\FormatterInterface;
use PsMcpServerDeps\Monolog\Formatter\LineFormatter;
use PsMcpServerDeps\Monolog\Logger;

class FleepHookHandler extends SocketHandler
{
    protected const FLEEP_HOST = 'fleep.io';

    protected const FLEEP_HOOK_URI = '/hook/';

    protected $token;

    public function __construct(
        string $token,
        $level = Logger::DEBUG,
        bool $bubble = true,
        bool $persistent = false,
        float $timeout = 0.0,
        float $writingTimeout = 10.0,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null,
    ) {
        if (!extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use the FleepHookHandler');
        }

        $this->token = $token;

        $connectionString = 'ssl://' . static::FLEEP_HOST . ':443';
        parent::__construct(
            $connectionString,
            $level,
            $bubble,
            $persistent,
            $timeout,
            $writingTimeout,
            $connectionTimeout,
            $chunkSize
        );
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(null, null, true, true);
    }

    public function write(array $record): void
    {
        parent::write($record);
        $this->closeSocket();
    }

    protected function generateDataStream(array $record): string
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

    private function buildHeader(string $content): string
    {
        $header = 'POST ' . static::FLEEP_HOOK_URI . $this->token . " HTTP/1.1\r\n";
        $header .= 'Host: ' . static::FLEEP_HOST . "\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Content-Length: ' . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }

    private function buildContent(array $record): string
    {
        $dataArray = [
            'message' => $record['formatted'],
        ];

        return http_build_query($dataArray);
    }
}
