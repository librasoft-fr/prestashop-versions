<?php declare(strict_types=1);

namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\Lexer\Emulative;


class ReadonlyFunctionTokenEmulator extends KeywordEmulator {
    public function getKeywordString(): string {
        return 'readonly';
    }

    public function getKeywordToken(): int {
        return \T_READONLY;
    }

    public function getPhpVersion(): string {
        return Emulative::PHP_8_2;
    }

    public function reverseEmulate(string $code, array $tokens): array {
        
        return $tokens;
    }
}
