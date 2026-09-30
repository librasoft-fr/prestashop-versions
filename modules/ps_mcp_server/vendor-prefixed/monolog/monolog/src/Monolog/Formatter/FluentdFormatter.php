<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Monolog\Formatter;

use PsMcpServerDeps\Monolog\Utils;

class FluentdFormatter implements FormatterInterface
{
    protected $levelTag = false;

    public function __construct(bool $levelTag = false)
    {
        if (!function_exists('json_encode')) {
            throw new \RuntimeException('PHP\'s json extension is required to use PsMcpServerDeps\Monolog\'s FluentdUnixFormatter');
        }

        $this->levelTag = $levelTag;
    }

    public function isUsingLevelsInTag(): bool
    {
        return $this->levelTag;
    }

    public function format(array $record): string
    {
        $tag = $record['channel'];
        if ($this->levelTag) {
            $tag .= '.' . strtolower($record['level_name']);
        }

        $message = [
            'message' => $record['message'],
            'context' => $record['context'],
            'extra' => $record['extra'],
        ];

        if (!$this->levelTag) {
            $message['level'] = $record['level'];
            $message['level_name'] = $record['level_name'];
        }

        return Utils::jsonEncode([$tag, $record['datetime']->getTimestamp(), $message]);
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}
