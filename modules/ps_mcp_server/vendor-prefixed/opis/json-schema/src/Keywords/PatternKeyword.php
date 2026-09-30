<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Errors\ValidationError;
use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Schema;
use PsMcpServerDeps\Opis\JsonSchema\ValidationContext;

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

        return $this->error($schema, $context, 'pattern', 'The string should match pattern: {pattern}', [
            'pattern' => $this->pattern,
        ]);
    }
}
