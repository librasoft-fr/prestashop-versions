<?php

declare(strict_types=1);

namespace PsMcpServerDeps\Sentry;

use PsMcpServerDeps\Sentry\Integration\IntegrationInterface;
use PsMcpServerDeps\Sentry\State\Scope;
use PsMcpServerDeps\Sentry\Transport\Result;

interface ClientInterface
{
    public function getOptions(): Options;

    public function getCspReportUrl(): ?string;

    public function captureMessage(string $message, ?Severity $level = null, ?Scope $scope = null, ?EventHint $hint = null): ?EventId;

    public function captureException(\Throwable $exception, ?Scope $scope = null, ?EventHint $hint = null): ?EventId;

    public function captureLastError(?Scope $scope = null, ?EventHint $hint = null): ?EventId;

    public function captureEvent(Event $event, ?EventHint $hint = null, ?Scope $scope = null): ?EventId;

    public function getIntegration(string $className): ?IntegrationInterface;

    public function flush(?int $timeout = null): Result;

    public function getStacktraceBuilder(): StacktraceBuilder;
}
