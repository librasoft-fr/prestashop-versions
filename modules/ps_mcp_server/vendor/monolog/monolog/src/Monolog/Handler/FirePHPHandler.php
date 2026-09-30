<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\WildfireFormatter;
use Monolog\Formatter\FormatterInterface;


class FirePHPHandler extends AbstractProcessingHandler
{
    use WebRequestRecognizerTrait;

    
    protected const PROTOCOL_URI = 'http://meta.wildfirehq.org/Protocol/JsonStream/0.2';

    
    protected const STRUCTURE_URI = 'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1';

    
    protected const PLUGIN_URI = 'http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3';

    
    protected const HEADER_PREFIX = 'X-Wf';

    
    protected static $initialized = false;

    
    protected static $messageIndex = 1;

    
    protected static $sendHeaders = true;

    
    protected function createHeader(array $meta, string $message): array
    {
        $header = sprintf('%s-%s', static::HEADER_PREFIX, join('-', $meta));

        return [$header => $message];
    }

    
    protected function createRecordHeader(array $record): array
    {
        
        
        return $this->createHeader(
            [1, 1, 1, self::$messageIndex++],
            $record['formatted']
        );
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new WildfireFormatter();
    }

    
    protected function getInitHeaders(): array
    {
        
        return array_merge(
            $this->createHeader(['Protocol', 1], static::PROTOCOL_URI),
            $this->createHeader([1, 'Structure', 1], static::STRUCTURE_URI),
            $this->createHeader([1, 'Plugin', 1], static::PLUGIN_URI)
        );
    }

    
    protected function sendHeader(string $header, string $content): void
    {
        if (!headers_sent() && self::$sendHeaders) {
            header(sprintf('%s: %s', $header, $content));
        }
    }

    
    protected function write(array $record): void
    {
        if (!self::$sendHeaders || !$this->isWebRequest()) {
            return;
        }

        
        if (!self::$initialized) {
            self::$initialized = true;

            self::$sendHeaders = $this->headersAccepted();
            if (!self::$sendHeaders) {
                return;
            }

            foreach ($this->getInitHeaders() as $header => $content) {
                $this->sendHeader($header, $content);
            }
        }

        $header = $this->createRecordHeader($record);
        if (trim(current($header)) !== '') {
            $this->sendHeader(key($header), current($header));
        }
    }

    
    protected function headersAccepted(): bool
    {
        if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('{\bFirePHP/\d+\.\d+\b}', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }

        return isset($_SERVER['HTTP_X_FIREPHP_VERSION']);
    }
}
