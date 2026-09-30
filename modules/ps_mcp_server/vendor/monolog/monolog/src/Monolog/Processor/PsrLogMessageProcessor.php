<?php declare(strict_types=1);



namespace Monolog\Processor;

use Monolog\Utils;


class PsrLogMessageProcessor implements ProcessorInterface
{
    public const SIMPLE_DATE = "Y-m-d\TH:i:s.uP";

    
    private $dateFormat;

    
    private $removeUsedContextFields;

    
    public function __construct(?string $dateFormat = null, bool $removeUsedContextFields = false)
    {
        $this->dateFormat = $dateFormat;
        $this->removeUsedContextFields = $removeUsedContextFields;
    }

    
    public function __invoke(array $record): array
    {
        if (false === strpos($record['message'], '{')) {
            return $record;
        }

        $replacements = [];
        foreach ($record['context'] as $key => $val) {
            $placeholder = '{' . $key . '}';
            if (strpos($record['message'], $placeholder) === false) {
                continue;
            }

            if (is_null($val) || is_scalar($val) || (is_object($val) && method_exists($val, "__toString"))) {
                $replacements[$placeholder] = $val;
            } elseif ($val instanceof \DateTimeInterface) {
                if (!$this->dateFormat && $val instanceof \Monolog\DateTimeImmutable) {
                    
                    
                    $replacements[$placeholder] = (string) $val;
                } else {
                    $replacements[$placeholder] = $val->format($this->dateFormat ?: static::SIMPLE_DATE);
                }
            } elseif ($val instanceof \UnitEnum) {
                $replacements[$placeholder] = $val instanceof \BackedEnum ? $val->value : $val->name;
            } elseif (is_object($val)) {
                $replacements[$placeholder] = '[object '.Utils::getClass($val).']';
            } elseif (is_array($val)) {
                $replacements[$placeholder] = 'array'.Utils::jsonEncode($val, null, true);
            } else {
                $replacements[$placeholder] = '['.gettype($val).']';
            }

            if ($this->removeUsedContextFields) {
                unset($record['context'][$key]);
            }
        }

        $record['message'] = strtr($record['message'], $replacements);

        return $record;
    }
}
