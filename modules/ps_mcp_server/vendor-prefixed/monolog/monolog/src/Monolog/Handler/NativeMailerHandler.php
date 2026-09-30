<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler;

use PsMcpServerDeps\Monolog\Formatter\LineFormatter;
use PsMcpServerDeps\Monolog\Logger;

class NativeMailerHandler extends MailHandler
{
    protected $to;

    protected $subject;

    protected $headers = [];

    protected $parameters = [];

    protected $maxColumnWidth;

    protected $contentType;

    protected $encoding = 'utf-8';

    public function __construct($to, string $subject, string $from, $level = Logger::ERROR, bool $bubble = true, int $maxColumnWidth = 70)
    {
        parent::__construct($level, $bubble);
        $this->to = (array) $to;
        $this->subject = $subject;
        $this->addHeader(sprintf('From: %s', $from));
        $this->maxColumnWidth = $maxColumnWidth;
    }

    public function addHeader($headers): self
    {
        foreach ((array) $headers as $header) {
            if (strpos($header, "\n") !== false || strpos($header, "\r") !== false) {
                throw new \InvalidArgumentException('Headers can not contain newline characters for security reasons');
            }
            $this->headers[] = $header;
        }

        return $this;
    }

    public function addParameter($parameters): self
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);

        return $this;
    }

    protected function send(string $content, array $records): void
    {
        $contentType = $this->getContentType() ?: ($this->isHtmlBody($content) ? 'text/html' : 'text/plain');

        if ($contentType !== 'text/html') {
            $content = wordwrap($content, $this->maxColumnWidth);
        }

        $headers = ltrim(implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $contentType . '; charset=' . $this->getEncoding() . "\r\n";
        if ($contentType === 'text/html' && false === strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }

        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }

        $parameters = implode(' ', $this->parameters);
        foreach ($this->to as $to) {
            mail($to, $subject, $content, $headers, $parameters);
        }
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setContentType(string $contentType): self
    {
        if (strpos($contentType, "\n") !== false || strpos($contentType, "\r") !== false) {
            throw new \InvalidArgumentException('The content type can not contain newline characters to prevent email header injection');
        }

        $this->contentType = $contentType;

        return $this;
    }

    public function setEncoding(string $encoding): self
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new \InvalidArgumentException('The encoding can not contain newline characters to prevent email header injection');
        }

        $this->encoding = $encoding;

        return $this;
    }
}
