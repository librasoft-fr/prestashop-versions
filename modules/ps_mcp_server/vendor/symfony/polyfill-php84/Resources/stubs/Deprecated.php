<?php



if (\PHP_VERSION_ID < 80100) {
    #[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION | Attribute::TARGET_CLASS_CONSTANT)]
    final class Deprecated
    {
        
        public ?string $message;

        
        public ?string $since;

        public function __construct(?string $message = null, ?string $since = null)
        {
            $this->message = $message;
            $this->since = $since;
        }
    }
} elseif (\PHP_VERSION_ID < 80400) {
    require dirname(__DIR__).'/Deprecated.php';
}
