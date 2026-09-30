<?php

namespace BrianHenryIE\Strauss\Config;

interface ChangeEnumeratorConfigInterface
{
    
    public function getExcludePackagesFromPrefixing(): array;

    
    public function getExcludeFilePatternsFromPrefixing(): array;

    
    public function getNamespaceReplacementPatterns(): array;

    public function getNamespacePrefix(): ?string;

    public function getClassmapPrefix(): ?string;
}
