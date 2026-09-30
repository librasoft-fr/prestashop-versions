<?php

namespace PsMcpServerDeps\Http\Discovery;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Psr18Client extends Psr17Factory implements ClientInterface
{
    private $client;

    public function __construct(
        ?ClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        ?UriFactoryInterface $uriFactory = null,
    ) {
        $requestFactory ?? $requestFactory = $client instanceof RequestFactoryInterface ? $client : null;
        $responseFactory ?? $responseFactory = $client instanceof ResponseFactoryInterface ? $client : null;
        $serverRequestFactory ?? $serverRequestFactory = $client instanceof ServerRequestFactoryInterface ? $client : null;
        $streamFactory ?? $streamFactory = $client instanceof StreamFactoryInterface ? $client : null;
        $uploadedFileFactory ?? $uploadedFileFactory = $client instanceof UploadedFileFactoryInterface ? $client : null;
        $uriFactory ?? $uriFactory = $client instanceof UriFactoryInterface ? $client : null;

        parent::__construct($requestFactory, $responseFactory, $serverRequestFactory, $streamFactory, $uploadedFileFactory, $uriFactory);

        $this->client = $client ?? Psr18ClientDiscovery::find();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
