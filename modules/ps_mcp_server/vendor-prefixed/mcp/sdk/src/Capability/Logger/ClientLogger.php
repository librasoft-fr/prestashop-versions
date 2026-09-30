<?php

namespace PsMcpServerDeps\Mcp\Capability\Logger;

use PsMcpServerDeps\Mcp\Schema\Enum\LoggingLevel;
use PsMcpServerDeps\Mcp\Server\ClientGateway;
use PsMcpServerDeps\Mcp\Server\Protocol;
use PsMcpServerDeps\Mcp\Server\Session\SessionInterface;
use Psr\Log\AbstractLogger;

final class ClientLogger extends AbstractLogger
{
    public function __construct(
        private ClientGateway $client,
        private SessionInterface $session,
    ) {
    }

    public function log($level, $message, array $context = []): void
    {
        $mcpLevel = $this->convertToMcpLevel($level);
        if (null === $mcpLevel) {
            return;
        }

        $minimumLevel = $this->session->get(Protocol::SESSION_LOGGING_LEVEL, '');
        $minimumLevel = LoggingLevel::tryFrom($minimumLevel) ?? LoggingLevel::Warning;

        if ($this->getSeverityIndex($minimumLevel) > $this->getSeverityIndex($mcpLevel)) {
            return;
        }

        $this->client->log($mcpLevel, $message);
    }

    private function convertToMcpLevel($level): ?LoggingLevel
    {
        return match (strtolower((string) $level)) {
            'emergency' => LoggingLevel::Emergency,
            'alert' => LoggingLevel::Alert,
            'critical' => LoggingLevel::Critical,
            'error' => LoggingLevel::Error,
            'warning' => LoggingLevel::Warning,
            'notice' => LoggingLevel::Notice,
            'info' => LoggingLevel::Info,
            'debug' => LoggingLevel::Debug,
            default => null,
        };
    }

    private function getSeverityIndex(LoggingLevel $level): int
    {
        return match ($level) {
            LoggingLevel::Debug => 0,
            LoggingLevel::Info => 1,
            LoggingLevel::Notice => 2,
            LoggingLevel::Warning => 3,
            LoggingLevel::Error => 4,
            LoggingLevel::Critical => 5,
            LoggingLevel::Alert => 6,
            LoggingLevel::Emergency => 7,
        };
    }
}
