<?php


namespace Opis\JsonSchema\Parsers\Keywords;

use Opis\JsonSchema\{Keyword, Helper};
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keywords\DefaultKeyword;
use Opis\JsonSchema\Parsers\{KeywordParser, SchemaParser};

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
                $defaults = (array)Helper::cloneValue($defaults);
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