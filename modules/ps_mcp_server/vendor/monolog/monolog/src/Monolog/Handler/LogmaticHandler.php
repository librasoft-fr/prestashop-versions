<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Logger;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LogmaticFormatter;


class LogmaticHandler extends SocketHandler
{
    
    private $logToken;

    
    private $hostname;

    
    private $appname;

    
    public function __construct(
        string $token,
        string $hostname = '',
        string $appname = '',
        bool $useSSL = true,
        $level = Logger::DEBUG,
        bool $bubble = true,
        bool $persistent = false,
        float $timeout = 0.0,
        float $writingTimeout = 10.0,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null
    ) {
        if ($useSSL && !extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use SSL encrypted connection for LogmaticHandler');
        }

        $endpoint = $useSSL ? 'ssl://api.logmatic.io:10515' : 'api.logmatic.io:10514';
        $endpoint .= '/v1/';

        parent::__construct(
            $endpoint,
            $level,
            $bubble,
            $persistent,
            $timeout,
            $writingTimeout,
            $connectionTimeout,
            $chunkSize
        );

        $this->logToken = $token;
        $this->hostname = $hostname;
        $this->appname  = $appname;
    }

    
    protected function generateDataStream(array $record): string
    {
        return $this->logToken . ' ' . $record['formatted'];
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        $formatter = new LogmaticFormatter();

        if (!empty($this->hostname)) {
            $formatter->setHostname($this->hostname);
        }
        if (!empty($this->appname)) {
            $formatter->setAppname($this->appname);
        }

        return $formatter;
    }
}
