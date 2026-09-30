<?php declare(strict_types=1);

namespace SebastianBergmann\CodeUnit;

use function count;
use function file;
use function file_exists;
use function is_readable;
use function range;
use function sprintf;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;


abstract class CodeUnit
{
    private readonly string $name;
    private readonly string $sourceFileName;

    
    private readonly array $sourceLines;

    
    public static function forClass(string $className): ClassUnit
    {
        self::ensureUserDefinedClass($className);

        $reflector = self::reflectorForClass($className);

        return new ClassUnit(
            $className,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forClassMethod(string $className, string $methodName): ClassMethodUnit
    {
        self::ensureUserDefinedClass($className);

        $reflector = self::reflectorForClassMethod($className, $methodName);

        return new ClassMethodUnit(
            $className . '::' . $methodName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forFileWithAbsolutePath(string $path): FileUnit
    {
        self::ensureFileExistsAndIsReadable($path);

        return new FileUnit(
            $path,
            $path,
            range(
                1,
                count(file($path))
            )
        );
    }

    
    public static function forInterface(string $interfaceName): InterfaceUnit
    {
        self::ensureUserDefinedInterface($interfaceName);

        $reflector = self::reflectorForClass($interfaceName);

        return new InterfaceUnit(
            $interfaceName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forInterfaceMethod(string $interfaceName, string $methodName): InterfaceMethodUnit
    {
        self::ensureUserDefinedInterface($interfaceName);

        $reflector = self::reflectorForClassMethod($interfaceName, $methodName);

        return new InterfaceMethodUnit(
            $interfaceName . '::' . $methodName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forTrait(string $traitName): TraitUnit
    {
        self::ensureUserDefinedTrait($traitName);

        $reflector = self::reflectorForClass($traitName);

        return new TraitUnit(
            $traitName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forTraitMethod(string $traitName, string $methodName): TraitMethodUnit
    {
        self::ensureUserDefinedTrait($traitName);

        $reflector = self::reflectorForClassMethod($traitName, $methodName);

        return new TraitMethodUnit(
            $traitName . '::' . $methodName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    public static function forFunction(string $functionName): FunctionUnit
    {
        $reflector = self::reflectorForFunction($functionName);

        if (!$reflector->isUserDefined()) {
            throw new InvalidCodeUnitException(
                sprintf(
                    '"%s" is not a user-defined function',
                    $functionName
                )
            );
        }

        return new FunctionUnit(
            $functionName,
            $reflector->getFileName(),
            range(
                $reflector->getStartLine(),
                $reflector->getEndLine()
            )
        );
    }

    
    private function __construct(string $name, string $sourceFileName, array $sourceLines)
    {
        $this->name           = $name;
        $this->sourceFileName = $sourceFileName;
        $this->sourceLines    = $sourceLines;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sourceFileName(): string
    {
        return $this->sourceFileName;
    }

    
    public function sourceLines(): array
    {
        return $this->sourceLines;
    }

    public function isClass(): bool
    {
        return false;
    }

    public function isClassMethod(): bool
    {
        return false;
    }

    public function isInterface(): bool
    {
        return false;
    }

    public function isInterfaceMethod(): bool
    {
        return false;
    }

    public function isTrait(): bool
    {
        return false;
    }

    public function isTraitMethod(): bool
    {
        return false;
    }

    public function isFunction(): bool
    {
        return false;
    }

    public function isFile(): bool
    {
        return false;
    }

    
    private static function ensureFileExistsAndIsReadable(string $path): void
    {
        if (!(file_exists($path) && is_readable($path))) {
            throw new InvalidCodeUnitException(
                sprintf(
                    'File "%s" does not exist or is not readable',
                    $path
                )
            );
        }
    }

    
    private static function ensureUserDefinedClass(string $className): void
    {
        try {
            $reflector = new ReflectionClass($className);

            if ($reflector->isInterface()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is an interface and not a class',
                        $className
                    )
                );
            }

            if ($reflector->isTrait()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is a trait and not a class',
                        $className
                    )
                );
            }

            if (!$reflector->isUserDefined()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is not a user-defined class',
                        $className
                    )
                );
            }
            
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }

    
    private static function ensureUserDefinedInterface(string $interfaceName): void
    {
        try {
            $reflector = new ReflectionClass($interfaceName);

            if (!$reflector->isInterface()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is not an interface',
                        $interfaceName
                    )
                );
            }

            if (!$reflector->isUserDefined()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is not a user-defined interface',
                        $interfaceName
                    )
                );
            }
            
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }

    
    private static function ensureUserDefinedTrait(string $traitName): void
    {
        try {
            $reflector = new ReflectionClass($traitName);

            if (!$reflector->isTrait()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is not a trait',
                        $traitName
                    )
                );
            }

            
            if (!$reflector->isUserDefined()) {
                throw new InvalidCodeUnitException(
                    sprintf(
                        '"%s" is not a user-defined trait',
                        $traitName
                    )
                );
            }
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }

    
    private static function reflectorForClass(string $className): ReflectionClass
    {
        try {
            return new ReflectionClass($className);
            
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }

    
    private static function reflectorForClassMethod(string $className, string $methodName): ReflectionMethod
    {
        try {
            return new ReflectionMethod($className, $methodName);
            
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }

    
    private static function reflectorForFunction(string $functionName): ReflectionFunction
    {
        try {
            return new ReflectionFunction($functionName);
            
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        
    }
}
