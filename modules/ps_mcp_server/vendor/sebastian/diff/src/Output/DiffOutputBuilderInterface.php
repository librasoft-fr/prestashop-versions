<?php declare(strict_types=1);

namespace SebastianBergmann\Diff\Output;


interface DiffOutputBuilderInterface
{
    public function getDiff(array $diff): string;
}
