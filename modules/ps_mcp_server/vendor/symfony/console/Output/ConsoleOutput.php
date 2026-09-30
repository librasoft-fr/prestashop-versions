<?php



namespace Symfony\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;


class ConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    private OutputInterface $stderr;
    private array $consoleSectionOutputs = [];

    
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, ?bool $decorated = null, ?OutputFormatterInterface $formatter = null)
    {
        parent::__construct($this->openOutputStream(), $verbosity, $decorated, $formatter);

        if (null === $formatter) {
            
            $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated);

            return;
        }

        $actualDecorated = $this->isDecorated();
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());

        if (null === $decorated) {
            $this->setDecorated($actualDecorated && $this->stderr->isDecorated());
        }
    }

    
    public function section(): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput($this->getStream(), $this->consoleSectionOutputs, $this->getVerbosity(), $this->isDecorated(), $this->getFormatter());
    }

    
    public function setDecorated(bool $decorated)
    {
        parent::setDecorated($decorated);
        $this->stderr->setDecorated($decorated);
    }

    
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->stderr->setFormatter($formatter);
    }

    
    public function setVerbosity(int $level)
    {
        parent::setVerbosity($level);
        $this->stderr->setVerbosity($level);
    }

    public function getErrorOutput(): OutputInterface
    {
        return $this->stderr;
    }

    
    public function setErrorOutput(OutputInterface $error)
    {
        $this->stderr = $error;
    }

    
    protected function hasStdoutSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    
    protected function hasStderrSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    
    private function isRunningOS400(): bool
    {
        $checks = [
            \function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            \PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }

    
    private function openOutputStream()
    {
        static $stdout;

        if ($stdout) {
            return $stdout;
        }

        if (!$this->hasStdoutSupport()) {
            return $stdout = fopen('php://output', 'w');
        }

        
        if (!\defined('STDOUT')) {
            return $stdout = @fopen('php://stdout', 'w') ?: fopen('php://output', 'w');
        }

        
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return $stdout = @fopen('php://stdout', 'w') ?: \STDOUT;
        }

        return $stdout = \STDOUT;
    }

    
    private function openErrorStream()
    {
        static $stderr;

        if ($stderr) {
            return $stderr;
        }

        if (!$this->hasStderrSupport()) {
            return $stderr = fopen('php://output', 'w');
        }

        
        if (!\defined('STDERR')) {
            return $stderr = @fopen('php://stderr', 'w') ?: fopen('php://output', 'w');
        }

        
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return $stderr = @fopen('php://stderr', 'w') ?: \STDERR;
        }

        return $stderr ??= \STDERR;
    }
}
