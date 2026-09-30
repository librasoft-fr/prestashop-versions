<?php



namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;


class ProgressIndicator
{
    private const FORMATS = [
        'normal' => ' %indicator% %message%',
        'normal_no_ansi' => ' %message%',

        'verbose' => ' %indicator% %message% (%elapsed:6s%)',
        'verbose_no_ansi' => ' %message% (%elapsed:6s%)',

        'very_verbose' => ' %indicator% %message% (%elapsed:6s%, %memory:6s%)',
        'very_verbose_no_ansi' => ' %message% (%elapsed:6s%, %memory:6s%)',
    ];

    private OutputInterface $output;
    private int $startTime;
    private ?string $format = null;
    private ?string $message = null;
    private array $indicatorValues;
    private int $indicatorCurrent;
    private int $indicatorChangeInterval;
    private float $indicatorUpdateTime;
    private bool $started = false;

    
    private static array $formatters;

    
    public function __construct(OutputInterface $output, ?string $format = null, int $indicatorChangeInterval = 100, ?array $indicatorValues = null)
    {
        $this->output = $output;

        $format ??= $this->determineBestFormat();
        $indicatorValues ??= ['-', '\\', '|', '/'];
        $indicatorValues = array_values($indicatorValues);

        if (2 > \count($indicatorValues)) {
            throw new InvalidArgumentException('Must have at least 2 indicator value characters.');
        }

        $this->format = self::getFormatDefinition($format);
        $this->indicatorChangeInterval = $indicatorChangeInterval;
        $this->indicatorValues = $indicatorValues;
        $this->startTime = time();
    }

    
    public function setMessage(?string $message)
    {
        $this->message = $message;

        $this->display();
    }

    
    public function start(string $message)
    {
        if ($this->started) {
            throw new LogicException('Progress indicator already started.');
        }

        $this->message = $message;
        $this->started = true;
        $this->startTime = time();
        $this->indicatorUpdateTime = $this->getCurrentTimeInMilliseconds() + $this->indicatorChangeInterval;
        $this->indicatorCurrent = 0;

        $this->display();
    }

    
    public function advance()
    {
        if (!$this->started) {
            throw new LogicException('Progress indicator has not yet been started.');
        }

        if (!$this->output->isDecorated()) {
            return;
        }

        $currentTime = $this->getCurrentTimeInMilliseconds();

        if ($currentTime < $this->indicatorUpdateTime) {
            return;
        }

        $this->indicatorUpdateTime = $currentTime + $this->indicatorChangeInterval;
        ++$this->indicatorCurrent;

        $this->display();
    }

    
    public function finish(string $message)
    {
        if (!$this->started) {
            throw new LogicException('Progress indicator has not yet been started.');
        }

        $this->message = $message;
        $this->display();
        if (!$this->output instanceof ConsoleSectionOutput) {
            $this->output->writeln('');
        }
        $this->started = false;
    }

    
    public static function getFormatDefinition(string $name): ?string
    {
        return self::FORMATS[$name] ?? null;
    }

    
    public static function setPlaceholderFormatterDefinition(string $name, callable $callable)
    {
        self::$formatters ??= self::initPlaceholderFormatters();

        self::$formatters[$name] = $callable;
    }

    
    public static function getPlaceholderFormatterDefinition(string $name): ?callable
    {
        self::$formatters ??= self::initPlaceholderFormatters();

        return self::$formatters[$name] ?? null;
    }

    private function display(): void
    {
        if (OutputInterface::VERBOSITY_QUIET === $this->output->getVerbosity()) {
            return;
        }

        $this->overwrite(preg_replace_callback("{%([a-z\-_]+)(?:\:([^%]+))?%}i", function ($matches) {
            if ($formatter = self::getPlaceholderFormatterDefinition($matches[1])) {
                return $formatter($this);
            }

            return $matches[0];
        }, $this->format ?? ''));
    }

    private function determineBestFormat(): string
    {
        return match ($this->output->getVerbosity()) {
            
            OutputInterface::VERBOSITY_VERBOSE => $this->output->isDecorated() ? 'verbose' : 'verbose_no_ansi',
            OutputInterface::VERBOSITY_VERY_VERBOSE,
            OutputInterface::VERBOSITY_DEBUG => $this->output->isDecorated() ? 'very_verbose' : 'very_verbose_no_ansi',
            default => $this->output->isDecorated() ? 'normal' : 'normal_no_ansi',
        };
    }

    
    private function overwrite(string $message): void
    {
        if ($this->output instanceof ConsoleSectionOutput) {
            $this->output->overwrite($message);
        } elseif ($this->output->isDecorated()) {
            $this->output->write("\x0D\x1B[2K");
            $this->output->write($message);
        } else {
            $this->output->writeln($message);
        }
    }

    private function getCurrentTimeInMilliseconds(): float
    {
        return round(microtime(true) * 1000);
    }

    
    private static function initPlaceholderFormatters(): array
    {
        return [
            'indicator' => static fn (self $indicator) => $indicator->indicatorValues[$indicator->indicatorCurrent % \count($indicator->indicatorValues)],
            'message' => static fn (self $indicator) => $indicator->message,
            'elapsed' => static fn (self $indicator) => Helper::formatTime(time() - $indicator->startTime, 2),
            'memory' => static fn () => Helper::formatMemory(memory_get_usage(true)),
        ];
    }
}
