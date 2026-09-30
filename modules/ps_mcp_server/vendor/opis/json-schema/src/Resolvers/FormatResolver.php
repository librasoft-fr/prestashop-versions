<?php


namespace Opis\JsonSchema\Resolvers;

use Opis\JsonSchema\{Helper, Format, JsonPointer, Uri};
use Opis\JsonSchema\Formats\{
    DateTimeFormats, IriFormats, MiscFormats, UriFormats
};

class FormatResolver
{
    
    protected array $formats = [];

    
    public function __construct()
    {
        $this->formats = [
            'string' => [
                'date' => DateTimeFormats::class . '::date',
                'time' => DateTimeFormats::class . '::time',
                'date-time' => DateTimeFormats::class . '::dateTime',
                'duration' => DateTimeFormats::class . '::duration',

                'uri' => UriFormats::class . '::uri',
                'uri-reference' => UriFormats::class . '::uriReference',
                'uri-template' => UriFormats::class . '::uriTemplate',

                'regex' => Helper::class . '::isValidPattern',
                'ipv4' => MiscFormats::class . '::ipv4',
                'ipv6' => MiscFormats::class . '::ipv6',
                'uuid' => MiscFormats::class . '::uuid',

                'email' => MiscFormats::class . '::email',
                'hostname' => Uri::class . '::isValidHost',

                'json-pointer' => JsonPointer::class . '::isAbsolutePointer',
                'relative-json-pointer' => JsonPointer::class . '::isRelativePointer',

                'idn-hostname' => IriFormats::class . '::idnHostname',
                'idn-email' => IriFormats::class . '::idnEmail',
                'iri' => IriFormats::class . '::iri',
                'iri-reference' => IriFormats::class . '::iriReference',
            ],
        ];
    }

    
    public function resolve(string $name, string $type)
    {
        return $this->formats[$type][$name] ?? null;
    }

    
    public function resolveAll(string $name): ?array
    {
        $list = null;

        foreach ($this->formats as $type => $items) {
            if (isset($items[$name])) {
                $list[$type] = $items[$name];
            }
        }

        return $list;
    }

    
    public function register(string $type, string $name, Format $format): self
    {
        $this->formats[$type][$name] = $format;

        return $this;
    }

    
    public function registerCallable(string $type, string $name, callable $format): self
    {
        $this->formats[$type][$name] = $format;

        return $this;
    }

    
    public function unregister(string $type, string $name): bool
    {
        if (isset($this->formats[$type][$name])) {
            unset($this->formats[$type][$name]);

            return true;
        }

        return false;
    }

    public function __serialize(): array
    {
        return ['formats' => $this->formats];
    }

    public function __unserialize(array $data): void
    {
        $this->formats = $data['formats'];
    }
}