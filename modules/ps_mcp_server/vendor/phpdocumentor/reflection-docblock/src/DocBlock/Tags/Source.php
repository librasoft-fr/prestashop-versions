<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

use function preg_match;


final class Source extends BaseTag implements Factory\StaticMethod
{
    protected string $name = 'source';

    
    private int $startingLine;

    
    private ?int $lineCount = null;

    
    public function __construct($startingLine, $lineCount = null, ?Description $description = null)
    {
        Assert::integerish($startingLine);
        Assert::nullOrIntegerish($lineCount);

        $this->startingLine = (int) $startingLine;
        $this->lineCount    = $lineCount !== null ? (int) $lineCount : null;
        $this->description  = $description;
    }

    public static function create(
        string $body,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ): self {
        Assert::stringNotEmpty($body);
        Assert::notNull($descriptionFactory);

        $startingLine = 1;
        $lineCount    = null;
        $description  = null;

        
        if (preg_match('/^([1-9]\d*)\s*(?:((?1))\s+)?(.*)$/sux', $body, $matches)) {
            $startingLine = (int) $matches[1];
            if (isset($matches[2]) && $matches[2] !== '') {
                $lineCount = (int) $matches[2];
            }

            $description = $matches[3];
        }

        return new static($startingLine, $lineCount, $descriptionFactory->create($description ?? '', $context));
    }

    
    public function getStartingLine(): int
    {
        return $this->startingLine;
    }

    
    public function getLineCount(): ?int
    {
        return $this->lineCount;
    }

    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        $startingLine = (string) $this->startingLine;

        $lineCount = $this->lineCount !== null ? ' ' . $this->lineCount : '';

        return $startingLine
            . $lineCount
            . ($description !== ''
                ? ' ' . $description
                : '');
    }
}
