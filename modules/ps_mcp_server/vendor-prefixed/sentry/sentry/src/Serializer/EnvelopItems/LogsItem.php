<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer\EnvelopItems;

use PsMcpServerDeps\Sentry\Attributes\Attribute;
use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\EventType;
use PsMcpServerDeps\Sentry\Logs\Log;
use PsMcpServerDeps\Sentry\Util\JSON;

class LogsItem implements EnvelopeItemInterface
{
    public static function toEnvelopeItem(Event $event): string
    {
        $logs = $event->getLogs();

        $header = [
            'type' => (string) EventType::logs(),
            'item_count' => \count($logs),
            'content_type' => 'application/vnd.sentry.items.log+json',
        ];

        return \sprintf(
            "%s\n%s",
            JSON::encode($header),
            JSON::encode([
                'items' => array_map(static function (Log $log): array {
                    return [
                        'timestamp' => $log->getTimestamp(),
                        'trace_id' => $log->getTraceId(),
                        'level' => (string) $log->getLevel(),
                        'body' => $log->getBody(),
                        'attributes' => array_map(static function (Attribute $attribute): array {
                            return [
                                'type' => $attribute->getType(),
                                'value' => $attribute->getValue(),
                            ];
                        }, $log->attributes()->all()),
                    ];
                }, $logs),
            ])
        );
    }
}
