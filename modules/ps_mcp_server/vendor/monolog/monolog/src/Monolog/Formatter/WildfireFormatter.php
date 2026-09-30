<?php declare(strict_types=1);



namespace Monolog\Formatter;

use Monolog\Logger;


class WildfireFormatter extends NormalizerFormatter
{
    
    private $logLevels = [
        Logger::DEBUG     => 'LOG',
        Logger::INFO      => 'INFO',
        Logger::NOTICE    => 'INFO',
        Logger::WARNING   => 'WARN',
        Logger::ERROR     => 'ERROR',
        Logger::CRITICAL  => 'ERROR',
        Logger::ALERT     => 'ERROR',
        Logger::EMERGENCY => 'ERROR',
    ];

    
    public function __construct(?string $dateFormat = null)
    {
        parent::__construct($dateFormat);

        
        $this->removeJsonEncodeOption(JSON_UNESCAPED_UNICODE);
    }

    
    public function format(array $record): string
    {
        
        $file = $line = '';
        if (isset($record['extra']['file'])) {
            $file = $record['extra']['file'];
            unset($record['extra']['file']);
        }
        if (isset($record['extra']['line'])) {
            $line = $record['extra']['line'];
            unset($record['extra']['line']);
        }

        
        $record = $this->normalize($record);
        $message = ['message' => $record['message']];
        $handleError = false;
        if ($record['context']) {
            $message['context'] = $record['context'];
            $handleError = true;
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
            $handleError = true;
        }
        if (count($message) === 1) {
            $message = reset($message);
        }

        if (isset($record['context']['table'])) {
            $type  = 'TABLE';
            $label = $record['channel'] .': '. $record['message'];
            $message = $record['context']['table'];
        } else {
            $type  = $this->logLevels[$record['level']];
            $label = $record['channel'];
        }

        
        $json = $this->toJson([
            [
                'Type'  => $type,
                'File'  => $file,
                'Line'  => $line,
                'Label' => $label,
            ],
            $message,
        ], $handleError);

        
        return sprintf(
            '%d|%s|',
            strlen($json),
            $json
        );
    }

    
    public function formatBatch(array $records)
    {
        throw new \BadMethodCallException('Batch formatting does not make sense for the WildfireFormatter');
    }

    
    protected function normalize($data, int $depth = 0)
    {
        if (is_object($data) && !$data instanceof \DateTimeInterface) {
            return $data;
        }

        return parent::normalize($data, $depth);
    }
}
