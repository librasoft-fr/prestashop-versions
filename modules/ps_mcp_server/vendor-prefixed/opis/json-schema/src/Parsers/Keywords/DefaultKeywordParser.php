<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords;

use PsMcpServerDeps\Opis\JsonSchema\Helper;
use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Keyword;
use PsMcpServerDeps\Opis\JsonSchema\Keywords\DefaultKeyword;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;

class DefaultKeywordParser extends KeywordParser
{
    protected ?string $properties = null;

    public function __construct(string $keyword, ?string $properties = 'properties')
    {
        parent::__construct($keyword);
        $this->properties = $properties;
    }

    public function type(): string
    {
        return self::TYPE_APPEND;
    }

    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        if (!$parser->option('allowDefaults')) {
            return null;
        }

        $defaults = null;

        if ($this->keywordExists($schema)) {
            $defaults = $this->keywordValue($schema);

            if (is_object($defaults)) {
                $defaults = (array) Helper::cloneValue($defaults);
            } else {
                $defaults = null;
            }
        }

        if ($this->properties !== null && property_exists($schema, $this->properties)
            && is_object($schema->{$this->properties})) {
            foreach ($schema->{$this->properties} as $name => $value) {
                if (is_object($value) && property_exists($value, $this->keyword)) {
                    $defaults[$name] = $value->{$this->keyword};
                }
            }
        }

        if (!$defaults) {
            return null;
        }

        return new DefaultKeyword($defaults);
    }
}
