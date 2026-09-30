<?php

namespace PsMcpServerDeps\Mcp\Server\Session;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

class SessionManager implements SessionManagerInterface
{
    public function __construct(
        private readonly SessionStoreInterface $store,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function create(): SessionInterface
    {
        return new Session($this->store, Uuid::v4());
    }

    public function createWithId(Uuid $id): SessionInterface
    {
        return new Session($this->store, $id);
    }

    public function exists(Uuid $id): bool
    {
        return $this->store->exists($id);
    }

    public function destroy(Uuid $id): bool
    {
        return $this->store->destroy($id);
    }

    public function gc(): void
    {
        if (random_int(0, 100) > 1) {
            return;
        }

        $deletedSessions = $this->store->gc();
        if (!empty($deletedSessions)) {
            $this->logger->debug('Garbage collected expired sessions.', [
                'count' => \count($deletedSessions),
                'session_ids' => array_map(static fn (Uuid $id) => $id->toRfc4122(), $deletedSessions),
            ]);
        }
    }
}
