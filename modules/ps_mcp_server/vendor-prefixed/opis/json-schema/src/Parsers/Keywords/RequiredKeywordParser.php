<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\RequiredDataKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\RequiredKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\DataKeywordTrait;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class RequiredKeywordParser extends KeywordParser
{
    use DataKeywordTrait;

    public function type(): string
    {
        return self::TYPE_OBJECT;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        $filter = $this->propertiesFilter($parser, $schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new RequiredDataKeyword($pointer, $filter);
            }
        }

        if (!is_array($value)) {
            throw $this->keywordException('{keyword} must be an array of strings', $info);
        }

        foreach ($value as $name) {
            if (!is_string($name)) {
                throw $this->keywordException('{keyword} must be an array of strings', $info);
            }
        }

        if ($filter) {
            $value = array_filter($value, $filter);
        }

        return $value ? new RequiredKeyword(array_unique($value)) : null;
    }

    protected function propertiesFilter(SchemaParser $parser, object $schema): ?callable
    {
        if (!$parser->option('allowDefaults')) {
            return null;
        }

        if (!property_exists($schema, 'properties') || !is_object($schema->properties)) {
            return null;
        }

        $props = $schema->properties;

        return static function (string $name) use ($props) {
            if (!property_exists($props, $name)) {
                return true;
            }

            if (is_object($props->{$name}) && property_exists($props->{$name}, 'default')) {
                return false;
            }

            return true;
        };
    }
}
