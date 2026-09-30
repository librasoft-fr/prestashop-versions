<?php


namespace Opis\JsonSchema\Keywords;

use Opis\JsonSchema\{ValidationContext, Keyword, Schema};
use Opis\JsonSchema\Errors\ValidationError;

class SlotsKeyword implements Keyword
{
    use ErrorTrait;

    
    protected array $slots;

    
    protected array $stack = [];

    
    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $newContext = $context->newInstance($context->currentData(), $schema);

        foreach ($this->slots as $name => $fallback) {
            $slot = $this->resolveSlotSchema($name, $context);

            if ($slot === null) {
                $save = true;
                if (is_string($fallback)) {
                    $save = false;
                    $fallback = $this->resolveSlot($fallback, $context);
                }

                if ($fallback === true) {
                    continue;
                }

                if ($fallback === false) {
                    return $this->error($schema, $context, '$slots', "Required slot '{slot}' is missing", [
                        'slot' => $name,
                    ]);
                }

                if (is_object($fallback) && !($fallback instanceof Schema)) {
                    $fallback = $context->loader()->loadObjectSchema($fallback);
                    if ($save) {
                        $this->slots[$name] = $fallback;
                    }
                }

                $slot = $fallback;
            }

            if ($error = $slot->validate($newContext)) {
                return $this->error($schema, $context,'$slots', "Schema for slot '{slot}' was not matched", [
                    'slot' => $name,
                ], $error);
            }
        }

        return null;
    }

    
    protected function resolveSlotSchema(string $name, ValidationContext $context): ?Schema
    {
        do {
            $slot = $context->slot($name);
        } while ($slot === null && $context = $context->parent());

        return $slot;
    }

    
    protected function resolveSlot(string $name, ValidationContext $context)
    {
        $slot = $this->resolveSlotSchema($name, $context);

        if ($slot !== null) {
            return $slot;
        }

        if (!isset($this->slots[$name])) {
            return false;
        }

        $slot = $this->slots[$name];

        if (is_bool($slot)) {
            return $slot;
        }

        if (is_object($slot)) {
            if ($slot instanceof Schema) {
                return $slot;
            }

            $slot = $context->loader()->loadObjectSchema($slot);
            $this->slots[$name] = $slot;
            return $slot;
        }

        if (!is_string($slot)) {
            
            return false;
        }

        if (in_array($slot, $this->stack)) {
            
            return false;
        }

        $this->stack[] = $slot;
        $slot = $this->resolveSlot($slot, $context);
        array_pop($this->stack);

        return $slot;
    }
}