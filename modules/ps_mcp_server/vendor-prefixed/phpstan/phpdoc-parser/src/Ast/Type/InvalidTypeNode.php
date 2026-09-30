<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\Type;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\ParserException;

class InvalidTypeNode implements TypeNode
{
    use NodeAttributes;

    private array $exceptionArgs;

    public function __construct(ParserException $exception)
    {
        $this->exceptionArgs = [
            $exception->getCurrentTokenValue(),
            $exception->getCurrentTokenType(),
            $exception->getCurrentOffset(),
            $exception->getExpectedTokenType(),
            $exception->getExpectedTokenValue(),
            $exception->getCurrentTokenLine(),
        ];
    }

    public function getException(): ParserException
    {
        return new ParserException(...$this->exceptionArgs);
    }

    public function __toString(): string
    {
        return '*Invalid type*';
    }

    public static function __set_state(array $properties): self
    {
        $exception = new ParserException(...$properties['exceptionArgs']);
        $instance = new self($exception);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
