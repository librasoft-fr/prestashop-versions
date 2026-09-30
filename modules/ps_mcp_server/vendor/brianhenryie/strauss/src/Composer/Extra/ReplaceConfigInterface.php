<?php

namespace BrianHenryIE\Strauss\Composer\Extra;

interface ReplaceConfigInterface
{

    
    public function getExcludeFilePatternsFromPrefixing(): array;

    
    public function getNamespaceReplacementPatterns(): array;

    public function isIncludeModifiedDate(): bool;

    public function isIncludeAuthor(): bool;
}
