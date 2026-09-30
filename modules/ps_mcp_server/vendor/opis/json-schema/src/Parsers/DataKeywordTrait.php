<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\JsonPointer;

trait DataKeywordTrait
{
    
    protected function getDataKeywordPointer($value): ?JsonPointer
    {
        if (!is_object($value) || !property_exists($value, '$data') ||
            !is_string($value->{'$data'}) || count(get_object_vars($value)) !== 1) {
            return null;
        }

        return JsonPointer::parse($value->{'$data'});
    }

    
    protected function isDataKeywordAllowed(SchemaParser $parser, ?string $keyword = null): bool
    {
        if (!($enabled = $parser->option('allowDataKeyword'))) {
            return false;
        }

        if ($enabled === true) {
            return true;
        }

        if ($keyword === null) {
            return false;
        }

        return is_array($enabled) && in_array($keyword, $enabled);
    }
}