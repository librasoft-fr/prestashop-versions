<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Logger;

class MandrillHandler extends MailHandler
{
    protected $message;

    protected $apiKey;

    public function __construct(string $apiKey, $message, $level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (!$message instanceof \Swift_Message && is_callable($message)) {
            $message = $message();
        }
        if (!$message instanceof \Swift_Message) {
            throw new \InvalidArgumentException('You must provide either a Swift_Message instance or a callable returning it');
        }
        $this->message = $message;
        $this->apiKey = $apiKey;
    }

    protected function send(string $content, array $records): void
    {
        $mime = 'text/plain';
        if ($this->isHtmlBody($content)) {
            $mime = 'text/html';
        }

        $message = clone $this->message;
        $message->setBody($content, $mime);

        if (version_compare(\Swift::VERSION, '6.0.0', '>=')) {
            $message->setDate(new \DateTimeImmutable());
        } else {
            $message->setDate(time());
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://mandrillapp.com/api/1.0/messages/send-raw.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'key' => $this->apiKey,
            'raw_message' => (string) $message,
            'async' => false,
        ]));

        Curl\Util::execute($ch);
    }
}
