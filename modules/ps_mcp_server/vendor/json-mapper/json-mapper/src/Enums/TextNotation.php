<?php

declare(strict_types=1);

namespace JsonMapper\Enums;

use MyCLabs\Enum\Enum;


class TextNotation extends Enum
{
    private const STUDLY_CAPS = 'studly_caps';
    private const CAMEL_CASE = 'camel_case';
    private const UNDERSCORE = 'underscore';
    private const KEBAB_CASE = 'kebab-case';
}
