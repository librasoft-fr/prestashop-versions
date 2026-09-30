<?php



namespace Symfony\Component\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Terminal;


class ConsoleSectionOutput extends StreamOutput
{
    private array $content = [];
    private int $lines = 0;
    private array $sections;
    private Terminal $terminal;
    private int $maxHeight = 0;

    
    public function __construct($stream, array &$sections, int $verbosity, bool $decorated, OutputFormatterInterface $formatter)
    {
        parent::__construct($stream, $verbosity, $decorated, $formatter);
        array_unshift($sections, $this);
        $this->sections = &$sections;
        $this->terminal = new Terminal();
    }

    
    public function setMaxHeight(int $maxHeight): void
    {
        
        $previousMaxHeight = $this->maxHeight;
        $this->maxHeight = $maxHeight;
        $existingContent = $this->popStreamContentUntilCurrentSection($previousMaxHeight ? min($previousMaxHeight, $this->lines) : $this->lines);

        parent::doWrite($this->getVisibleContent(), false);
        parent::doWrite($existingContent, false);
    }

    
    public function clear(?int $lines = null)
    {
        if (empty($this->content) || !$this->isDecorated()) {
            return;
        }

        if ($lines) {
            array_splice($this->content, -$lines);
        } else {
            $lines = $this->lines;
            $this->content = [];
        }

        $this->lines -= $lines;

        parent::doWrite($this->popStreamContentUntilCurrentSection($this->maxHeight ? min($this->maxHeight, $lines) : $lines), false);
    }

    
    public function overwrite(string|iterable $message)
    {
        if (!$this->content || !$this->isDecorated()) {
            $this->writeln($message);

            return;
        }

        
        
        
        $linesCleared = $this->lines;
        $this->content = [];
        $this->lines = 0;

        if (!is_iterable($message)) {
            $message = [$message];
        }

        foreach ($message as $line) {
            $this->addContent($this->getFormatter()->format($line) ?? '', true);
        }

        $erasedContent = $this->popStreamContentUntilCurrentSection($this->maxHeight ? min($this->maxHeight, $linesCleared) : $linesCleared);

        parent::doWrite($this->getVisibleContent(), false);
        parent::doWrite($erasedContent, false);
    }

    public function getContent(): string
    {
        return implode('', $this->content);
    }

    public function getVisibleContent(): string
    {
        if (0 === $this->maxHeight) {
            return $this->getContent();
        }

        return implode('', \array_slice($this->content, -$this->maxHeight));
    }

    
    public function addContent(string $input, bool $newline = true): int
    {
        $width = $this->terminal->getWidth();
        $lines = explode(\PHP_EOL, $input);
        $linesAdded = 0;
        $count = \count($lines) - 1;
        foreach ($lines as $i => $lineContent) {
            
            
            
            if ($i < $count || $newline) {
                $lineContent .= \PHP_EOL;
            }

            
            if ('' === $lineContent) {
                continue;
            }

            
            
            if (0 === $i
                && (false !== $lastLine = end($this->content))
                && !str_ends_with($lastLine, \PHP_EOL)
            ) {
                
                $this->lines -= (int) ceil($this->getDisplayLength($lastLine) / $width) ?: 1;
                
                $lineContent = $lastLine.$lineContent;
                
                array_splice($this->content, -1, 1, $lineContent);
            } else {
                
                $this->content[] = $lineContent;
            }

            $linesAdded += (int) ceil($this->getDisplayLength($lineContent) / $width) ?: 1;
        }

        $this->lines += $linesAdded;

        return $linesAdded;
    }

    
    public function addNewLineOfInputSubmit(): void
    {
        $this->content[] = \PHP_EOL;
        ++$this->lines;
    }

    
    protected function doWrite(string $message, bool $newline)
    {
        
        if (!$newline && str_ends_with($message, \PHP_EOL)) {
            $message = substr($message, 0, -\strlen(\PHP_EOL));
            $newline = true;
        }

        if (!$this->isDecorated()) {
            parent::doWrite($message, $newline);

            return;
        }

        
        
        $linesToClear = $deleteLastLine = ($lastLine = end($this->content) ?: '') && !str_ends_with($lastLine, \PHP_EOL) ? 1 : 0;

        $linesAdded = $this->addContent($message, $newline);

        if ($lineOverflow = $this->maxHeight > 0 && $this->lines > $this->maxHeight) {
            
            $linesToClear = $this->maxHeight;
        }

        $erasedContent = $this->popStreamContentUntilCurrentSection($linesToClear);

        if ($lineOverflow) {
            
            $previousLinesOfSection = \array_slice($this->content, $this->lines - $this->maxHeight, $this->maxHeight - $linesAdded);
            parent::doWrite(implode('', $previousLinesOfSection), false);
        }

        
        
        parent::doWrite($deleteLastLine ? $lastLine.$message : $message, true);
        parent::doWrite($erasedContent, false);
    }

    
    private function popStreamContentUntilCurrentSection(int $numberOfLinesToClearFromCurrentSection = 0): string
    {
        $numberOfLinesToClear = $numberOfLinesToClearFromCurrentSection;
        $erasedContent = [];

        foreach ($this->sections as $section) {
            if ($section === $this) {
                break;
            }

            $numberOfLinesToClear += $section->maxHeight ? min($section->lines, $section->maxHeight) : $section->lines;
            if ('' !== $sectionContent = $section->getVisibleContent()) {
                if (!str_ends_with($sectionContent, \PHP_EOL)) {
                    $sectionContent .= \PHP_EOL;
                }
                $erasedContent[] = $sectionContent;
            }
        }

        if ($numberOfLinesToClear > 0) {
            
            parent::doWrite(\sprintf("\x1b[%dA", $numberOfLinesToClear), false);
            
            parent::doWrite("\x1b[0J", false);
        }

        return implode('', array_reverse($erasedContent));
    }

    private function getDisplayLength(string $text): int
    {
        return Helper::width(Helper::removeDecoration($this->getFormatter(), str_replace("\t", '        ', $text)));
    }
}
