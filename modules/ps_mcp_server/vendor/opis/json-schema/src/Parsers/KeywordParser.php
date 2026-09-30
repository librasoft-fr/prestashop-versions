<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;

abstract class KeywordParser
{
    const TYPE_PREPEND = '_prepend';
    const TYPE_BEFORE = '_before';
    const TYPE_AFTER = '_after';
    const TYPE_APPEND = '_append';

    const TYPE_AFTER_REF = '_after_ref';

    const TYPE_STRING = 'string';
    const TYPE_NUMBER = 'number';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';

    use KeywordParserTrait;

    
    abstract public function type(): string;

    
    abstract public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword;

    
    protected function trackEvaluated(SchemaInfo $info): bool
    {
        $draft = $info->draft();
        return $draft !== '06' && $draft !== '07';
    }
}