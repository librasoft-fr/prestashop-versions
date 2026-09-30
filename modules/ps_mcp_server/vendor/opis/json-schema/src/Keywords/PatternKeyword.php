<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{Helper, ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class PatternKeyword implements Keyword
{
    use ErrorTrait;

    protected ?string $pattern;

    protected ?string $regex;

    
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
        $this->regex = Helper::patternToRegex($pattern);
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        if (preg_match($this->regex, $context->currentData())) {
            return null;
        }

        return $this->error($schema, $context, 'pattern', "The string should match pattern: {pattern}", [
            'pattern' => $this->pattern,
        ]);
    }
}