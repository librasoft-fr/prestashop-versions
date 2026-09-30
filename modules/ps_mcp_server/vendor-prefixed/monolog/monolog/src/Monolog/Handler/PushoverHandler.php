<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;
use PsMcpServerDeps\Monolog\Utils;

class PushoverHandler extends SocketHandler
{
    private $token;

    private $users;

    private $title;

    private $user;

    private $retry;

    private $expire;

    private $highPriorityLevel;

    private $emergencyLevel;

    private $useFormattedMessage = false;

    private $parameterNames = [
        'token' => true,
        'user' => true,
        'message' => true,
        'device' => true,
        'title' => true,
        'url' => true,
        'url_title' => true,
        'priority' => true,
        'timestamp' => true,
        'sound' => true,
        'retry' => true,
        'expire' => true,
        'callback' => true,
    ];

    private $sounds = [
        'pushover', 'bike', 'bugle', 'cashregister', 'classical', 'cosmic', 'falling', 'gamelan', 'incoming',
        'intermission', 'magic', 'mechanical', 'pianobar', 'siren', 'spacealarm', 'tugboat', 'alien', 'climb',
        'persistent', 'echo', 'updown', 'none',
    ];

    public function __construct(
        string $token,
        $users,
        ?string $title = null,
        $level = Logger::CRITICAL,
        bool $bubble = true,
        bool $useSSL = true,
        $highPriorityLevel = Logger::CRITICAL,
        $emergencyLevel = Logger::EMERGENCY,
        int $retry = 30,
        int $expire = 25200,
        bool $persistent = false,
        float $timeout = 0.0,
        float $writingTimeout = 10.0,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null,
    ) {
        $connectionString = $useSSL ? 'ssl://api.pushover.net:443' : 'api.pushover.net:80';
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

        $this->token = $token;
        $this->users = (array) $users;
        $this->title = $title ?: (string) gethostname();
        $this->highPriorityLevel = Logger::toMonologLevel($highPriorityLevel);
        $this->emergencyLevel = Logger::toMonologLevel($emergencyLevel);
        $this->retry = $retry;
        $this->expire = $expire;
    }

    protected function generateDataStream(array $record): string
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

    private function buildContent(array $record): string
    {
        $maxMessageLength = 512 - strlen($this->title);

        $message = ($this->useFormattedMessage) ? $record['formatted'] : $record['message'];
        $message = Utils::substr($message, 0, $maxMessageLength);

        $timestamp = $record['datetime']->getTimestamp();

        $dataArray = [
            'token' => $this->token,
            'user' => $this->user,
            'message' => $message,
            'title' => $this->title,
            'timestamp' => $timestamp,
        ];

        if (isset($record['level']) && $record['level'] >= $this->emergencyLevel) {
            $dataArray['priority'] = 2;
            $dataArray['retry'] = $this->retry;
            $dataArray['expire'] = $this->expire;
        } elseif (isset($record['level']) && $record['level'] >= $this->highPriorityLevel) {
            $dataArray['priority'] = 1;
        }

        $context = array_intersect_key($record['context'], $this->parameterNames);
        $extra = array_intersect_key($record['extra'], $this->parameterNames);

        $dataArray = array_merge($extra, $context, $dataArray);

        if (isset($dataArray['sound']) && !in_array($dataArray['sound'], $this->sounds)) {
            unset($dataArray['sound']);
        }

        return http_build_query($dataArray);
    }

    private function buildHeader(string $content): string
    {
        $header = "POST /1/messages.json HTTP/1.1\r\n";
        $header .= "Host: api.pushover.net\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Content-Length: ' . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }

    protected function write(array $record): void
    {
        foreach ($this->users as $user) {
            $this->user = $user;

            parent::write($record);
            $this->closeSocket();
        }

        $this->user = null;
    }

    public function setHighPriorityLevel($value): self
    {
        $this->highPriorityLevel = Logger::toMonologLevel($value);

        return $this;
    }

    public function setEmergencyLevel($value): self
    {
        $this->emergencyLevel = Logger::toMonologLevel($value);

        return $this;
    }

    public function useFormattedMessage(bool $value): self
    {
        $this->useFormattedMessage = $value;

        return $this;
    }
}
