<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer;

use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\EventType;
use PsMcpServerDeps\Sentry\Options;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\CheckInItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\ClientReportItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\EventItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\LogsItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\MetricsItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\ProfileItem;
use PsMcpServerDeps\Sentry\Serializer\EnvelopItems\TransactionItem;
use PsMcpServerDeps\Sentry\Tracing\DynamicSamplingContext;
use PsMcpServerDeps\Sentry\Util\JSON;

final class PayloadSerializer implements PayloadSerializerInterface
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function serialize(Event $event): string
    {
        $envelopeHeader = null;
        if ($event->getType() !== EventType::clientReport()) {
            $envelopeHeader = [
                'sent_at' => gmdate('Y-m-d\TH:i:s\Z'),
                'dsn' => (string) $this->options->getDsn(),
                'sdk' => $event->getSdkPayload(),
            ];

            if ($event->getType()->requiresEventId()) {
                $envelopeHeader['event_id'] = (string) $event->getId();
            }

            $dynamicSamplingContext = $event->getSdkMetadata('dynamic_sampling_context');
            if ($dynamicSamplingContext instanceof DynamicSamplingContext) {
                $entries = $dynamicSamplingContext->getEntries();

                if (!empty($entries)) {
                    $envelopeHeader['trace'] = $entries;
                }
            }
        }

        $items = [];

        switch ($event->getType()) {
            case EventType::event():
                $items[] = EventItem::toEnvelopeItem($event);
                break;
            case EventType::transaction():
                $items[] = TransactionItem::toEnvelopeItem($event);
                if ($event->getSdkMetadata('profile') !== null) {
                    $items[] = ProfileItem::toEnvelopeItem($event);
                }
                break;
            case EventType::checkIn():
                $items[] = CheckInItem::toEnvelopeItem($event);
                break;
            case EventType::logs():
                $items[] = LogsItem::toEnvelopeItem($event);
                break;
            case EventType::metrics():
                $items[] = MetricsItem::toEnvelopeItem($event);
                break;
            case EventType::clientReport():
                $items[] = ClientReportItem::toEnvelopeItem($event);
                break;
        }

        if ($envelopeHeader === null) {
            return \sprintf("{}\n%s", implode("\n", array_filter($items)));
        }

        return \sprintf("%s\n%s", JSON::encode($envelopeHeader), implode("\n", array_filter($items)));
    }
}
