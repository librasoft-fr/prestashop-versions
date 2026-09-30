<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Utils;


class CubeHandler extends AbstractProcessingHandler
{
    
    private $udpConnection = null;
    
    private $httpConnection = null;
    
    private $scheme;
    
    private $host;
    
    private $port;
    
    private $acceptedSchemes = ['http', 'udp'];

    
    public function __construct(string $url, $level = Logger::DEBUG, bool $bubble = true)
    {
        $urlInfo = parse_url($url);

        if ($urlInfo === false || !isset($urlInfo['scheme'], $urlInfo['host'], $urlInfo['port'])) {
            throw new \UnexpectedValueException('URL "'.$url.'" is not valid');
        }

        if (!in_array($urlInfo['scheme'], $this->acceptedSchemes)) {
            throw new \UnexpectedValueException(
                'Invalid protocol (' . $urlInfo['scheme']  . ').'
                . ' Valid options are ' . implode(', ', $this->acceptedSchemes)
            );
        }

        $this->scheme = $urlInfo['scheme'];
        $this->host = $urlInfo['host'];
        $this->port = (int) $urlInfo['port'];

        parent::__construct($level, $bubble);
    }

    
    protected function connectUdp(): void
    {
        if (!extension_loaded('sockets')) {
            throw new MissingExtensionException('The sockets extension is required to use udp URLs with the CubeHandler');
        }

        $udpConnection = socket_create(AF_INET, SOCK_DGRAM, 0);
        if (false === $udpConnection) {
            throw new \LogicException('Unable to create a socket');
        }

        $this->udpConnection = $udpConnection;
        if (!socket_connect($this->udpConnection, $this->host, $this->port)) {
            throw new \LogicException('Unable to connect to the socket at ' . $this->host . ':' . $this->port);
        }
    }

    
    protected function connectHttp(): void
    {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is required to use http URLs with the CubeHandler');
        }

        $httpConnection = curl_init('http://'.$this->host.':'.$this->port.'/1.0/event/put');
        if (false === $httpConnection) {
            throw new \LogicException('Unable to connect to ' . $this->host . ':' . $this->port);
        }

        $this->httpConnection = $httpConnection;
        curl_setopt($this->httpConnection, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->httpConnection, CURLOPT_RETURNTRANSFER, true);
    }

    
    protected function write(array $record): void
    {
        $date = $record['datetime'];

        $data = ['time' => $date->format('Y-m-d\TH:i:s.uO')];
        unset($record['datetime']);

        if (isset($record['context']['type'])) {
            $data['type'] = $record['context']['type'];
            unset($record['context']['type']);
        } else {
            $data['type'] = $record['channel'];
        }

        $data['data'] = $record['context'];
        $data['data']['level'] = $record['level'];

        if ($this->scheme === 'http') {
            $this->writeHttp(Utils::jsonEncode($data));
        } else {
            $this->writeUdp(Utils::jsonEncode($data));
        }
    }

    private function writeUdp(string $data): void
    {
        if (!$this->udpConnection) {
            $this->connectUdp();
        }

        socket_send($this->udpConnection, $data, strlen($data), 0);
    }

    private function writeHttp(string $data): void
    {
        if (!$this->httpConnection) {
            $this->connectHttp();
        }

        if (null === $this->httpConnection) {
            throw new \LogicException('No connection could be established');
        }

        curl_setopt($this->httpConnection, CURLOPT_POSTFIELDS, '['.$data.']');
        curl_setopt($this->httpConnection, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen('['.$data.']'),
        ]);

        Curl\Util::execute($this->httpConnection, 5, false);
    }
}
