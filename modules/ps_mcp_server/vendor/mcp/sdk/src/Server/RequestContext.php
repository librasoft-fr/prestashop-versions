<?php



namespace Mcp\Server;

use Mcp\Capability\Logger\ClientLogger;
use Mcp\Schema\JsonRpc\Request;
use Mcp\Server\Session\SessionInterface;


final class RequestContext
{
    private ?ClientGateway $clientGateway = null;
    private ?ClientLogger $clientLogger = null;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly Request $request,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getClientGateway(): ClientGateway
    {
        if (null == $this->clientGateway) {
            $this->clientGateway = new ClientGateway($this->session);
        }

        return $this->clientGateway;
    }

    public function getClientLogger(): ClientLogger
    {
        if (null === $this->clientLogger) {
            $this->clientLogger = new ClientLogger($this->getClientGateway(), $this->session);
        }

        return $this->clientLogger;
    }
}
