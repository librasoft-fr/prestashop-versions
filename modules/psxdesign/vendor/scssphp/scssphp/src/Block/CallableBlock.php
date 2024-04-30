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
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Compiler\Environment;
/**
 * @internal
 */
class CallableBlock extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var array|null
     */
    public $args;
    /**
     * @var Environment|null
     */
    public $parentEnv;
    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }
}
