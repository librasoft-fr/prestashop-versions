<?php



namespace Mcp;

use Mcp\Server\Builder;
use Mcp\Server\Protocol;
use Mcp\Server\Transport\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


final class Server
{
    public function __construct(
        private readonly Protocol $protocol,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public static function builder(): Builder
    {
        return new Builder();
    }

    
    public function run(TransportInterface $transport): mixed
    {
        $transport->initialize();

        $this->protocol->connect($transport);

        $this->logger->info('Running server...');

        try {
            return $transport->listen();
        } finally {
            $transport->close();
        }
    }
}
