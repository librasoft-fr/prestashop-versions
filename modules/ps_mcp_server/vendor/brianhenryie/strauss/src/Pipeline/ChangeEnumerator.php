<?php


namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Config\ChangeEnumeratorConfigInterface;
use BrianHenryIE\Strauss\Files\FileWithDependency;
use BrianHenryIE\Strauss\Types\ClassSymbol;
use BrianHenryIE\Strauss\Types\DiscoveredSymbols;
use BrianHenryIE\Strauss\Types\FunctionSymbol;
use BrianHenryIE\Strauss\Types\NamespaceSymbol;

class ChangeEnumerator
{
    protected ChangeEnumeratorConfigInterface $config;
    protected string $workingDir;

    public function __construct(ChangeEnumeratorConfigInterface $config, string $workingDir)
    {
        $this->config = $config;
        $this->workingDir = $workingDir;
    }

    public function determineReplacements(DiscoveredSymbols $discoveredSymbols): void
    {
        foreach ($discoveredSymbols->getSymbols() as $symbol) {
            $symbolSourceFile = $symbol->getSourceFile();
            if ($symbolSourceFile instanceof FileWithDependency) {
                if (in_array(
                    $symbolSourceFile->getDependency()->getPackageName(),
                    $this->config->getExcludePackagesFromPrefixing(),
                    true
                )) {
                    continue;
                }

                foreach ($this->config->getExcludeFilePatternsFromPrefixing() as $excludeFilePattern) {
                    
                    
                    if (1 === preg_match($excludeFilePattern, $symbolSourceFile->getSourcePath($this->workingDir))) {
                        continue 2;
                    }
                }
            }
            
            if ($symbol instanceof NamespaceSymbol) {
                $namespaceReplacementPatterns = $this->config->getNamespaceReplacementPatterns();

                

                
                foreach ($namespaceReplacementPatterns as $pattern => $replacement) {
                    if (substr($pattern, 0, 1) !== substr($pattern, -1, 1)) {
                        unset($namespaceReplacementPatterns[$pattern]);
                        $pattern = '~'. preg_quote($pattern, '~') . '~';
                        $namespaceReplacementPatterns[$pattern] = $replacement;
                    }
                    unset($pattern, $replacement);
                }

                if (!is_null($this->config->getNamespacePrefix())) {
                    $stripPattern = '~^('.preg_quote($this->config->getNamespacePrefix(), '~') .'\\\\*)*(.*)~';
                    $strippedSymbol = preg_replace(
                        $stripPattern,
                        '$2',
                        $symbol->getOriginalSymbol()
                    );
                    $namespaceReplacementPatterns[ "~(" . preg_quote($this->config->getNamespacePrefix(), '~') . '\\\\*)*' . preg_quote($strippedSymbol, '~') . '~' ]
                        = "{$this->config->getNamespacePrefix()}\\{$strippedSymbol}";
                    unset($stripPattern, $strippedSymbol);
                }

                
                foreach ($namespaceReplacementPatterns as $namespaceReplacementPattern => $replacement) {
                    $prefixed = preg_replace(
                        $namespaceReplacementPattern,
                        $replacement,
                        $symbol->getOriginalSymbol()
                    );

                    if ($prefixed !== $symbol->getOriginalSymbol()) {
                        $symbol->setReplacement($prefixed);
                        continue 2;
                    }
                }
            }

            if ($symbol instanceof ClassSymbol) {
                
                if (str_starts_with($symbol->getOriginalSymbol(), $this->config->getClassmapPrefix())) {
                    continue;
                }

                $symbol->setReplacement($this->config->getClassmapPrefix() . $symbol->getOriginalSymbol());
            }

            if ($symbol instanceof FunctionSymbol) {
                
                $functionPrefix = strtolower($this->config->getClassmapPrefix());
                if (str_starts_with($symbol->getOriginalSymbol(), $functionPrefix)) {
                    continue;
                }

                $symbol->setReplacement($functionPrefix . $symbol->getOriginalSymbol());
            }
        }
    }
}
