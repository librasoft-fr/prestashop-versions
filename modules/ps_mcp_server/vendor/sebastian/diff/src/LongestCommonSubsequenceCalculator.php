<?php declare(strict_types=1);

namespace SebastianBergmann\Diff;

interface LongestCommonSubsequenceCalculator
{
    
    public function calculate(array $from, array $to): array;
}
