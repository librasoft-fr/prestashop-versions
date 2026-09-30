<?php

declare(strict_types=1);

namespace PsMcpServerDeps\PHPStan\PhpDocParser\Ast\PhpDoc;

use PsMcpServerDeps\PHPStan\PhpDocParser\Ast\NodeAttributes;
use PsMcpServerDeps\PHPStan\PhpDocParser\Parser\ParserException;

class InvalidTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    public string $value;

    private array $exceptionArgs;

    public function __construct(string $value, ParserException $exception)
    {
        $this->value = $value;
        $this->exceptionArgs = [
            $exception->getCurrentTokenValue(),
            $exception->getCurrentTokenType(),
            $exception->getCurrentOffset(),
            $exception->getExpectedTokenType(),
            $exception->getExpectedTokenValue(),
            $exception->getCurrentTokenLine(),
        ];
    }

    public function __get(string $name): ?ParserException
    {
        if ($name !== 'exception') {
            \trigger_error(\sprintf('Undefined property: %s::$%s', self::class, $name), \E_USER_WARNING);

            return null;
        }

        return new ParserException(...$this->exceptionArgs);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function __set_state(array $properties): self
    {
        $exception = new ParserException(...$properties['exceptionArgs']);
        $instance = new self($properties['value'], $exception);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }

        return $instance;
    }
}
