<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry\HttpClient;

use PsMcpServerDeps\Sentry\Options;

interface HttpClientInterface
{
    public function sendRequest(Request $request, Options $options): Response;
}
