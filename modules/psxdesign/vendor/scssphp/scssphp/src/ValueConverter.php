<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */
namespace PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp;

use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Node\Number;
final class ValueConverter
{
    // Prevent instantiating it
    private function __construct()
    {
    }
    /**
     * Parses a value from a Scss source string.
     *
     * The returned value is guaranteed to be supported by the
     * Compiler methods for registering custom variables. No other
     * guarantee about it is provided. It should be considered
     * opaque values by the caller.
     *
     * @param string $source
     *
     * @return mixed
     */
    public static function parseValue($source)
    {
        $parser = new \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Parser(__CLASS__);
        if (!$parser->parseValue($source, $value)) {
            throw new \InvalidArgumentException(\sprintf('Invalid value source "%s".', $source));
        }
        return $value;
    }
    /**
     * Converts a PHP value to a Sass value
     *
     * The returned value is guaranteed to be supported by the
     * Compiler methods for registering custom variables. No other
     * guarantee about it is provided. It should be considered
     * opaque values by the caller.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function fromPhp($value)
    {
        if ($value instanceof \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Node\Number) {
            return $value;
        }
        if (\is_array($value) && isset($value[0]) && \in_array($value[0], [\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_NULL, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_COLOR, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_KEYWORD, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_LIST, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_MAP, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_STRING])) {
            return $value;
        }
        if ($value === null) {
            return \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler::$null;
        }
        if ($value === \true) {
            return \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler::$true;
        }
        if ($value === \false) {
            return \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler::$false;
        }
        if ($value === '') {
            return \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler::$emptyString;
        }
        if (\is_int($value) || \is_float($value)) {
            return new \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Node\Number($value, '');
        }
        if (\is_string($value)) {
            return [\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_STRING, '"', [$value]];
        }
        throw new \InvalidArgumentException(\sprintf('Cannot convert the value of type "%s" to a Sass value.', \gettype($value)));
    }
}
