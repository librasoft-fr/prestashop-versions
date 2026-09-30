<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Void_;
use Webmozart\Assert\Assert;

use function array_keys;
use function array_map;
use function explode;
use function implode;
use function is_string;
use function preg_match;
use function sort;
use function strpos;
use function substr;
use function trigger_error;
use function trim;
use function var_export;

use const E_USER_DEPRECATED;


final class Method extends BaseTag implements Factory\StaticMethod
{
    protected string $name = 'method';

    private string $methodName;

    private bool $isStatic;

    private Type $returnType;

    private bool $returnsReference;

    
    private array $parameters;

    
    public function __construct(
        string $methodName,
        array $arguments = [],
        ?Type $returnType = null,
        bool $static = false,
        ?Description $description = null,
        bool $returnsReference = false,
        ?array $parameters = null
    ) {
        Assert::stringNotEmpty($methodName);

        if ($returnType === null) {
            $returnType = new Void_();
        }

        $arguments = $this->filterArguments($arguments);

        $this->methodName       = $methodName;
        $this->returnType       = $returnType;
        $this->isStatic         = $static;
        $this->description      = $description;
        $this->returnsReference = $returnsReference;
        $this->parameters = $parameters ?? $this->fromLegacyArguments($arguments);
    }

    
    public static function create(
        string $body,
        ?TypeResolver $typeResolver = null,
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null
    ): ?self {
        trigger_error(
            'Create using static factory is deprecated, this method should not be called directly
             by library consumers',
            E_USER_DEPRECATED
        );
        Assert::stringNotEmpty($body);
        Assert::notNull($typeResolver);
        Assert::notNull($descriptionFactory);

        
        
        
        
        
        
        
        
        
        
        
        if (
            !preg_match(
                '/^
                # Static keyword
                # Declares a static method ONLY if type is also present
                (?:
                    (static)
                    \s+
                )?
                # Return type
                (?:
                    (
                        (?:[\w\|_\\\\]*\$this[\w\|_\\\\]*)
                        |
                        (?:
                            (?:[\w\|_\\\\]+)
                            # array notation
                            (?:\[\])*
                        )*+
                    )
                    \s+
                )?
                # Returns reference
                (?:
                    (&)
                    \s*
                )?
                # Method name
                ([\w_]+)
                # Arguments
                (?:
                    \(([^\)]*)\)
                )?
                \s*
                # Description
                (.*)
            $/sux',
                $body,
                $matches
            )
        ) {
            return null;
        }

        [, $static, $returnType, $returnsReference, $methodName, $argumentLines, $description] = $matches;

        $static = $static === 'static';

        if ($returnType === '') {
            $returnType = 'void';
        }

        $returnsReference = $returnsReference === '&';

        $returnType  = $typeResolver->resolve($returnType, $context);
        $description = $descriptionFactory->create($description, $context);

        
        $arguments = [];
        if ($argumentLines !== '') {
            $argumentsExploded = explode(',', $argumentLines);
            foreach ($argumentsExploded as $argument) {
                $argument = explode(' ', self::stripRestArg(trim($argument)), 2);
                if (strpos($argument[0], '$') === 0) {
                    $argumentName = substr($argument[0], 1);
                    $argumentType = new Mixed_();
                } else {
                    $argumentType = $typeResolver->resolve($argument[0], $context);
                    $argumentName = '';
                    if (isset($argument[1])) {
                        $argument[1]  = self::stripRestArg($argument[1]);
                        $argumentName = substr($argument[1], 1);
                    }
                }

                $arguments[] = ['name' => $argumentName, 'type' => $argumentType];
            }
        }

        return new static(
            $methodName,
            $arguments,
            $returnType,
            $static,
            $description,
            $returnsReference
        );
    }

    
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    
    public function getArguments(): array
    {
        trigger_error('Method deprecated, use ::getParameters()', E_USER_DEPRECATED);

        return array_map(
            static function (MethodParameter $methodParameter) {
                return ['name' => $methodParameter->getName(), 'type' => $methodParameter->getType()];
            },
            $this->parameters
        );
    }

    
    public function getParameters(): array
    {
        return $this->parameters;
    }

    
    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function getReturnType(): Type
    {
        return $this->returnType;
    }

    public function returnsReference(): bool
    {
        return $this->returnsReference;
    }

    public function __toString(): string
    {
        $arguments = [];
        foreach ($this->parameters as $parameter) {
            $arguments[] = (string) $parameter;
        }

        $argumentStr = '(' . implode(', ', $arguments) . ')';

        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        $static = $this->isStatic ? 'static' : '';

        $returnType = (string) $this->returnType;

        $methodName = $this->methodName;

        $reference = $this->returnsReference ? '&' : '';

        return $static
            . ($returnType !== '' ? ($static !== '' ? ' ' : '') . $returnType : '')
            . ($methodName !== '' ? ($static !== '' || $returnType !== '' ? ' ' : '') . $reference . $methodName : '')
            . $argumentStr
            . ($description !== '' ? ' ' . $description : '');
    }

    
    private function filterArguments(array $arguments = []): array
    {
        $result = [];
        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                $argument = ['name' => $argument];
            }

            if (!isset($argument['type'])) {
                $argument['type'] = new Mixed_();
            }

            $keys = array_keys($argument);
            sort($keys);
            if ($keys !== ['name', 'type']) {
                throw new InvalidArgumentException(
                    'Arguments can only have the "name" and "type" fields, found: ' . var_export($keys, true)
                );
            }

            $result[] = $argument;
        }

        return $result;
    }

    private static function stripRestArg(string $argument): string
    {
        if (strpos($argument, '...') === 0) {
            $argument = trim(substr($argument, 3));
        }

        return $argument;
    }

    
    private function fromLegacyArguments(array $arguments): array
    {
        trigger_error(
            'Create method parameters via legacy format is deprecated add parameters via the constructor',
            E_USER_DEPRECATED
        );

        return array_map(
            static function ($arg) {
                return new MethodParameter(
                    $arg['name'],
                    $arg['type']
                );
            },
            $arguments
        );
    }
}
