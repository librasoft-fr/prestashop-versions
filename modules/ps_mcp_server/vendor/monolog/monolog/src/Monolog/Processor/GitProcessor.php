<?php declare(strict_types=1);



namespace Monolog\Processor;

use Monolog\Logger;
use Psr\Log\LogLevel;


class GitProcessor implements ProcessorInterface
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

        $record['extra']['git'] = self::getGitInfo();

        return $record;
    }

    
    private static function getGitInfo(): array
    {
        if (self::$cache) {
            return self::$cache;
        }

        $branches = shell_exec('git branch -v --no-abbrev');
        if ($branches && preg_match('{^\* (.+?)\s+([a-f0-9]{40})(?:\s|$)}m', $branches, $matches)) {
            return self::$cache = [
                'branch' => $matches[1],
                'commit' => $matches[2],
            ];
        }

        return self::$cache = [];
    }
}
