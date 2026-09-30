<?php

namespace PsMcpServerDeps\Mcp\Client\Handler\Request;

use PsMcpServerDeps\Mcp\Schema\Request\CreateSamplingMessageRequest;
use PsMcpServerDeps\Mcp\Schema\Result\CreateSamplingMessageResult;

interface SamplingCallbackInterface
{
    public function __invoke(CreateSamplingMessageRequest $request): CreateSamplingMessageResult;
}
