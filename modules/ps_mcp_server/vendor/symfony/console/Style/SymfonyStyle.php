<?php



namespace Symfony\Component\Console\Style;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\OutputWrapper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\TrimmedBufferOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Terminal;


class SymfonyStyle extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;

    private InputInterface $input;
    private OutputInterface $output;
    private SymfonyQuestionHelper $questionHelper;
    private ProgressBar $progressBar;
    private int $lineLength;
    private TrimmedBufferOutput $bufferedOutput;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new TrimmedBufferOutput(\DIRECTORY_SEPARATOR === '\\' ? 4 : 2, $output->getVerbosity(), false, clone $output->getFormatter());
        
        $width = (new Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = min($width - (int) (\DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);

        parent::__construct($this->output = $output);
    }

    
    public function block(string|array $messages, ?string $type = null, ?string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = true)
    {
        $messages = \is_array($messages) ? array_values($messages) : [$messages];

        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, $escape));
        $this->newLine();
    }

    
    public function title(string $message)
    {
        $this->autoPrependBlock();
        $this->writeln([
            \sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            \sprintf('<comment>%s</>', str_repeat('=', Helper::width(Helper::removeDecoration($this->getFormatter(), $message)))),
        ]);
        $this->newLine();
    }

    
    public function section(string $message)
    {
        $this->autoPrependBlock();
        $this->writeln([
            \sprintf('<comment>%s</>', OutputFormatter::escapeTrailingBackslash($message)),
            \sprintf('<comment>%s</>', str_repeat('-', Helper::width(Helper::removeDecoration($this->getFormatter(), $message)))),
        ]);
        $this->newLine();
    }

    
    public function listing(array $elements)
    {
        $this->autoPrependText();
        $elements = array_map(static fn ($element) => \sprintf(' * %s', $element), $elements);

        $this->writeln($elements);
        $this->newLine();
    }

    
    public function text(string|array $message)
    {
        $this->autoPrependText();

        $messages = \is_array($message) ? array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(\sprintf(' %s', $message));
        }
    }

    
    public function comment(string|array $message)
    {
        $this->block($message, null, null, '<fg=default;bg=default> // </>', false, false);
    }

    
    public function success(string|array $message)
    {
        $this->block($message, 'OK', 'fg=black;bg=green', ' ', true);
    }

    
    public function error(string|array $message)
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', true);
    }

    
    public function warning(string|array $message)
    {
        $this->block($message, 'WARNING', 'fg=black;bg=yellow', ' ', true);
    }

    
    public function note(string|array $message)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }

    
    public function info(string|array $message)
    {
        $this->block($message, 'INFO', 'fg=green', ' ', true);
    }

    
    public function caution(string|array $message)
    {
        $this->block($message, 'CAUTION', 'fg=white;bg=red', ' ! ', true);
    }

    
    public function table(array $headers, array $rows)
    {
        $this->createTable()
            ->setHeaders($headers)
            ->setRows($rows)
            ->render()
        ;

        $this->newLine();
    }

    
    public function horizontalTable(array $headers, array $rows)
    {
        $this->createTable()
            ->setHorizontal(true)
            ->setHeaders($headers)
            ->setRows($rows)
            ->render()
        ;

        $this->newLine();
    }

    
    public function definitionList(string|array|TableSeparator ...$list)
    {
        $headers = [];
        $row = [];
        foreach ($list as $value) {
            if ($value instanceof TableSeparator) {
                $headers[] = $value;
                $row[] = $value;
                continue;
            }
            if (\is_string($value)) {
                $headers[] = new TableCell($value, ['colspan' => 2]);
                $row[] = null;
                continue;
            }
            if (!\is_array($value)) {
                throw new InvalidArgumentException('Value should be an array, string, or an instance of TableSeparator.');
            }
            $headers[] = key($value);
            $row[] = current($value);
        }

        $this->horizontalTable($headers, [$row]);
    }

    public function ask(string $question, ?string $default = null, ?callable $validator = null): mixed
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    public function askHidden(string $question, ?callable $validator = null): mixed
    {
        $question = new Question($question);

        $question->setHidden(true);
        $question->setValidator($validator);

        return $this->askQuestion($question);
    }

    public function confirm(string $question, bool $default = true): bool
    {
        return $this->askQuestion(new ConfirmationQuestion($question, $default));
    }

    public function choice(string $question, array $choices, mixed $default = null, bool $multiSelect = false): mixed
    {
        if (null !== $default) {
            $values = array_flip($choices);
            $default = $values[$default] ?? $default;
        }

        $questionChoice = new ChoiceQuestion($question, $choices, $default);
        $questionChoice->setMultiselect($multiSelect);

        return $this->askQuestion($questionChoice);
    }

    
    public function progressStart(int $max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }

    
    public function progressAdvance(int $step = 1)
    {
        $this->getProgressBar()->advance($step);
    }

    
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        unset($this->progressBar);
    }

    public function createProgressBar(int $max = 0): ProgressBar
    {
        $progressBar = parent::createProgressBar($max);

        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === getenv('TERM_PROGRAM')) {
            $progressBar->setEmptyBarCharacter('░'); 
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); 
        }

        return $progressBar;
    }

    
    public function progressIterate(iterable $iterable, ?int $max = null): iterable
    {
        yield from $this->createProgressBar()->iterate($iterable, $max);

        $this->newLine(2);
    }

    public function askQuestion(Question $question): mixed
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }

        $this->questionHelper ??= new SymfonyQuestionHelper();

        $answer = $this->questionHelper->ask($this->input, $this, $question);

        if ($this->input->isInteractive()) {
            if ($this->output instanceof ConsoleSectionOutput) {
                
                
                $this->output->addNewLineOfInputSubmit();
            }
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }

        return $answer;
    }

    
    public function writeln(string|iterable $messages, int $type = self::OUTPUT_NORMAL)
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::writeln($message, $type);
            $this->writeBuffer($message, true, $type);
        }
    }

    
    public function write(string|iterable $messages, bool $newline = false, int $type = self::OUTPUT_NORMAL)
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            parent::write($message, $newline, $type);
            $this->writeBuffer($message, $newline, $type);
        }
    }

    
    public function newLine(int $count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(str_repeat("\n", $count));
    }

    
    public function getErrorStyle(): self
    {
        return new self($this->input, $this->getErrorOutput());
    }

    public function createTable(): Table
    {
        $output = $this->output instanceof ConsoleOutputInterface ? $this->output->section() : $this->output;
        $style = clone Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');

        return (new Table($output))->setStyle($style);
    }

    private function getProgressBar(): ProgressBar
    {
        return $this->progressBar
            ?? throw new RuntimeException('The ProgressBar is not started.');
    }

    private function autoPrependBlock(): void
    {
        $chars = substr(str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);

        if (!isset($chars[0])) {
            $this->newLine(); 

            return;
        }
        
        $this->newLine(2 - substr_count($chars, "\n"));
    }

    private function autoPrependText(): void
    {
        $fetched = $this->bufferedOutput->fetch();
        
        if ($fetched && !str_ends_with($fetched, "\n")) {
            $this->newLine();
        }
    }

    private function writeBuffer(string $message, bool $newLine, int $type): void
    {
        
        $this->bufferedOutput->write($message, $newLine, $type);
    }

    private function createBlock(iterable $messages, ?string $type = null, ?string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = false): array
    {
        $indentLength = 0;
        $prefixLength = Helper::width(Helper::removeDecoration($this->getFormatter(), $prefix));
        $lines = [];

        if (null !== $type) {
            $type = \sprintf('[%s] ', $type);
            $indentLength = Helper::width($type);
            $lineIndentation = str_repeat(' ', $indentLength);
        }

        
        $outputWrapper = new OutputWrapper();
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = OutputFormatter::escape($message);
            }

            $message = str_replace("\r\n", "\n", $message);

            $lines = array_merge(
                $lines,
                explode("\n", $outputWrapper->wrap(
                    $message,
                    $this->lineLength - $prefixLength - $indentLength,
                    "\n"
                ))
            );

            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }

        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            array_unshift($lines, '');
            $lines[] = '';
        }

        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = $firstLineIndex === $i ? $type.$line : $lineIndentation.$line;
            }

            $line = $prefix.$line;
            $line .= str_repeat(' ', max($this->lineLength - Helper::width(Helper::removeDecoration($this->getFormatter(), $line)), 0));

            if ($style) {
                $line = \sprintf('<%s>%s</>', $style, $line);
            }
        }

        return $lines;
    }
}
