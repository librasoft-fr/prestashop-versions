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
 * Debug formatter
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @deprecated since 1.4.0.
 *
 * @internal
 */
class Debug extends \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        @\trigger_error('The Debug formatter is deprecated since 1.4.0.', \E_USER_DEPRECATED);
        $this->indentLevel = 0;
        $this->indentChar = '';
        $this->break = "\n";
        $this->open = ' {';
        $this->close = ' }';
        $this->tagSeparator = ', ';
        $this->assignSeparator = ': ';
        $this->keepSemicolons = \true;
    }
    /**
     * {@inheritdoc}
     */
    protected function indentStr()
    {
        return \str_repeat('  ', $this->indentLevel);
    }
    /**
     * {@inheritdoc}
     */
    protected function blockLines(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $indent = $this->indentStr();
        if (empty($block->lines)) {
            $this->write("{$indent}block->lines: []\n");
            return;
        }
        foreach ($block->lines as $index => $line) {
            $this->write("{$indent}block->lines[{$index}]: {$line}\n");
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function blockSelectors(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $indent = $this->indentStr();
        if (empty($block->selectors)) {
            $this->write("{$indent}block->selectors: []\n");
            return;
        }
        foreach ($block->selectors as $index => $selector) {
            $this->write("{$indent}block->selectors[{$index}]: {$selector}\n");
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function blockChildren(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $indent = $this->indentStr();
        if (empty($block->children)) {
            $this->write("{$indent}block->children: []\n");
            return;
        }
        $this->indentLevel++;
        foreach ($block->children as $i => $child) {
            $this->block($child);
        }
        $this->indentLevel--;
    }
    /**
     * {@inheritdoc}
     */
    protected function block(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $indent = $this->indentStr();
        $this->write("{$indent}block->type: {$block->type}\n" . "{$indent}block->depth: {$block->depth}\n");
        $this->currentBlock = $block;
        $this->blockSelectors($block);
        $this->blockLines($block);
        $this->blockChildren($block);
    }
}
