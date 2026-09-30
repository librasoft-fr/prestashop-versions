<?php



namespace Symfony\Component\VarExporter\Internal;

use Symfony\Component\Serializer\Attribute\Ignore;

if (\PHP_VERSION_ID >= 80300) {
    
    trait LazyObjectTrait
    {
        #[Ignore]
        private readonly LazyObjectState $lazyObjectState;
    }
} else {
    
    trait LazyObjectTrait
    {
        #[Ignore]
        private LazyObjectState $lazyObjectState;
    }
}
