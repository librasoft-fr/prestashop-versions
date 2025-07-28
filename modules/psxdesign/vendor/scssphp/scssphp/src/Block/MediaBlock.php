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
class MediaBlock extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Block
{
    /**
     * @var string|array|null
     */
    public $value;
    /**
     * @var array|null
     */
    public $queryList;
    public function __construct()
    {
        $this->type = \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_MEDIA;
    }
}
