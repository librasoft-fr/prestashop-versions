<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Pragmas;

use PsMcpServerDeps\Opis\JsonSchema\Info\SchemaInfo;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\PragmaParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\SchemaParser;
use PsMcpServerDeps\Opis\JsonSchema\Pragma;
use PsMcpServerDeps\Opis\JsonSchema\Pragmas\SlotsPragma;

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
