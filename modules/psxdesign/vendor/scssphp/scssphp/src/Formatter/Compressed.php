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
namespace PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter;

use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter;
/**
 * Compressed formatter
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 *
 * @internal
 */
class Compressed extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->indentLevel = 0;
        $this->indentChar = '  ';
        $this->break = '';
        $this->open = '{';
        $this->close = '}';
        $this->tagSeparator = ',';
        $this->assignSeparator = ':';
        $this->keepSemicolons = \false;
    }
    /**
     * {@inheritdoc}
     */
    public function blockLines(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $inner = $this->indentStr();
        $glue = $this->break . $inner;
        foreach ($block->lines as $index => $line) {
            if (\substr($line, 0, 2) === '/*' && \substr($line, 2, 1) !== '!') {
                unset($block->lines[$index]);
            }
        }
        $this->write($inner . \implode($glue, $block->lines));
        if (!empty($block->children)) {
            $this->write($this->break);
        }
    }
    /**
     * Output block selectors
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     */
    protected function blockSelectors(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        \assert(!empty($block->selectors));
        $inner = $this->indentStr();
        $this->write($inner . \implode($this->tagSeparator, \str_replace([' > ', ' + ', ' ~ '], ['>', '+', '~'], $block->selectors)) . $this->open . $this->break);
    }
}
