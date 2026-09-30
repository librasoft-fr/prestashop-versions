<?php declare(strict_types=1);



namespace Monolog\Processor;

use Monolog\Logger;
use Psr\Log\LogLevel;


class MercurialProcessor implements ProcessorInterface
{
    
    private $level;
    
    private static $cache = null;

    
    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    
    public function __invoke(array $record): array
    {
        
        if ($record['level'] < $this->level) {
            return $record;
        }

        $record['extra']['hg'] = self::getMercurialInfo();

        return $record;
    }

    
    private static function getMercurialInfo(): array
    {
        if (self::$cache) {
            return self::$cache;
        }

        $result = explode(' ', trim((string) shell_exec('hg id -nb')));

        if (count($result) >= 3) {
            return self::$cache = [
                'branch' => $result[1],
                'revision' => $result[2],
            ];
        }

        return self::$cache = [];
    }
}
