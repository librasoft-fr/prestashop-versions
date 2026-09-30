<?php



namespace Symfony\Component\Console\Helper;


final class TerminalInputHelper
{
    
    private $inputStream;
    private bool $isStdin;
    private string $initialState = '';
    private int $signalToKill = 0;
    private array $signalHandlers = [];
    private array $targetSignals = [];
    private bool $withStty;

    
    public function __construct($inputStream, bool $withStty = true)
    {
        $this->inputStream = $inputStream;
        $this->isStdin = 'php://stdin' === stream_get_meta_data($inputStream)['uri'];
        $this->withStty = $withStty;

        if ($withStty) {
            if (!\is_string($state = shell_exec('stty -g'))) {
                throw new \RuntimeException('Unable to read the terminal settings.');
            }

            $this->initialState = $state;

            $this->createSignalHandlers();
        }
    }

    
    public function waitForInput(): void
    {
        if ($this->isStdin) {
            $r = [$this->inputStream];
            $w = [];

            
            while (0 === @stream_select($r, $w, $w, 0, 100)) {
                $r = [$this->inputStream];
            }
        }

        if ($this->withStty) {
            $this->checkForKillSignal();
        }
    }

    
    public function finish(): void
    {
        if (!$this->withStty) {
            return;
        }

        
        $this->checkForKillSignal();
        shell_exec('stty '.$this->initialState);
        $this->signalToKill = 0;

        foreach ($this->signalHandlers as $signal => $originalHandler) {
            pcntl_signal($signal, $originalHandler);
        }
        $this->signalHandlers = [];
        $this->targetSignals = [];
    }

    private function createSignalHandlers(): void
    {
        if (!\function_exists('pcntl_async_signals') || !\function_exists('pcntl_signal')) {
            return;
        }

        pcntl_async_signals(true);
        $this->targetSignals = [\SIGINT, \SIGQUIT, \SIGTERM];

        foreach ($this->targetSignals as $signal) {
            $this->signalHandlers[$signal] = pcntl_signal_get_handler($signal);

            pcntl_signal($signal, function ($signal) {
                
                $currentState = shell_exec('stty -g');
                shell_exec('stty '.$this->initialState);
                $originalHandler = $this->signalHandlers[$signal];

                if (\is_callable($originalHandler)) {
                    $originalHandler($signal);
                    
                    shell_exec('stty '.$currentState);

                    return;
                }

                
                if (\SIG_DFL === $originalHandler) {
                    $this->signalToKill = $signal;
                }
            });
        }
    }

    private function checkForKillSignal(): void
    {
        if (\in_array($this->signalToKill, $this->targetSignals, true)) {
            
            if (\function_exists('posix_kill')) {
                pcntl_signal($this->signalToKill, \SIG_DFL);
                posix_kill(getmypid(), $this->signalToKill);
            }

            
            exit(128 + $this->signalToKill);
        }
    }
}
