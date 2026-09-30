<?php


namespace Opis\JsonSchema\Parsers\Pragmas;

use Opis\JsonSchema\Pragma;
use Opis\JsonSchema\Pragmas\SlotsPragma;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\{PragmaParser, SchemaParser};

class SlotsPragmaParser extends PragmaParser
{
    
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Pragma
    {
        if (!$parser->option('allowSlots') || !$this->pragmaExists($info)) {
            return null;
        }

        $value = $this->pragmaValue($info);

        if (!is_object($value)) {
            throw $this->pragmaException('Pragma {pragma} must be an object', $info);
        }

        $list = [];

        foreach ($value as $name => $slot) {
            if ($slot === null) {
                continue;
            }

            if (is_bool($slot)) {

                $list[$name] = $parser->parseSchema(new SchemaInfo(
                    $slot, null, $info->base(), $info->root(),
                    array_merge($info->path(), [$this->pragma, $name]),
                    $info->draft() ?? $parser->defaultDraftVersion()
                ));
            } elseif (is_string($slot) || is_object($slot)) {
                $list[$name] = $slot;
            } else {
                throw $this->pragmaException('Pragma {pragma} contains invalid value for slot ' . $name, $info);
            }
        }

        return $list ? new SlotsPragma($list) : null;
    }
}