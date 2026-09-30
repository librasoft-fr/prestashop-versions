<?php declare(strict_types=1);

namespace SebastianBergmann\Diff\Output;

use function fclose;
use function fopen;
use function fwrite;
use function str_ends_with;
use function stream_get_contents;
use function substr;
use SebastianBergmann\Diff\Differ;


final class DiffOnlyOutputBuilder implements DiffOutputBuilderInterface
{
    private string $header;

    public function __construct(string $header = "--- Original\n+++ New\n")
    {
        $this->header = $header;
    }

    public function getDiff(array $diff): string
    {
        $buffer = fopen('php://memory', 'r+b');

        if ('' !== $this->header) {
            fwrite($buffer, $this->header);

            if (!str_ends_with($this->header, "\n")) {
                fwrite($buffer, "\n");
            }
        }

        foreach ($diff as $diffEntry) {
            if ($diffEntry[1] === Differ::ADDED) {
                fwrite($buffer, '+' . $diffEntry[0]);
            } elseif ($diffEntry[1] === Differ::REMOVED) {
                fwrite($buffer, '-' . $diffEntry[0]);
            } elseif ($diffEntry[1] === Differ::DIFF_LINE_END_WARNING) {
                fwrite($buffer, ' ' . $diffEntry[0]);

                continue; 
            } else { 
                continue; 
            }

            $lc = substr($diffEntry[0], -1);

            if ($lc !== "\n" && $lc !== "\r") {
                fwrite($buffer, "\n"); 
            }
        }

        $diff = stream_get_contents($buffer, -1, 0);
        fclose($buffer);

        return $diff;
    }
}
