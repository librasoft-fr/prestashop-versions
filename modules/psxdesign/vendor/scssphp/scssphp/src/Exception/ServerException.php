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
namespace PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception;

@\trigger_error(\sprintf('The "%s" class is deprecated.', \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception\ServerException::class), \E_USER_DEPRECATED);
/**
 * Server Exception
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @deprecated The Scssphp server should define its own exception instead.
 */
class ServerException extends \Exception implements \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Exception\SassException
{
}
