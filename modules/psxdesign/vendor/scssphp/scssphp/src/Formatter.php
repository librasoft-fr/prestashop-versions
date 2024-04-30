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

use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock;
use PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\SourceMap\SourceMapGenerator;
/**
 * Base formatter
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 *
 * @internal
 */
abstract class Formatter
{
    /**
     * @var int
     */
    public $indentLevel;
    /**
     * @var string
     */
    public $indentChar;
    /**
     * @var string
     */
    public $break;
    /**
     * @var string
     */
    public $open;
    /**
     * @var string
     */
    public $close;
    /**
     * @var string
     */
    public $tagSeparator;
    /**
     * @var string
     */
    public $assignSeparator;
    /**
     * @var bool
     */
    public $keepSemicolons;
    /**
     * @var \ScssPhp\ScssPhp\Formatter\OutputBlock
     */
    protected $currentBlock;
    /**
     * @var int
     */
    protected $currentLine;
    /**
     * @var int
     */
    protected $currentColumn;
    /**
     * @var \ScssPhp\ScssPhp\SourceMap\SourceMapGenerator|null
     */
    protected $sourceMapGenerator;
    /**
     * @var string
     */
    protected $strippedSemicolon;
    /**
     * Initialize formatter
     *
     * @api
     */
    public abstract function __construct();
    /**
     * Return indentation (whitespace)
     *
     * @return string
     */
    protected function indentStr()
    {
        return '';
    }
    /**
     * Return property assignment
     *
     * @api
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     */
    public function property($name, $value)
    {
        return \rtrim($name) . $this->assignSeparator . $value . ';';
    }
    /**
     * Return custom property assignment
     * differs in that you have to keep spaces in the value as is
     *
     * @api
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return string
     */
    public function customProperty($name, $value)
    {
        return \rtrim($name) . \trim($this->assignSeparator) . $value . ';';
    }
    /**
     * Output lines inside a block
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     *
     * @return void
     */
    protected function blockLines(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        $inner = $this->indentStr();
        $glue = $this->break . $inner;
        $this->write($inner . \implode($glue, $block->lines));
        if (!empty($block->children)) {
            $this->write($this->break);
        }
    }
    /**
     * Output block selectors
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     *
     * @return void
     */
    protected function blockSelectors(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        \assert(!empty($block->selectors));
        $inner = $this->indentStr();
        $this->write($inner . \implode($this->tagSeparator, $block->selectors) . $this->open . $this->break);
    }
    /**
     * Output block children
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     *
     * @return void
     */
    protected function blockChildren(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        foreach ($block->children as $child) {
            $this->block($child);
        }
    }
    /**
     * Output non-empty block
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     *
     * @return void
     */
    protected function block(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block)
    {
        if (empty($block->lines) && empty($block->children)) {
            return;
        }
        $this->currentBlock = $block;
        $pre = $this->indentStr();
        if (!empty($block->selectors)) {
            $this->blockSelectors($block);
            $this->indentLevel++;
        }
        if (!empty($block->lines)) {
            $this->blockLines($block);
        }
        if (!empty($block->children)) {
            $this->blockChildren($block);
        }
        if (!empty($block->selectors)) {
            $this->indentLevel--;
            if (!$this->keepSemicolons) {
                $this->strippedSemicolon = '';
            }
            if (empty($block->children)) {
                $this->write($this->break);
            }
            $this->write($pre . $this->close . $this->break);
        }
    }
    /**
     * Test and clean safely empty children
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock $block
     *
     * @return bool
     */
    protected function testEmptyChildren($block)
    {
        $isEmpty = empty($block->lines);
        if ($block->children) {
            foreach ($block->children as $k => &$child) {
                if (!$this->testEmptyChildren($child)) {
                    $isEmpty = \false;
                    continue;
                }
                if ($child->type === \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_MEDIA || $child->type === \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Type::T_DIRECTIVE) {
                    $child->children = [];
                    $child->selectors = null;
                }
            }
        }
        return $isEmpty;
    }
    /**
     * Entry point to formatting a block
     *
     * @api
     *
     * @param \ScssPhp\ScssPhp\Formatter\OutputBlock             $block              An abstract syntax tree
     * @param \ScssPhp\ScssPhp\SourceMap\SourceMapGenerator|null $sourceMapGenerator Optional source map generator
     *
     * @return string
     */
    public function format(\PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\Formatter\OutputBlock $block, \PrestaShop\Module\PsxDesign\Vendor\ScssPhp\ScssPhp\SourceMap\SourceMapGenerator $sourceMapGenerator = null)
    {
        $this->sourceMapGenerator = null;
        if ($sourceMapGenerator) {
            $this->currentLine = 1;
            $this->currentColumn = 0;
            $this->sourceMapGenerator = $sourceMapGenerator;
        }
        $this->testEmptyChildren($block);
        \ob_start();
        try {
            $this->block($block);
        } catch (\Exception $e) {
            \ob_end_clean();
            throw $e;
        } catch (\Throwable $e) {
            \ob_end_clean();
            throw $e;
        }
        $out = \ob_get_clean();
        \assert($out !== \false);
        return $out;
    }
    /**
     * Output content
     *
     * @param string $str
     *
     * @return void
     */
    protected function write($str)
    {
        if (!empty($this->strippedSemicolon)) {
            echo $this->strippedSemicolon;
            $this->strippedSemicolon = '';
        }
        /*
         * Maybe Strip semi-colon appended by property(); it's a separator, not a terminator
         * will be striped for real before a closing, otherwise displayed unchanged starting the next write
         */
        if (!$this->keepSemicolons && $str && \strpos($str, ';') !== \false && \substr($str, -1) === ';') {
            $str = \substr($str, 0, -1);
            $this->strippedSemicolon = ';';
        }
        if ($this->sourceMapGenerator) {
            $lines = \explode("\n", $str);
            $lastLine = \array_pop($lines);
            foreach ($lines as $line) {
                // If the written line starts is empty, adding a mapping would add it for
                // a non-existent column as we are at the end of the line
                if ($line !== '') {
                    \assert($this->currentBlock->sourceLine !== null);
                    \assert($this->currentBlock->sourceName !== null);
                    $this->sourceMapGenerator->addMapping(
                        $this->currentLine,
                        $this->currentColumn,
                        $this->currentBlock->sourceLine,
                        //columns from parser are off by one
                        $this->currentBlock->sourceColumn > 0 ? $this->currentBlock->sourceColumn - 1 : 0,
                        $this->currentBlock->sourceName
                    );
                }
                $this->currentLine++;
                $this->currentColumn = 0;
            }
            if ($lastLine !== '') {
                \assert($this->currentBlock->sourceLine !== null);
                \assert($this->currentBlock->sourceName !== null);
                $this->sourceMapGenerator->addMapping(
                    $this->currentLine,
                    $this->currentColumn,
                    $this->currentBlock->sourceLine,
                    //columns from parser are off by one
                    $this->currentBlock->sourceColumn > 0 ? $this->currentBlock->sourceColumn - 1 : 0,
                    $this->currentBlock->sourceName
                );
            }
            $this->currentColumn += \strlen($lastLine);
        }
        echo $str;
    }
}
