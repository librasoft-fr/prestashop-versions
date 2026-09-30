<?php


namespace Opis\JsonSchema\Parsers;


use Opis\JsonSchema\Info\SchemaInfo;

trait DraftOptionTrait
{
    protected function optionAllowedForDraft(string $option, SchemaInfo $info, SchemaParser $parser): bool
    {
        $value = $parser->option($option);

        if (!$value) {
            return false;
        }

        if ($value === true) {
            return true;
        }

        if (is_array($value)) {
            return in_array($info->draft(), $value);
        }

        return $value === $info->draft();
    }
}