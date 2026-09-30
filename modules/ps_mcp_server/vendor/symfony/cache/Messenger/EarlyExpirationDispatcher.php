<?php



namespace Symfony\Component\Cache\Messenger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DependencyInjection\ReverseContainer;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;


class EarlyExpirationDispatcher
{
    private MessageBusInterface $bus;
    private ReverseContainer $reverseContainer;
    private ?\Closure $callbackWrapper;

    public function __construct(MessageBusInterface $bus, ReverseContainer $reverseContainer, ?callable $callbackWrapper = null)
    {
        $this->bus = $bus;
        $this->reverseContainer = $reverseContainer;
        $this->callbackWrapper = null === $callbackWrapper ? null : $callbackWrapper(...);
    }

    
    public function __invoke(callable $callback, CacheItem $item, bool &$save, AdapterInterface $pool, \Closure $setMetadata, ?LoggerInterface $logger = null, ?float $beta = null)
    {
        if (!$item->isHit() || null === $message = EarlyExpirationMessage::create($this->reverseContainer, $callback, $item, $pool)) {
            
            $logger?->info('Computing item "{key}" online: '.($item->isHit() ? 'callback cannot be reversed' : 'item is stale'), ['key' => $item->getKey()]);

            return null !== $this->callbackWrapper ? ($this->callbackWrapper)($callback, $item, $save, $pool, $setMetadata, $logger, $beta) : $callback($item, $save);
        }

        $envelope = $this->bus->dispatch($message);

        if ($logger) {
            if ($envelope->last(HandledStamp::class)) {
                $logger->info('Item "{key}" was computed online', ['key' => $item->getKey()]);
            } else {
                $logger->info('Item "{key}" sent for recomputation', ['key' => $item->getKey()]);
            }
        }

        
        $save = false;

        return $message->getItem()->get() ?? $item->get();
    }
}
