<?php declare(strict_types=1);



namespace Composer\FilterList;

use Composer\Util\Http\Response;
use Composer\Util\HttpDownloader;


class FilterListApiClient
{
    
    private $httpDownloader;

    public function __construct(HttpDownloader $httpDownloader)
    {
        $this->httpDownloader = $httpDownloader;
    }

    
    public function postPurls(string $url, array $packageConstraintMap, array $configuredLists): Response
    {
        $purls = array_map(static function (string $packageName): string {
            return 'pkg://composer/' . $packageName;
        }, array_keys($packageConstraintMap));

        $body = [
            'packages' => $purls,
            'lists' => $configuredLists,
        ];

        $options = [];
        $options['http']['method'] = 'POST';
        $options['http']['header'][] = 'Content-type: application/json';
        $options['http']['timeout'] = 10;
        $options['http']['content'] = json_encode($body);

        return $this->httpDownloader->get($url, $options);
    }
}
