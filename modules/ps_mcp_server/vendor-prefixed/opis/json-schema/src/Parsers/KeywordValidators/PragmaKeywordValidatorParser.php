<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordValidators;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\KeywordValidator;
use PsMcpServerDeps\Opis\JsonSchema\KeywordValidators\PragmaKeywordValidator;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordValidatorParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class PragmaKeywordValidatorParser extends KeywordValidatorParser
{
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator
    {
        if (!$parser->option('allowPragmas') || !$this->keywordExists($info)) {
            return null;
        }

        $value = $this->keywordValue($info);

        if (!is_object($value)) {
            throw $this->keywordException('{keyword} must be an object', $info);
        }

        $list = [];

        $draft = $info->draft() ?? $parser->defaultDraftVersion();

        $pragmaInfo = new SchemaInfo($value, null, $info->id() ?? $info->base(), $info->root(),
            array_merge($info->path(), [$this->keyword]), $draft);

        foreach ($parser->draft($draft)->pragmas() as $pragma) {
            if ($handler = $pragma->parse($pragmaInfo, $parser, $shared)) {
                $list[] = $handler;
            }
        }

        return $list ? new PragmaKeywordValidator($list) : null;
    }
}
