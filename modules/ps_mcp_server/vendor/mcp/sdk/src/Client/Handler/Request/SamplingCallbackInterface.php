<?php



namespace Mcp\Client\Handler\Request;

use Mcp\Schema\Request\CreateSamplingMessageRequest;
use Mcp\Schema\Result\CreateSamplingMessageResult;


interface SamplingCallbackInterface
{
    public function __invoke(CreateSamplingMessageRequest $request): CreateSamplingMessageResult;
}
