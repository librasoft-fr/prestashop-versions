<?php

declare(strict_types=1);

namespace Sentry\Serializer;


class RepresentationSerializer extends AbstractSerializer implements RepresentationSerializerInterface
{
    
    public function representationSerialize($value)
    {
        $value = $this->serializeRecursively($value);

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    
    protected function serializeValue($value)
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        if (\is_float($value) && (int) $value == $value) {
            return $value . '.0';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return (string) parent::serializeValue($value);
    }
}
