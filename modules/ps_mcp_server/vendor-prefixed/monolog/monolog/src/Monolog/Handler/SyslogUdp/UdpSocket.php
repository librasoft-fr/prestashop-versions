<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Handler\SyslogUdp;

use PsMcpServerDeps\Monolog\Utils;

class UdpSocket
{
    protected const DATAGRAM_MAX_LENGTH = 65023;

    protected $ip;

    protected $port;

    protected $socket;

    public function __construct(string $ip, int $port = 514)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function write($line, $header = '')
    {
        $this->send($this->assembleMessage($line, $header));
    }

    public function close(): void
    {
        if (is_resource($this->socket) || $this->socket instanceof \Socket) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    protected function getSocket()
    {
        if (null !== $this->socket) {
            return $this->socket;
        }

        $domain = AF_INET;
        $protocol = SOL_UDP;

        if ($this->port === 0) {
            $domain = AF_UNIX;
            $protocol = IPPROTO_IP;
        }

        $this->socket = socket_create($domain, SOCK_DGRAM, $protocol) ?: null;
        if (null === $this->socket) {
            throw new \RuntimeException('The UdpSocket to ' . $this->ip . ':' . $this->port . ' could not be opened via socket_create');
        }

        return $this->socket;
    }

    protected function send(string $chunk): void
    {
        socket_sendto($this->getSocket(), $chunk, strlen($chunk), $flags = 0, $this->ip, $this->port);
    }

    protected function assembleMessage(string $line, string $header): string
    {
        $chunkSize = static::DATAGRAM_MAX_LENGTH - strlen($header);

        return $header . Utils::substr($line, 0, $chunkSize);
    }
}
