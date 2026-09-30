<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\ItemsKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class ItemsKeywordParser extends KeywordParser
{
    public const ONLY_SCHEMA = 1;
    public const ONLY_ARRAY = 2;
    public const BOTH = 3;

    protected int $mode;
    protected ?string $startIndexKeyword;

    public function __construct(string $keyword, int $mode = self::BOTH, ?string $startIndexKeyword = null)
    {
        parent::__construct($keyword);
        $this->mode = $mode;
        $this->startIndexKeyword = $startIndexKeyword;
    }

    public function type(): string
    {
        return self::TYPE_ARRAY;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        $alwaysValid = false;

        if (is_bool($value)) {
            if ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException('{keyword} must contain an array of json schemas', $info);
            }
            if ($value) {
                $alwaysValid = true;
            }
        } elseif (is_array($value)) {
            if ($this->mode === self::ONLY_SCHEMA) {
                throw $this->keywordException('{keyword} must contain a valid json schema', $info);
            }
            $valid = 0;
            foreach ($value as $index => $v) {
                if (is_bool($v)) {
                    if ($v) {
                        ++$valid;
                    }
                } elseif (!is_object($v)) {
                    throw $this->keywordException("{keyword}[$index] must contain a valid json schema", $info);
                } elseif (!count(get_object_vars($v))) {
                    ++$valid;
                }
            }
            if ($valid === count($value)) {
                $alwaysValid = true;
            }
        } elseif (!is_object($value)) {
            if ($this->mode === self::BOTH) {
                throw $this->keywordException('{keyword} must be a json schema or an array of json schemas', $info);
            } elseif ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException('{keyword} must contain an array of json schemas', $info);
            }
            throw $this->keywordException('{keyword} must contain a valid json schema', $info);
        } else {
            if ($this->mode === self::ONLY_ARRAY) {
                throw $this->keywordException('{keyword} must contain an array of json schemas', $info);
            }
            if (!count(get_object_vars($value))) {
                $alwaysValid = true;
            }
        }

        $startIndex = 0;
        if ($this->startIndexKeyword !== null && $this->keywordExists($schema, $this->startIndexKeyword)) {
            $start = $this->keywordValue($schema, $this->startIndexKeyword);
            if (is_array($start)) {
                $startIndex = count($start);
            }
        }

        return new ItemsKeyword($value, $alwaysValid, $this->keyword, $startIndex);
    }
}
