<?php declare(strict_types=1);



namespace Composer\Repository;

use Composer\FilterList\FilterListEntry;
use Composer\Semver\Constraint\ConstraintInterface;


interface FilterListProviderInterface
{
    public function hasFilter(): bool;

    
    public function getFilter(array $packageConstraintMap, array $configuredLists): array;

    
    public function getFilterLists(): array;
}
