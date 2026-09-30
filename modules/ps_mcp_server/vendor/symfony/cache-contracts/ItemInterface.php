<?php



namespace Symfony\Contracts\Cache;

use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;


interface ItemInterface extends CacheItemInterface
{
    
    public const METADATA_EXPIRY = 'expiry';

    
    public const METADATA_CTIME = 'ctime';

    
    public const METADATA_TAGS = 'tags';

    
    public const RESERVED_CHARACTERS = '{}()/\@:';

    
    public function tag(string|iterable $tags): static;

    
    public function getMetadata(): array;
}
