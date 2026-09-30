<?php declare(strict_types=1);



namespace Monolog\Attribute;


#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsMonologProcessor
{
    
    public $channel = null;
    
    public $handler = null;
    
    public $method = null;
    
    
    public function __construct(
        ?string $channel = null,
        ?string $handler = null,
        ?string $method = null
    ) {
        $this->channel = $channel;
        $this->handler = $handler;
        $this->method = $method;
    }
} 
