<?php declare(strict_types=1);



namespace Composer\FilterList\Source;


class SourceValidator
{
    
    public function validate(string $listName, array $source): UrlSource
    {
        if (!isset($source['type'])) {
            throw new \RuntimeException('Source configuration is missing the "type" field.');
        }

        if ($source['type'] === 'url') {
            return $this->validateUrlSource($listName, $source);
        }

        throw new \RuntimeException('Unsupported source type "'.$source['type'].'". Only "url" is currently supported.');
    }

    
    private function validateUrlSource(string $listName, array $source): UrlSource
    {
        if (!isset($source['url']) || !is_string($source['url'])) {
            throw new \RuntimeException('Source configuration is missing a string "url" field.');
        }
        if (str_starts_with($source['url'], 'https://') === false) {
            throw new \RuntimeException('Source URL for policy list "'.$listName.'" must start with "https://"; got "'.$source['url'].'".');
        }

        return new UrlSource($listName, $source['url']);
    }
}
