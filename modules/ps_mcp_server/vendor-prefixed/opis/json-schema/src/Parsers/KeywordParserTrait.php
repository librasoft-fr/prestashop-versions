<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers;

use PsMcpServerDeps\Opis\JsonSchema\Exceptions\InvalidKeywordException;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;

trait KeywordParserTrait
{
    protected string $keyword;

    public function __construct(string $keyword)
    {
        $this->keyword = $keyword;
    }

    protected function keywordExists(object $schema, ?string $keyword = null): bool
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->data();
        }

        return property_exists($schema, $keyword ?? $this->keyword);
    }

    protected function keywordValue(object $schema, ?string $keyword = null)
    {
        if ($schema instanceof SchemaInfo) {
            $schema = $schema->data();
        }

        return $schema->{$keyword ?? $this->keyword};
    }

    protected function keywordException(string $message, SchemaInfo $info, ?string $keyword = null): InvalidKeywordException
    {
        $keyword = $keyword ?? $this->keyword;

        return new InvalidKeywordException(str_replace('{keyword}', $keyword, $message), $keyword, $info);
    }
}
