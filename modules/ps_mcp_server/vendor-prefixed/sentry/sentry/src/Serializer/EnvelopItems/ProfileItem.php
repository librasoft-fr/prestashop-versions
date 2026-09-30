<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\Serializer\EnvelopItems;

use PsMcpServerDeps\Sentry\Event;
use PsMcpServerDeps\Sentry\Profiling\Profile;
use PsMcpServerDeps\Sentry\Util\JSON;

class ProfileItem implements EnvelopeItemInterface
{
    public static function toEnvelopeItem(Event $event): ?string
    {
        $header = [
            'type' => 'profile',
            'content_type' => 'application/json',
        ];

        $profile = $event->getSdkMetadata('profile');
        if (!$profile instanceof Profile) {
            return null;
        }

        $payload = $profile->getFormattedData($event);
        if ($payload === null) {
            return null;
        }

        return \sprintf("%s\n%s", JSON::encode($header), JSON::encode($payload));
    }
}
