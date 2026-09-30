<?php



namespace Symfony\Component\Console\SignalRegistry;

final class SignalRegistry
{
    
    private array $signalHandlers = [];

    
    private array $stack = [];

    
    private array $originalHandlers = [];

    public function __construct()
    {
        if (\function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }
    }

    public function register(int $signal, callable $signalHandler): void
    {
        $previous = pcntl_signal_get_handler($signal);

        if (!isset($this->originalHandlers[$signal])) {
            $this->originalHandlers[$signal] = $previous;
        }

        if (!isset($this->signalHandlers[$signal])) {
            if (\is_callable($previous) && [$this, 'handle'] !== $previous) {
                $this->signalHandlers[$signal][] = $previous;
            }
        }

        $this->signalHandlers[$signal][] = $signalHandler;

        pcntl_signal($signal, [$this, 'handle']);
    }

    public static function isSupported(): bool
    {
        return \function_exists('pcntl_signal');
    }

    
    public function handle(int $signal): void
    {
        $count = \count($this->signalHandlers[$signal]);

        foreach ($this->signalHandlers[$signal] as $i => $signalHandler) {
            $hasNext = $i !== $count - 1;
            $signalHandler($signal, $hasNext);
        }
    }

    
    public function pushCurrentHandlers(): void
    {
        
        
        foreach ($this->signalHandlers as $signal => $handlers) {
            if (isset($this->originalHandlers[$signal])) {
                pcntl_signal($signal, $this->originalHandlers[$signal]);
            }
        }

        $this->stack[] = $this->signalHandlers;
        $this->signalHandlers = [];
    }

    
    public function popPreviousHandlers(): void
    {
        $popped = $this->signalHandlers;
        $previous = array_pop($this->stack) ?? [];

        
        
        $this->signalHandlers = $previous + $popped;

        
        foreach ($previous as $signal => $handlers) {
            pcntl_signal($signal, [$this, 'handle']);
        }

        
        foreach ($popped as $signal => $handlers) {
            if (!isset($previous[$signal]) && isset($this->originalHandlers[$signal])) {
                pcntl_signal($signal, $this->originalHandlers[$signal]);
            }
        }

        $this->signalHandlers = $previous;
    }
}
