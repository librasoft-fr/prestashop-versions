<?php declare(strict_types=1);



namespace Monolog\Handler\Curl;

use CurlHandle;


final class Util
{
    
    private static $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    
    public static function execute($ch, int $retries = 5, bool $closeAfterDone = true)
    {
        while ($retries--) {
            $curlResponse = curl_exec($ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($ch);

                if (false === in_array($curlErrno, self::$retriableErrorCodes, true) || !$retries) {
                    $curlError = curl_error($ch);

                    if (\PHP_VERSION_ID < 80000 && $closeAfterDone) {
                        curl_close($ch);
                    }

                    throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }

                continue;
            }

            if (\PHP_VERSION_ID < 80000 && $closeAfterDone) {
                curl_close($ch);
            }

            return $curlResponse;
        }

        return false;
    }
}
