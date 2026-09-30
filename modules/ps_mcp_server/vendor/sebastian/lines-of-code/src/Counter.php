<?php declare(strict_types=1);

namespace SebastianBergmann\LinesOfCode;

use function assert;
use function file_get_contents;
use function substr_count;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

final class Counter
{
    
    public function countInSourceFile(string $sourceFile): LinesOfCode
    {
        return $this->countInSourceString(file_get_contents($sourceFile));
    }

    
    public function countInSourceString(string $source): LinesOfCode
    {
        $linesOfCode = substr_count($source, "\n");

        if ($linesOfCode === 0 && !empty($source)) {
            $linesOfCode = 1;
        }

        assert($linesOfCode >= 0);

        try {
            $nodes = (new ParserFactory)->createForHostVersion()->parse($source);

            assert($nodes !== null);

            return $this->countInAbstractSyntaxTree($linesOfCode, $nodes);

            
        } catch (Error $error) {
            throw new RuntimeException(
                $error->getMessage(),
                $error->getCode(),
                $error,
            );
        }
        
    }

    
    public function countInAbstractSyntaxTree(int $linesOfCode, array $nodes): LinesOfCode
    {
        $traverser = new NodeTraverser;
        $visitor   = new LineCountingVisitor($linesOfCode);

        $traverser->addVisitor($visitor);

        try {
            
            $traverser->traverse($nodes);
            
        } catch (Error $error) {
            throw new RuntimeException(
                $error->getMessage(),
                $error->getCode(),
                $error,
            );
        }
        

        return $visitor->result();
    }
}
