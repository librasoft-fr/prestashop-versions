<?php declare(strict_types=1);



namespace Monolog\Formatter;


class ScalarFormatter extends NormalizerFormatter
{
    
    public function format(array $record): array
    {
        $result = [];
        foreach ($record as $key => $value) {
            $result[$key] = $this->normalizeValue($value);
        }

        return $result;
    }

    
    protected function normalizeValue($value)
    {
        $normalized = $this->normalize($value);

        if (is_array($normalized)) {
            return $this->toJson($normalized, true);
        }

        return $normalized;
    }
}
