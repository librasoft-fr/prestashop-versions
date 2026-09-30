<?php

declare(strict_types=1);

namespace JsonMapper\Enums;

use MyCLabs\Enum\Enum;


class ScalarType extends Enum
{
    protected const STRING = 'string';
    protected const BOOLEAN = 'boolean';
    protected const BOOL = 'bool';
    protected const INTEGER = 'integer';
    protected const INT = 'int';
    protected const DOUBLE = 'double';
    protected const FLOAT = 'float';
    protected const MIXED = 'mixed';
}
