<?php



namespace Symfony\Contracts\Service\Attribute;

use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;


#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class SubscribedService
{
    
    public array $attributes;

    
    public function __construct(
        public ?string $key = null,
        public ?string $type = null,
        public bool $nullable = false,
        array|object $attributes = [],
    ) {
        $this->attributes = \is_array($attributes) ? $attributes : [$attributes];
    }
}
