<?php

declare(strict_types=1);

namespace Sentry\State;

use Sentry\Breadcrumb;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Options;
use Sentry\Severity;
use Sentry\Tracing\DynamicSamplingContext;
use Sentry\Tracing\PropagationContext;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;
use Sentry\UserDataBag;


class Scope
{
    
    public const MAX_FLAGS = 100;

    
    private $propagationContext;

    
    private $breadcrumbs = [];

    
    private $user;

    
    private $contexts = [];

    
    private $tags = [];

    
    private $flags = [];

    
    private $extra = [];

    
    private $fingerprint = [];

    
    private $level;

    
    private $eventProcessors = [];

    
    private $span;

    
    private static $globalEventProcessors = [];

    
    private static $externalPropagationContextCallback;

    public function __construct(?PropagationContext $propagationContext = null)
    {
        $this->propagationContext = $propagationContext ?? PropagationContext::fromDefaults();
    }

    
    public function setTag(string $key, string $value): self
    {
        $this->tags[$key] = $value;

        return $this;
    }

    
    public function setTags(array $tags): self
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    
    public function removeTag(string $key): self
    {
        unset($this->tags[$key]);

        return $this;
    }

    
    public function addFeatureFlag(string $key, bool $result): self
    {
        
        
        foreach ($this->flags as $flagIndex => $flag) {
            if (isset($flag[$key])) {
                unset($this->flags[$flagIndex]);
            }
        }

        
        if (\count($this->flags) >= self::MAX_FLAGS) {
            array_shift($this->flags);
        }

        $this->flags[] = [$key => $result];

        if ($this->span !== null) {
            $this->span->setFlag($key, $result);
        }

        return $this;
    }

    
    public function setContext(string $name, array $value): self
    {
        if (!empty($value)) {
            $this->contexts[$name] = $value;
        }

        return $this;
    }

    
    public function removeContext(string $name): self
    {
        unset($this->contexts[$name]);

        return $this;
    }

    
    public function setExtra(string $key, $value): self
    {
        $this->extra[$key] = $value;

        return $this;
    }

    
    public function setExtras(array $extras): self
    {
        $this->extra = array_merge($this->extra, $extras);

        return $this;
    }

    
    public function getUser(): ?UserDataBag
    {
        return $this->user;
    }

    
    public function setUser($user): self
    {
        if (!\is_array($user) && !$user instanceof UserDataBag) {
            throw new \TypeError(\sprintf('The $user argument must be either an array or an instance of the "%s" class. Got: "%s".', UserDataBag::class, get_debug_type($user)));
        }

        if (\is_array($user)) {
            $user = UserDataBag::createFromArray($user);
        }

        if ($this->user === null) {
            $this->user = $user;
        } else {
            $this->user = $this->user->merge($user);
        }

        return $this;
    }

    
    public function removeUser(): self
    {
        $this->user = null;

        return $this;
    }

    
    public function setFingerprint(array $fingerprint): self
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    
    public function setLevel(?Severity $level): self
    {
        $this->level = $level;

        return $this;
    }

    
    public function addBreadcrumb(Breadcrumb $breadcrumb, int $maxBreadcrumbs = 100): self
    {
        $this->breadcrumbs[] = $breadcrumb;
        $this->breadcrumbs = \array_slice($this->breadcrumbs, -$maxBreadcrumbs);

        return $this;
    }

    
    public function clearBreadcrumbs(): self
    {
        $this->breadcrumbs = [];

        return $this;
    }

    
    public function addEventProcessor(callable $eventProcessor): self
    {
        $this->eventProcessors[] = $eventProcessor;

        return $this;
    }

    
    public static function addGlobalEventProcessor(callable $eventProcessor): void
    {
        self::$globalEventProcessors[] = $eventProcessor;
    }

    public static function registerExternalPropagationContext(callable $callback): void
    {
        self::$externalPropagationContextCallback = $callback;
    }

    public static function clearExternalPropagationContext(): void
    {
        self::$externalPropagationContextCallback = null;
    }

    
    public static function getExternalPropagationContext(): ?array
    {
        $callback = self::$externalPropagationContextCallback;
        if (!\is_callable($callback)) {
            return null;
        }

        try {
            $context = $callback();
        } catch (\Throwable $exception) {
            return null;
        }

        if (!\is_array($context)) {
            return null;
        }

        $traceId = $context['trace_id'] ?? null;
        $spanId = $context['span_id'] ?? null;

        if (!\is_string($traceId) || preg_match('/^[0-9a-f]{32}$/i', $traceId) !== 1) {
            return null;
        }

        if (!\is_string($spanId) || preg_match('/^[0-9a-f]{16}$/i', $spanId) !== 1) {
            return null;
        }

        return [
            'trace_id' => $traceId,
            'span_id' => $spanId,
        ];
    }

    
    public function clear(): self
    {
        $this->user = null;
        $this->level = null;
        $this->span = null;
        $this->fingerprint = [];
        $this->breadcrumbs = [];
        $this->tags = [];
        $this->flags = [];
        $this->extra = [];
        $this->contexts = [];

        return $this;
    }

    
    public function applyToEvent(Event $event, ?EventHint $hint = null, ?Options $options = null): ?Event
    {
        $event->setFingerprint(array_merge($event->getFingerprint(), $this->fingerprint));

        if (empty($event->getBreadcrumbs())) {
            $event->setBreadcrumb($this->breadcrumbs);
        }

        if ($this->level !== null) {
            $event->setLevel($this->level);
        }

        if (!empty($this->tags)) {
            $event->setTags(array_merge($this->tags, $event->getTags()));
        }

        if (!empty($this->flags)) {
            $event->setContext('flags', [
                'values' => array_map(static function (array $flag) {
                    return [
                        'flag' => key($flag),
                        'result' => current($flag),
                    ];
                }, array_values($this->flags)),
            ]);
        }

        if (!empty($this->extra)) {
            $event->setExtra(array_merge($this->extra, $event->getExtra()));
        }

        if ($this->user !== null) {
            $user = $event->getUser();

            if ($user === null) {
                $user = $this->user;
            } else {
                $user = $this->user->merge($user);
            }

            $event->setUser($user);
        }

        
        $externalPropagationContext = null;
        if ($this->span === null) {
            $externalPropagationContext = self::getExternalPropagationContext();
        }

        $traceContext = $this->span !== null
            ? $this->span->getTraceContext()
            : ($externalPropagationContext ?? $this->propagationContext->getTraceContext());

        if (!\array_key_exists('trace', $event->getContexts())) {
            $event->setContext('trace', $traceContext);
        }

        if ($this->span !== null) {
            
            $transaction = $this->span->getTransaction();
            if ($transaction !== null) {
                $event->setSdkMetadata('dynamic_sampling_context', $transaction->getDynamicSamplingContext());
            }
        } elseif ($externalPropagationContext === null) {
            $dynamicSamplingContext = $this->propagationContext->getDynamicSamplingContext();
            if ($dynamicSamplingContext === null && $options !== null) {
                $dynamicSamplingContext = DynamicSamplingContext::fromOptions($options, $this);
            }
            $event->setSdkMetadata('dynamic_sampling_context', $dynamicSamplingContext);
        }

        foreach (array_merge($this->contexts, $event->getContexts()) as $name => $data) {
            $event->setContext($name, $data);
        }

        
        if ($hint === null) {
            $hint = new EventHint();
        }

        foreach (array_merge(self::$globalEventProcessors, $this->eventProcessors) as $processor) {
            $event = $processor($event, $hint);

            if ($event === null) {
                return null;
            }

            if (!$event instanceof Event) {
                throw new \InvalidArgumentException(\sprintf('The event processor must return null or an instance of the %s class', Event::class));
            }
        }

        return $event;
    }

    
    public function getSpan(): ?Span
    {
        return $this->span;
    }

    
    public function setSpan(?Span $span): self
    {
        $this->span = $span;

        return $this;
    }

    
    public function getTransaction(): ?Transaction
    {
        if ($this->span !== null) {
            return $this->span->getTransaction();
        }

        return null;
    }

    public function hasExternalPropagationContext(): bool
    {
        return $this->span === null && self::getExternalPropagationContext() !== null;
    }

    
    public function getTraceContext(): array
    {
        if ($this->span !== null) {
            return $this->span->getTraceContext();
        }

        return self::getExternalPropagationContext() ?? $this->propagationContext->getTraceContext();
    }

    public function getPropagationContext(): PropagationContext
    {
        return $this->propagationContext;
    }

    public function setPropagationContext(PropagationContext $propagationContext): self
    {
        $this->propagationContext = $propagationContext;

        return $this;
    }

    public function __clone()
    {
        if ($this->user !== null) {
            $this->user = clone $this->user;
        }
        if ($this->propagationContext !== null) {
            $this->propagationContext = clone $this->propagationContext;
        }
    }
}
