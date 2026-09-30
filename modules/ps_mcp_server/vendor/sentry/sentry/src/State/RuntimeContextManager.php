<?php

declare(strict_types=1);

namespace Sentry\State;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sentry\Tracing\PropagationContext;


final class RuntimeContextManager
{
    private const PROCESS_EXECUTION_CONTEXT_KEY = 'process';

    
    private $baseHub;

    
    private $globalContext;

    
    private $activeContexts = [];

    
    private $executionContextToRuntimeContext = [];

    public function __construct(HubInterface $baseHub)
    {
        $this->baseHub = $baseHub;
        $this->globalContext = null;
    }

    
    public function setCurrentHub(HubInterface $hub): bool
    {
        $executionContextKey = $this->getExecutionContextKey();

        if ($this->hasActiveContextForExecutionContextKey($executionContextKey)) {
            $runtimeContextId = $this->executionContextToRuntimeContext[$executionContextKey];
            $this->activeContexts[$runtimeContextId]->setHub($hub);

            return true;
        }

        $this->baseHub = $hub;

        if ($this->globalContext !== null) {
            $this->globalContext->setHub($hub);
        }

        return false;
    }

    public function getCurrentHub(): HubInterface
    {
        return $this->getCurrentContext()->getHub();
    }

    public function getCurrentContext(): RuntimeContext
    {
        $executionContextKey = $this->getExecutionContextKey();

        if ($this->hasActiveContextForExecutionContextKey($executionContextKey)) {
            $runtimeContextId = $this->executionContextToRuntimeContext[$executionContextKey];

            return $this->activeContexts[$runtimeContextId];
        }

        return $this->getGlobalContext();
    }

    public function hasActiveContext(): bool
    {
        return $this->hasActiveContextForExecutionContextKey($this->getExecutionContextKey());
    }

    
    public function startContext(): void
    {
        $executionContextKey = $this->getExecutionContextKey();

        if ($this->hasActiveContextForExecutionContextKey($executionContextKey)) {
            
            return;
        }

        $this->createContextForExecutionContextKey($executionContextKey);
    }

    
    public function endContext(?int $timeout = null): void
    {
        $executionContextKey = $this->getExecutionContextKey();

        if (!$this->hasActiveContextForExecutionContextKey($executionContextKey)) {
            return;
        }

        $runtimeContextId = $this->executionContextToRuntimeContext[$executionContextKey];
        unset($this->executionContextToRuntimeContext[$executionContextKey]);

        $this->removeContextById($runtimeContextId, $timeout);
    }

    private function createContextForExecutionContextKey(string $executionContextKey): void
    {
        $runtimeContextId = $this->generateRuntimeContextId();
        $runtimeContext = new RuntimeContext($runtimeContextId, $this->createHubFromBaseHub());

        $this->activeContexts[$runtimeContextId] = $runtimeContext;
        $this->executionContextToRuntimeContext[$executionContextKey] = $runtimeContextId;
    }

    private function removeContextById(string $runtimeContextId, ?int $timeout = null): void
    {
        if (!isset($this->activeContexts[$runtimeContextId])) {
            return;
        }

        $runtimeContext = $this->activeContexts[$runtimeContextId];
        unset($this->activeContexts[$runtimeContextId]);
        
        $this->removeExecutionContextMappingsForRuntimeContext($runtimeContextId);

        $logger = $this->getLoggerFromHub($runtimeContext->getHub());

        $this->flushRuntimeContextResources($runtimeContext, $timeout, $logger);
    }

    private function flushRuntimeContextResources(RuntimeContext $runtimeContext, ?int $timeout, LoggerInterface $logger): void
    {
        $hub = $runtimeContext->getHub();

        
        
        try {
            $runtimeContext->getLogsAggregator()->flush($hub);
        } catch (\Throwable $exception) {
            $logger->error('Failed to flush logs while ending a runtime context.', [
                'exception' => $exception,
                'runtime_context_id' => $runtimeContext->getId(),
            ]);
        }

        
        try {
            $runtimeContext->getMetricsAggregator()->flush($hub);
        } catch (\Throwable $exception) {
            $logger->error('Failed to flush trace metrics while ending a runtime context.', [
                'exception' => $exception,
                'runtime_context_id' => $runtimeContext->getId(),
            ]);
        }

        $client = $hub->getClient();

        if ($client === null) {
            return;
        }

        
        try {
            $client->flush($timeout);
        } catch (\Throwable $exception) {
            $logger->error('Failed to flush the client transport while ending a runtime context.', [
                'exception' => $exception,
                'runtime_context_id' => $runtimeContext->getId(),
            ]);
        }
    }

    private function removeExecutionContextMappingsForRuntimeContext(string $runtimeContextId): void
    {
        foreach ($this->executionContextToRuntimeContext as $executionContextKey => $mappedRuntimeContextId) {
            if ($mappedRuntimeContextId === $runtimeContextId) {
                unset($this->executionContextToRuntimeContext[$executionContextKey]);
            }
        }
    }

    private function hasActiveContextForExecutionContextKey(string $executionContextKey): bool
    {
        if (!isset($this->executionContextToRuntimeContext[$executionContextKey])) {
            return false;
        }

        $runtimeContextId = $this->executionContextToRuntimeContext[$executionContextKey];

        if (!isset($this->activeContexts[$runtimeContextId])) {
            
            unset($this->executionContextToRuntimeContext[$executionContextKey]);

            return false;
        }

        return true;
    }

    private function createHubFromBaseHub(): HubInterface
    {
        if (!$this->baseHub instanceof Hub) {
            return new Hub($this->baseHub->getClient());
        }

        $clonedScope = null;

        $this->baseHub->configureScope(static function (Scope $scope) use (&$clonedScope): void {
            $clonedScope = clone $scope;
            
            $clonedScope->setSpan(null);
            $clonedScope->setPropagationContext(PropagationContext::fromDefaults());
        });

        return new Hub($this->baseHub->getClient(), $clonedScope ?? new Scope());
    }

    private function getLoggerFromHub(HubInterface $hub): LoggerInterface
    {
        $client = $hub->getClient();

        if ($client === null) {
            return new NullLogger();
        }

        return $client->getOptions()->getLoggerOrNullLogger();
    }

    private function generateRuntimeContextId(): string
    {
        return \sprintf('%s-%d', str_replace('.', '', uniqid('', true)), mt_rand());
    }

    private function getExecutionContextKey(): string
    {
        
        return self::PROCESS_EXECUTION_CONTEXT_KEY;
    }

    private function getGlobalContext(): RuntimeContext
    {
        if ($this->globalContext === null) {
            
            $this->globalContext = new RuntimeContext('global', $this->baseHub);
        }

        return $this->globalContext;
    }
}
