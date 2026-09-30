<?php declare(strict_types=1);



namespace Monolog\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Utils;


class ErrorLogHandler extends AbstractProcessingHandler
{
    public const OPERATING_SYSTEM = 0;
    public const SAPI = 4;

    
    protected $messageType;
    
    protected $expandNewlines;

    
    public function __construct(int $messageType = self::OPERATING_SYSTEM, $level = Logger::DEBUG, bool $bubble = true, bool $expandNewlines = false)
    {
        parent::__construct($level, $bubble);

        if (false === in_array($messageType, self::getAvailableTypes(), true)) {
            $message = sprintf('The given message type "%s" is not supported', print_r($messageType, true));

            throw new \InvalidArgumentException($message);
        }

        $this->messageType = $messageType;
        $this->expandNewlines = $expandNewlines;
    }

    
    public static function getAvailableTypes(): array
    {
        return [
            self::OPERATING_SYSTEM,
            self::SAPI,
        ];
    }

    
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter('[%datetime%] %channel%.%level_name%: %message% %context% %extra%');
    }

    
    protected function write(array $record): void
    {
        if (!$this->expandNewlines) {
            error_log((string) $record['formatted'], $this->messageType);

            return;
        }

        $lines = preg_split('{[\r\n]+}', (string) $record['formatted']);
        if ($lines === false) {
            $pcreErrorCode = preg_last_error();
            throw new \RuntimeException('Failed to preg_split formatted string: ' . $pcreErrorCode . ' / '. Utils::pcreLastErrorMessage($pcreErrorCode));
        }
        foreach ($lines as $line) {
            error_log($line, $this->messageType);
        }
    }
}
