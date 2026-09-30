<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Parser;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Comment;
use PsMcpServerDeps\PHPStan\PhpDocParser\Lexer\Lexer;

class TokenIterator
{
    private array $tokens;

    private int $index;

    private array $comments = [];

    private array $savePoints = [];

    private array $skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS];

    private ?string $newline = null;

    public function __construct(array $tokens, int $index = 0)
    {
        $this->tokens = $tokens;
        $this->index = $index;

        $this->skipIrrelevantTokens();
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getContentBetween(int $startPos, int $endPos): string
    {
        if ($startPos < 0 || $endPos > \count($this->tokens)) {
            throw new \LogicException();
        }

        $content = '';
        for ($i = $startPos; $i < $endPos; ++$i) {
            $content .= $this->tokens[$i][Lexer::VALUE_OFFSET];
        }

        return $content;
    }

    public function getTokenCount(): int
    {
        return \count($this->tokens);
    }

    public function currentTokenValue(): string
    {
        return $this->tokens[$this->index][Lexer::VALUE_OFFSET];
    }

    public function currentTokenType(): int
    {
        return $this->tokens[$this->index][Lexer::TYPE_OFFSET];
    }

    public function currentTokenOffset(): int
    {
        $offset = 0;
        for ($i = 0; $i < $this->index; ++$i) {
            $offset += \strlen($this->tokens[$i][Lexer::VALUE_OFFSET]);
        }

        return $offset;
    }

    public function currentTokenLine(): int
    {
        return $this->tokens[$this->index][Lexer::LINE_OFFSET];
    }

    public function currentTokenIndex(): int
    {
        return $this->index;
    }

    public function endIndexOfLastRelevantToken(): int
    {
        $endIndex = $this->currentTokenIndex();
        --$endIndex;
        while (\in_array($this->tokens[$endIndex][Lexer::TYPE_OFFSET], $this->skippedTokenTypes, true)) {
            if (!isset($this->tokens[$endIndex - 1])) {
                break;
            }
            --$endIndex;
        }

        return $endIndex;
    }

    public function isCurrentTokenValue(string $tokenValue): bool
    {
        return $this->tokens[$this->index][Lexer::VALUE_OFFSET] === $tokenValue;
    }

    public function isCurrentTokenType(int ...$tokenType): bool
    {
        return \in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $tokenType, true);
    }

    public function isPrecededByHorizontalWhitespace(): bool
    {
        return ($this->tokens[$this->index - 1][Lexer::TYPE_OFFSET] ?? -1) === Lexer::TOKEN_HORIZONTAL_WS;
    }

    public function consumeTokenType(int $tokenType): void
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType) {
            $this->throwError($tokenType);
        }

        if ($tokenType === Lexer::TOKEN_PHPDOC_EOL) {
            if ($this->newline === null) {
                $this->detectNewline();
            }
        }

        $this->next();
    }

    public function consumeTokenValue(int $tokenType, string $tokenValue): void
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType || $this->tokens[$this->index][Lexer::VALUE_OFFSET] !== $tokenValue) {
            $this->throwError($tokenType, $tokenValue);
        }

        $this->next();
    }

    public function tryConsumeTokenValue(string $tokenValue): bool
    {
        if ($this->tokens[$this->index][Lexer::VALUE_OFFSET] !== $tokenValue) {
            return false;
        }

        $this->next();

        return true;
    }

    public function flushComments(): array
    {
        $res = $this->comments;
        $this->comments = [];

        return $res;
    }

    public function tryConsumeTokenType(int $tokenType): bool
    {
        if ($this->tokens[$this->index][Lexer::TYPE_OFFSET] !== $tokenType) {
            return false;
        }

        if ($tokenType === Lexer::TOKEN_PHPDOC_EOL) {
            if ($this->newline === null) {
                $this->detectNewline();
            }
        }

        $this->next();

        return true;
    }

    public function skipNewLineTokens(): void
    {
        if (!$this->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
            return;
        }

        do {
            $foundNewLine = $this->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        } while ($foundNewLine === true);
    }

    public function skipNewLineTokensAndConsumeComments(): void
    {
        if ($this->currentTokenType() === Lexer::TOKEN_COMMENT) {
            $this->comments[] = new Comment($this->currentTokenValue(), $this->currentTokenLine(), $this->currentTokenIndex());
            $this->next();
        }

        if (!$this->isCurrentTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
            return;
        }

        do {
            $foundNewLine = $this->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
            if ($this->currentTokenType() !== Lexer::TOKEN_COMMENT) {
                continue;
            }

            $this->comments[] = new Comment($this->currentTokenValue(), $this->currentTokenLine(), $this->currentTokenIndex());
            $this->next();
        } while ($foundNewLine === true);
    }

    private function detectNewline(): void
    {
        $value = $this->currentTokenValue();
        if (\substr($value, 0, 2) === "\r\n") {
            $this->newline = "\r\n";
        } elseif (\substr($value, 0, 1) === "\n") {
            $this->newline = "\n";
        }
    }

    public function getSkippedHorizontalWhiteSpaceIfAny(): string
    {
        if ($this->index > 0 && $this->tokens[$this->index - 1][Lexer::TYPE_OFFSET] === Lexer::TOKEN_HORIZONTAL_WS) {
            return $this->tokens[$this->index - 1][Lexer::VALUE_OFFSET];
        }

        return '';
    }

    public function joinUntil(int ...$tokenType): string
    {
        $s = '';
        while (!\in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $tokenType, true)) {
            $s .= $this->tokens[$this->index++][Lexer::VALUE_OFFSET];
        }

        return $s;
    }

    public function next(): void
    {
        ++$this->index;
        $this->skipIrrelevantTokens();
    }

    private function skipIrrelevantTokens(): void
    {
        if (!isset($this->tokens[$this->index])) {
            return;
        }

        while (\in_array($this->tokens[$this->index][Lexer::TYPE_OFFSET], $this->skippedTokenTypes, true)) {
            if (!isset($this->tokens[$this->index + 1])) {
                break;
            }
            ++$this->index;
        }
    }

    public function addEndOfLineToSkippedTokens(): void
    {
        $this->skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS, Lexer::TOKEN_PHPDOC_EOL];
    }

    public function removeEndOfLineFromSkippedTokens(): void
    {
        $this->skippedTokenTypes = [Lexer::TOKEN_HORIZONTAL_WS];
    }

    public function forwardToTheEnd(): void
    {
        $lastToken = \count($this->tokens) - 1;
        $this->index = $lastToken;
    }

    public function pushSavePoint(): void
    {
        $this->savePoints[] = [$this->index, $this->comments];
    }

    public function dropSavePoint(): void
    {
        \array_pop($this->savePoints);
    }

    public function rollback(): void
    {
        $savepoint = \array_pop($this->savePoints);
        \assert($savepoint !== null);
        [$this->index, $this->comments] = $savepoint;
    }

    private function throwError(int $expectedTokenType, ?string $expectedTokenValue = null): void
    {
        throw new ParserException($this->currentTokenValue(), $this->currentTokenType(), $this->currentTokenOffset(), $expectedTokenType, $expectedTokenValue, $this->currentTokenLine());
    }

    public function hasTokenImmediatelyBefore(int $pos, int $expectedTokenType): bool
    {
        $tokens = $this->tokens;
        --$pos;
        for (; $pos >= 0; --$pos) {
            $token = $tokens[$pos];
            $type = $token[Lexer::TYPE_OFFSET];
            if ($type === $expectedTokenType) {
                return true;
            }
            if (!\in_array($type, [
                Lexer::TOKEN_HORIZONTAL_WS,
                Lexer::TOKEN_PHPDOC_EOL,
            ], true)) {
                break;
            }
        }

        return false;
    }

    public function hasTokenImmediatelyAfter(int $pos, int $expectedTokenType): bool
    {
        $tokens = $this->tokens;
        ++$pos;
        for ($c = \count($tokens); $pos < $c; ++$pos) {
            $token = $tokens[$pos];
            $type = $token[Lexer::TYPE_OFFSET];
            if ($type === $expectedTokenType) {
                return true;
            }
            if (!\in_array($type, [
                Lexer::TOKEN_HORIZONTAL_WS,
                Lexer::TOKEN_PHPDOC_EOL,
            ], true)) {
                break;
            }
        }

        return false;
    }

    public function getDetectedNewline(): ?string
    {
        return $this->newline;
    }

    public function hasParentheses(int $startPos, int $endPos): bool
    {
        return $this->hasTokenImmediatelyBefore($startPos, Lexer::TOKEN_OPEN_PARENTHESES)
            && $this->hasTokenImmediatelyAfter($endPos, Lexer::TOKEN_CLOSE_PARENTHESES);
    }
}
