<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;

abstract class KeywordParser
{
    use KeywordParserTrait;
    public const TYPE_PREPEND = '_prepend';
    public const TYPE_BEFORE = '_before';
    public const TYPE_AFTER = '_after';
    public const TYPE_APPEND = '_append';

    public const TYPE_AFTER_REF = '_after_ref';

    public const TYPE_STRING = 'string';
    public const TYPE_NUMBER = 'number';
    public const TYPE_ARRAY = 'array';
    public const TYPE_OBJECT = 'object';

    abstract public function type(): string;

    abstract public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword;

    protected function trackEvaluated(SchemaInfo $info): bool
    {
        $draft = $info->draft();

        return $draft !== '06' && $draft !== '07';
    }
}
