<?php



namespace Symfony\Component\Console\Output;

use Symfony\Component\Console\Exception\InvalidArgumentException;


enum AnsiColorMode
{
    
    case Ansi4;

    
    case Ansi8;

    
    case Ansi24;

    
    public function convertFromHexToAnsiColorCode(string $hexColor): string
    {
        $hexColor = str_replace('#', '', $hexColor);

        if (3 === \strlen($hexColor)) {
            $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
        }

        if (6 !== \strlen($hexColor)) {
            throw new InvalidArgumentException(\sprintf('Invalid "#%s" color.', $hexColor));
        }

        $color = hexdec($hexColor);

        $r = ($color >> 16) & 255;
        $g = ($color >> 8) & 255;
        $b = $color & 255;

        return match ($this) {
            self::Ansi4 => (string) $this->convertFromRGB($r, $g, $b),
            self::Ansi8 => '8;5;'.((string) $this->convertFromRGB($r, $g, $b)),
            self::Ansi24 => \sprintf('8;2;%d;%d;%d', $r, $g, $b),
        };
    }

    private function convertFromRGB(int $r, int $g, int $b): int
    {
        return match ($this) {
            self::Ansi4 => $this->degradeHexColorToAnsi4($r, $g, $b),
            self::Ansi8 => $this->degradeHexColorToAnsi8($r, $g, $b),
            default => throw new InvalidArgumentException("RGB cannot be converted to {$this->name}."),
        };
    }

    private function degradeHexColorToAnsi4(int $r, int $g, int $b): int
    {
        return round($b / 255) << 2 | (round($g / 255) << 1) | round($r / 255);
    }

    
    private function degradeHexColorToAnsi8(int $r, int $g, int $b): int
    {
        if ($r === $g && $g === $b) {
            if ($r < 8) {
                return 16;
            }

            if ($r > 248) {
                return 231;
            }

            return (int) round(($r - 8) / 247 * 24) + 232;
        }

        return 16 +
                (36 * (int) round($r / 255 * 5)) +
                (6 * (int) round($g / 255 * 5)) +
                (int) round($b / 255 * 5);
    }
}
