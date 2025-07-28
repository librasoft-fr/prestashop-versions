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
namespace PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block;

use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type;
/**
 * @internal
 */
class NestedPropertyBlock extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block
{
    /**
     * @var bool
     */
    public $hasValue;
    /**
     * @var array
     */
    public $prefix;
    public function __construct()
    {
        $this->type = \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_NESTED_PROPERTY;
    }
}
