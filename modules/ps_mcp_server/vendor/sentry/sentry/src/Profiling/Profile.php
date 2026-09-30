<?php

declare(strict_types=1);

namespace Sentry\Profiling;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sentry\Context\OsContext;
use Sentry\Context\RuntimeContext;
use Sentry\Event;
use Sentry\EventId;
use Sentry\Options;
use Sentry\Util\PrefixStripper;
use Sentry\Util\SentryUid;


final class Profile
{
    use PrefixStripper;

    
    private const VERSION = '1';

    
    private const THREAD_ID = '0';

    
    private const MIN_SAMPLE_COUNT = 2;

    
    private const MAX_PROFILE_DURATION = 30;

    
    private $startTimeStamp;

    
    private $excimerLog;

    
    private $eventId;

    
    private $options;

    
    private $logger;

    public function __construct(?Options $options = null)
    {
        $this->options = $options;
        $this->logger = $options !== null ? $options->getLoggerOrNullLogger() : new NullLogger();
    }

    public function setStartTimeStamp(float $startTimeStamp): void
    {
        $this->startTimeStamp = $startTimeStamp;
    }

    
    public function setExcimerLog($excimerLog): void
    {
        $this->excimerLog = $excimerLog;
    }

    public function setEventId(EventId $eventId): void
    {
        $this->eventId = $eventId;
    }

    
    public function getFormattedData(Event $event): ?array
    {
        if (!$this->validateExcimerLog()) {
            $this->logger->warning('The profile does not contain enough samples, the profile will be discarded.');

            return null;
        }

        $osContext = $event->getOsContext();
        if (!$this->validateOsContext($osContext)) {
            $this->logger->warning('The OS context is not missing or invalid, the profile will be discarded.');

            return null;
        }

        $runtimeContext = $event->getRuntimeContext();
        if (!$this->validateRuntimeContext($runtimeContext)) {
            $this->logger->warning('The runtime context is not missing or invalid, the profile will be discarded.');

            return null;
        }

        if (!$this->validateEvent($event)) {
            $this->logger->warning('The event is missing a transaction and/or trace ID, the profile will be discarded.');

            return null;
        }

        $frames = [];
        $frameHashMap = [];

        $stacks = [];
        $stackHashMap = [];

        $registerStack = static function (array $stack) use (&$stacks, &$stackHashMap): int {
            $stackHash = md5(serialize($stack));

            if (\array_key_exists($stackHash, $stackHashMap) === false) {
                $stackHashMap[$stackHash] = \count($stacks);
                $stacks[] = $stack;
            }

            return $stackHashMap[$stackHash];
        };

        $samples = [];

        $duration = 0;

        $loggedStacks = $this->prepareStacks();
        foreach ($loggedStacks as $stack) {
            $stackFrames = [];

            foreach ($stack['trace'] as $frame) {
                $absolutePath = $frame['file'];
                $lineno = $frame['line'];

                $frameKey = "{$absolutePath}:{$lineno}";

                $frameIndex = $frameHashMap[$frameKey] ?? null;

                if ($frameIndex === null) {
                    $file = $this->stripPrefixFromFilePath($this->options, $absolutePath);
                    $module = null;

                    if (isset($frame['class'], $frame['function'])) {
                        
                        $function = $frame['class'] . '::' . $frame['function'];
                        $module = $frame['class'];
                    } elseif (isset($frame['function'])) {
                        
                        $function = $frame['function'];
                    } else {
                        
                        $function = $file;
                    }

                    $frameHashMap[$frameKey] = $frameIndex = \count($frames);
                    $frames[] = [
                        'filename' => $file,
                        'abs_path' => $absolutePath,
                        'module' => $module,
                        'function' => $function,
                        'lineno' => $lineno,
                    ];
                }

                $stackFrames[] = $frameIndex;
            }

            $stackId = $registerStack($stackFrames);

            $duration = $stack['timestamp'];

            $samples[] = [
                'stack_id' => $stackId,
                'thread_id' => self::THREAD_ID,
                'elapsed_since_start_ns' => (int) round($duration * 1e+9),
            ];
        }

        if (!$this->validateMaxDuration((float) $duration)) {
            $this->logger->warning(\sprintf('The profile is %ss which is longer than the allowed %ss, the profile will be discarded.', (float) $duration, self::MAX_PROFILE_DURATION));

            return null;
        }

        $startTime = \DateTime::createFromFormat('U.u', number_format($this->startTimeStamp, 4, '.', ''), new \DateTimeZone('UTC'));
        if ($startTime === false) {
            $this->logger->warning(\sprintf('The start time (%s) of the profile is not valid, the profile will be discarded.', $this->startTimeStamp));

            return null;
        }

        return [
            'device' => [
                'architecture' => $osContext->getMachineType(),
            ],
            'event_id' => $this->eventId ? (string) $this->eventId : SentryUid::generate(),
            'os' => [
                'name' => $osContext->getName(),
                'version' => $osContext->getVersion(),
                'build_number' => $osContext->getBuild() ?? '',
            ],
            'platform' => 'php',
            'release' => $event->getRelease() ?? '',
            'environment' => $event->getEnvironment() ?? Event::DEFAULT_ENVIRONMENT,
            'runtime' => [
                'name' => $runtimeContext->getName(),
                'sapi' => $runtimeContext->getSAPI(),
                'version' => $runtimeContext->getVersion(),
            ],
            'timestamp' => $startTime->format(\DATE_RFC3339_EXTENDED),
            'transaction' => [
                'id' => (string) $event->getId(),
                'name' => $event->getTransaction(),
                'trace_id' => $event->getTraceId(),
                'active_thread_id' => self::THREAD_ID,
            ],
            'version' => self::VERSION,
            'profile' => [
                'frames' => $frames,
                'samples' => $samples,
                'stacks' => $stacks,
            ],
        ];
    }

    
    private function prepareStacks(): array
    {
        $stacks = [];

        foreach ($this->excimerLog as $stack) {
            if ($stack instanceof \ExcimerLogEntry) {
                $stacks[] = [
                    'trace' => $stack->getTrace(),
                    'timestamp' => $stack->getTimestamp(),
                ];
            } else {
                
                $stacks[] = $stack;
            }
        }

        return $stacks;
    }

    private function validateExcimerLog(): bool
    {
        if (\is_array($this->excimerLog)) {
            $sampleCount = \count($this->excimerLog);
        } else {
            $sampleCount = $this->excimerLog->count();
        }

        return $sampleCount >= self::MIN_SAMPLE_COUNT;
    }

    private function validateMaxDuration(float $duration): bool
    {
        if ($duration > self::MAX_PROFILE_DURATION) {
            return false;
        }

        return true;
    }

    
    private function validateOsContext(?OsContext $osContext): bool
    {
        if ($osContext === null) {
            return false;
        }

        if ($osContext->getVersion() === null) {
            return false;
        }

        if ($osContext->getMachineType() === null) {
            return false;
        }

        return true;
    }

    
    private function validateRuntimeContext(?RuntimeContext $runtimeContext): bool
    {
        if ($runtimeContext === null) {
            return false;
        }

        if ($runtimeContext->getVersion() === null) {
            return false;
        }

        return true;
    }

    
    private function validateEvent(Event $event): bool
    {
        if ($event->getTransaction() === null) {
            return false;
        }

        if ($event->getTraceId() === null) {
            return false;
        }

        return true;
    }
}
