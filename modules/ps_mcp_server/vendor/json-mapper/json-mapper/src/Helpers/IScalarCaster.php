<?php

declare(strict_types=1);

namespace JsonMapper\Helpers;

use JsonMapper\Enums\ScalarType;

interface IScalarCaster
{
    
    public function cast(ScalarType $scalarType, $value);
}
