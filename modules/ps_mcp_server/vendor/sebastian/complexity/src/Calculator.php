<?php declare(strict_types=1);

namespace SebastianBergmann\Complexity;

use function assert;
use function file_get_contents;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\ParserFactory;

final class Calculator
{
    
    public function calculateForSourceFile(string $sourceFile): ComplexityCollection
    {
        return $this->calculateForSourceString(file_get_contents($sourceFile));
    }

    
    public function calculateForSourceString(string $source): ComplexityCollection
    {
        try {
            $nodes = (new ParserFactory)->createForHostVersion()->parse($source);

            assert($nodes !== null);

            return $this->calculateForAbstractSyntaxTree($nodes);

            
        } catch (Error $error) {
            throw new RuntimeException(
                $error->getMessage(),
                $error->getCode(),
                $error,
            );
        }
        
    }

    
    public function calculateForAbstractSyntaxTree(array $nodes): ComplexityCollection
    {
        $traverser                    = new NodeTraverser;
        $complexityCalculatingVisitor = new ComplexityCalculatingVisitor(true);

        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor(new ParentConnectingVisitor);
        $traverser->addVisitor($complexityCalculatingVisitor);

        try {
            
            $traverser->traverse($nodes);
            
        } catch (Error $error) {
            throw new RuntimeException(
                $error->getMessage(),
                $error->getCode(),
                $error,
            );
        }
        

        return $complexityCalculatingVisitor->result();
    }
}
