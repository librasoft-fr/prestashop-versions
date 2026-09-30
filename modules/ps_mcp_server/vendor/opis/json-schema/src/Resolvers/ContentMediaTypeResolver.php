<?php


namespace Opis\JsonSchema\Resolvers;

use finfo;
use Opis\JsonSchema\ContentMediaType;

class ContentMediaTypeResolver
{
    
    protected array $media;

    
    protected $defaultMedia = null;

    
    public function __construct(array $media = [], $defaultMedia = null)
    {
        $media += [
            'application/json' => self::class . '::IsJsonEncoded',
        ];

        $this->media = $media;
        $this->defaultMedia = $defaultMedia ?? self::class . '::IsEncodedAsType';
    }

    
    public function resolve(string $name)
    {
        return $this->media[$name] ?? $this->defaultMedia;
    }

    
    public function register(string $name, ContentMediaType $media): self
    {
        $this->media[$name] = $media;

        return $this;
    }

    
    public function registerCallable(string $name, callable $media): self
    {
        $this->media[$name] = $media;

        return $this;
    }

    
    public function unregister(string $name): bool
    {
        if (isset($this->media[$name])) {
            unset($this->media[$name]);

            return true;
        }

        return false;
    }

    
    public function setDefaultHandler($handler): self
    {
        $this->defaultMedia = $handler;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'media' => $this->media,
            'defaultMedia' => $this->defaultMedia,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->media = $data['media'];
        $this->defaultMedia = $data['defaultMedia'];
    }

    public static function IsJsonEncoded(string $value,
         string $type): bool
    {
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function IsEncodedAsType(string $value, string $type): bool
    {
        
        static $finfo = false;

        if ($finfo === false) {
            if (!class_exists(finfo::class)) {
                $finfo = null;
                return false;
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        } elseif (!$finfo) {
            return false;
        }

        $r = $finfo->buffer($value);

        return $r == $type || $r == 'application/x-empty';
    }
}