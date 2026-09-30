<?php declare(strict_types=1);



namespace Composer\FilterList\FilterListProvider;

use Composer\FilterList\FilterListApiClient;
use Composer\FilterList\FilterListEntryBuilder;
use Composer\FilterList\Source\UrlSource;
use Composer\Repository\FilterListProviderInterface;
use Composer\Util\HttpDownloader;


class UrlSourceFilterListProvider implements FilterListProviderInterface
{
    
    private $apiClient;
    
    private $entryBuilder;
    
    private $source;

    public function __construct(
        HttpDownloader $httpDownloader,
        UrlSource $source
    ) {
        $this->apiClient = new FilterListApiClient($httpDownloader);
        $this->entryBuilder = new FilterListEntryBuilder();
        $this->source = $source;
    }

    public function hasFilter(): bool
    {
        return true;
    }

    
    public function getFilter(array $packageConstraintMap, array $configuredLists): array
    {
        $response = $this->apiClient->postPurls($this->source->url, $packageConstraintMap, [$this->source->listName]);

        $decoded = $response->decodeJson();
        $entries = isset($decoded['filter']) && is_array($decoded['filter']) ? $decoded['filter'] : [];

        
        $rawByList = [$this->source->listName => $entries];

        return ['filter' => $this->entryBuilder->build($rawByList, $packageConstraintMap)];
    }

    public function getFilterLists(): array
    {
        return [$this->source->listName];
    }
}
