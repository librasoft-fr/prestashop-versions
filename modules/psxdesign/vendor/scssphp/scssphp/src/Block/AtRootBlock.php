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
class AtRootBlock extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block
{
    /**
     * @var array|null
     */
    public $selector;
    /**
     * @var array|null
     */
    public $with;
    public function __construct()
    {
        $this->type = \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_AT_ROOT;
    }
}
