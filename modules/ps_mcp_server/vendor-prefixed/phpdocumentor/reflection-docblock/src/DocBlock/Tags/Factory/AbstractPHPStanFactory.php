<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\Factory;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tag;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context as TypeContext;
use PsMcpServerDeps\PHPStan\PhpDocParser\Lexer\Lexer;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\ConstExprParser;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\PhpDocParser;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\TokenIterator;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\TypeParser;
use PsMcpServerDeps\PHPStan\PhpDocParser\ParserConfig;

class AbstractPHPStanFactory implements Factory
{
    private PhpDocParser $parser;
    private Lexer $lexer;

    private array $factories;

    public function __construct(PHPStanFactory ...$factories)
    {
        if (\class_exists(ParserConfig::class)) {
            $config = new ParserConfig(['indexes' => true, 'lines' => true]);
            $this->lexer = new Lexer($config);
            $constParser = new ConstExprParser($config);
            $this->parser = new PhpDocParser(
                $config,
                new TypeParser($config, $constParser),
                $constParser
            );
        } else {
            $this->lexer = new Lexer(true);
            $constParser = new ConstExprParser(true, true, ['lines' => true, 'indexes' => true]);
            $this->parser = new PhpDocParser(
                new TypeParser($constParser, true, ['lines' => true, 'indexes' => true]),
                $constParser,
                true,
                true,
                ['lines' => true, 'indexes' => true],
                true
            );
        }

        $this->factories = $factories;
    }

    public function create(string $tagLine, ?TypeContext $context = null): Tag
    {
        $tokens = $this->tokenizeLine($tagLine . "\n");
        $ast = $this->parser->parseTag($tokens);
        if (\property_exists($ast->value, 'description') === true) {
            $ast->value->setAttribute(
                'description',
                \rtrim($ast->value->description . $tokens->joinUntil(Lexer::TOKEN_END), "\n")
            );
        }

        if ($context === null) {
            $context = new TypeContext('');
        }

        try {
            foreach ($this->factories as $factory) {
                if ($factory->supports($ast, $context)) {
                    return $factory->create($ast, $context);
                }
            }
        } catch (\RuntimeException $e) {
            return InvalidTag::create((string) $ast->value, 'method')->withError($e);
        }

        return InvalidTag::create(
            (string) $ast->value,
            $ast->name
        );
    }

    private function tokenizeLine(string $tagLine): TokenIterator
    {
        $tokens = $this->lexer->tokenize($tagLine);
        $fixed = [];
        foreach ($tokens as $token) {
            if (($token[1] === Lexer::TOKEN_PHPDOC_EOL) && \rtrim($token[0], " \t") !== $token[0]) {
                $fixed[] = [
                    \rtrim($token[Lexer::VALUE_OFFSET], " \t"),
                    Lexer::TOKEN_PHPDOC_EOL,
                    $token[2] ?? 0,
                ];
                $fixed[] = [
                    \ltrim($token[Lexer::VALUE_OFFSET], "\n\r"),
                    Lexer::TOKEN_HORIZONTAL_WS,
                    ($token[2] ?? 0) + 1,
                ];
                continue;
            }

            $fixed[] = $token;
        }

        return new TokenIterator($fixed);
    }
}
