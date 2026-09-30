<?php



if (\PHP_VERSION_ID < 80400) {
    
    final class ReflectionConstant
    {
        
        public $name;

        private $value;
        private $deprecated;
        private $persistent;

        private static $persistentConstants = [];

        public function __construct(string $name)
        {
            if (!defined($name) || false !== strpos($name, '::')) {
                throw new ReflectionException("Constant \"$name\" does not exist");
            }

            $this->name = ltrim($name, '\\');
            $deprecated = false;
            $eh = set_error_handler(static function ($type, $msg, $file, $line) use ($name, &$deprecated, &$eh) {
                if (\E_DEPRECATED === $type && "Constant $name is deprecated" === $msg) {
                    return $deprecated = true;
                }

                return $eh && $eh($type, $msg, $file, $line);
            });

            try {
                $this->value = constant($name);
                $this->deprecated = $deprecated;
            } finally {
                restore_error_handler();
            }
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getValue()
        {
            return $this->value;
        }

        public function getNamespaceName(): string
        {
            if (false === $slashPos = strrpos($this->name, '\\')) {
                return '';
            }

            return substr($this->name, 0, $slashPos);
        }

        public function getShortName(): string
        {
            if (false === $slashPos = strrpos($this->name, '\\')) {
                return $this->name;
            }

            return substr($this->name, $slashPos + 1);
        }

        public function isDeprecated(): bool
        {
            return $this->deprecated;
        }

        public function __toString(): string
        {
            
            
            
            
            if (!self::$persistentConstants) {
                $persistentConstants = get_defined_constants(true);
                unset($persistentConstants['user']);
                foreach ($persistentConstants as $constants) {
                    self::$persistentConstants += $constants;
                }
            }
            $persistent = array_key_exists($this->name, self::$persistentConstants);

            
            
            $result = 'Constant [ ';
            if ($persistent || $this->deprecated) {
                $result .= '<';
                if ($persistent) {
                    $result .= 'persistent';
                    if ($this->deprecated) {
                        $result .= ', ';
                    }
                }
                if ($this->deprecated) {
                    $result .= 'deprecated';
                }
                $result .= '> ';
            }
            
            if (is_object($this->value)) {
                $result .= \PHP_VERSION_ID >= 80000 ? get_debug_type($this->value) : gettype($this->value);
            } elseif (is_bool($this->value)) {
                $result .= 'bool';
            } elseif (is_int($this->value)) {
                $result .= 'int';
            } elseif (is_float($this->value)) {
                $result .= 'float';
            } elseif (null === $this->value) {
                $result .= 'null';
            } else {
                $result .= gettype($this->value);
            }
            $result .= ' ';
            $result .= $this->name;
            $result .= ' ] { ';
            if (is_array($this->value)) {
                $result .= 'Array';
            } else {
                
                
                
                $result .= (string) $this->value;
            }
            $result .= " }\n";

            return $result;
        }

        public function __sleep(): array
        {
            throw new Exception("Serialization of 'ReflectionConstant' is not allowed");
        }

        public function __wakeup(): void
        {
            throw new Exception("Unserialization of 'ReflectionConstant' is not allowed");
        }
    }
}
