<?php

namespace PsMcpServerDeps\Mcp\Server\Transport;

trait ManagesTransportCallbacks
{
    protected $messageListener;

    protected $sessionEndListener;

    protected $outgoingMessagesProvider;

    protected $pendingRequestsProvider;

    protected $responseFinder;

    protected $fiberYieldHandler;

    public function onMessage(callable $listener): void
    {
        $this->messageListener = $listener;
    }

    public function onSessionEnd(callable $listener): void
    {
        $this->sessionEndListener = $listener;
    }

    public function setOutgoingMessagesProvider(callable $provider): void
    {
        $this->outgoingMessagesProvider = $provider;
    }

    public function setPendingRequestsProvider(callable $provider): void
    {
        $this->pendingRequestsProvider = $provider;
    }

    public function setResponseFinder(callable $finder): void
    {
        $this->responseFinder = $finder;
    }

    public function setFiberYieldHandler(callable $handler): void
    {
        $this->fiberYieldHandler = $handler;
    }
}
