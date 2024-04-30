<?php

namespace KlaviyoV3Sdk;

if (!defined('_PS_VERSION_')) {
    exit;
}

use KlaviyoV3Sdk\Exception\KlaviyoAuthenticationException;
use KlaviyoV3Sdk\Exception\KlaviyoException;
use KlaviyoV3Sdk\Exception\KlaviyoRateLimitException;
use KlaviyoV3Sdk\Exception\KlaviyoApiException;
use DateTime;
use Exception;
use KlaviyoPsModule;

class KlaviyoV3Api
{
    /**
     * Host and versions
     */
    const BASE_URL = 'https://a.klaviyo.com/';
    const KLAVIYO_V3_REVISION = '2023-08-15';

    /**
     * Request methods
     */
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    /**
     * Error messages
     */
    const ERROR_INVALID_API_KEY = 'Invalid API Key.';
    const ERROR_NON_200_STATUS = 'Request Failed with HTTP Status Code: %s';
    const ERROR_API_CALL_FAILED = 'Request could be completed at this time, API call failed';
    const ERROR_MALFORMED_RESPONSE_BODY = 'Response from API could not be decoded from JSON, check response body';

    /**
     * Request options
     */
    const ACCEPT_KEY_HEADER = 'accept';
    const REVISION_KEY_HEADER = 'revision';
    const AUTHORIZATION_KEY_HEADER = 'Authorization';
    const KLAVIYO_API_KEY = 'Klaviyo-API-Key';
    const PROPERTIES = 'properties';
    const KLAVIYO_USER_AGENT_KEY = 'X-Klaviyo-User-Agent';
    const APPLICATION_JSON_HEADER_VALUE = 'application/json';
    const CONTENT_TYPE_HEADER = 'Content-type';

    /**
     * Payload options
     */
    const CUSTOMER_PROPERTIES_MAP = ['$email' => 'email', 'firstname' => 'first_name', 'lastname' => 'last_name'];
    const DATA_KEY_PAYLOAD = 'data';
    const LINKS_KEY_PAYLOAD = 'links';
    const NEXT_KEY_PAYLOAD = 'next';
    const TYPE_KEY_PAYLOAD = 'type';
    const ATTRIBUTE_KEY_PAYLOAD = 'attributes';
    const PROPERTIES_KEY_PAYLOAD = 'properties';
    const TIME_KEY_PAYLOAD = 'time';
    const VALUE_KEY_PAYLOAD = 'value';
    const VALUE_KEY_PAYLOAD_OLD = '$value';
    const METRIC_KEY_PAYLOAD = 'metric';
    const PROFILE_KEY_PAYLOAD = 'profile';
    const NAME_KEY_PAYLOAD = 'name';
    const EVENT_VALUE_PAYLOAD = 'event';
    const ID_KEY_PAYLOAD = 'id';
    const PROFILE_SUBSCRIPTION_BULK_CREATE_JOB_PAYLOAD_KEY = 'profile-subscription-bulk-create-job';
    const LIST_PAYLOAD_KEY = 'list';
    const RELATIONSHIPS_PAYLOAD_KEY = 'relationships';
    const PROFILES_PAYLOAD_KEY = 'profiles';
    const CUSTOM_SOURCE_PAYLOAD_KEY = 'custom_source';
    const PRESTASHOP_PAYLOAD_VALUE = 'Prestashop';
    const SERVICE_PAYLOAD_KEY = 'service';
    const PRESTASHOP_SERVICE_KEY = 'prestashop';

    /**
     * @var string
     */
    protected $private_key;

    /**
     * @var string
     */
    protected $public_key;

    /**
     * Constructor method for Base class.
     *
     * @param string $public_key Public key (account ID) for Klaviyo account
     * @param string $private_key Private API key for Klaviyo account
     */
    public function __construct($public_key, $private_key)
    {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    /**
     *  Build headers for the all Klaviyo API calls
     *
     * @return array
     */
    public function getHeaders()
    {
        $klaviyops = KlaviyoPsModule::getInstance();

        $headers = array(
            CURLOPT_HTTPHEADER => [
                self::REVISION_KEY_HEADER . ': ' . self::KLAVIYO_V3_REVISION,
                self::CONTENT_TYPE_HEADER . ': ' . self::APPLICATION_JSON_HEADER_VALUE,
                self::ACCEPT_KEY_HEADER . ': ' . self::APPLICATION_JSON_HEADER_VALUE,
                self::KLAVIYO_USER_AGENT_KEY . ': ' . 'prestashop-klaviyo/' . $klaviyops->version . ' ' . self::PRESTASHOP_PAYLOAD_VALUE . '/' . _PS_VERSION_ . ' PHP/' . phpversion(),
                self::AUTHORIZATION_KEY_HEADER . ': ' . self::KLAVIYO_API_KEY . ' ' . $this->private_key
            ]
        );

        return $headers;
    }


    /**
     * Query for all available lists in Klaviyo
     * https://developers.klaviyo.com/en/v2023-08-15/reference/get_lists
     *
     * @return array
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    public function getLists()
    {
        $response = $this->requestV3('api/lists/', self::HTTP_GET);
        $lists = $response[self::DATA_KEY_PAYLOAD];

        $next = $response[self::LINKS_KEY_PAYLOAD][self::NEXT_KEY_PAYLOAD];
        while ($next) {
            $next_qs = explode("?", $next)[1];
            $response = $this->requestV3("api/lists/?$next_qs", self::HTTP_GET);
            array_push($lists, ...$response[self::DATA_KEY_PAYLOAD]);

            $next = $response[self::LINKS_KEY_PAYLOAD][self::NEXT_KEY_PAYLOAD];
        }

        return $lists;
    }

    /**
     * Record an event for a customer on their Klaviyo profile
     * https://developers.klaviyo.com/en/v2023-08-15/reference/create_event
     *
     * @param $config
     * @return array
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    public function createEvent($config)
    {
        $event_time = new DateTime();
        $event_time->setTimestamp($config['time'] ?? time());

        $body = array(
            self::DATA_KEY_PAYLOAD => array(
                self::TYPE_KEY_PAYLOAD => self::EVENT_VALUE_PAYLOAD,
                self::ATTRIBUTE_KEY_PAYLOAD =>
                    $this->buildEventProperties($config['properties'], $event_time->format('Y-m-d\TH:i:sP'), $config['event']) +
                    $this->buildCustomerProperties($config['customer_properties'])
            )
        );

        return $this->requestV3('/api/events/', self::HTTP_POST, $body);
    }

    /**
     * Subscribe members to a Klaviyo list
     * https://developers.klaviyo.com/en/reference/subscribe_profiles
     *
     * @param $listId
     * @param $profiles
     * @return array
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    public function subscribeMembersToList($listId, $profiles)
    {
        $body = array(
            self::DATA_KEY_PAYLOAD => array(
                self::TYPE_KEY_PAYLOAD => self::PROFILE_SUBSCRIPTION_BULK_CREATE_JOB_PAYLOAD_KEY,
                self::ATTRIBUTE_KEY_PAYLOAD => array(
                    self::CUSTOM_SOURCE_PAYLOAD_KEY => self::PRESTASHOP_PAYLOAD_VALUE,
                    self::PROFILES_PAYLOAD_KEY => array(
                        self::DATA_KEY_PAYLOAD => $profiles
                    )
                ),
                self::RELATIONSHIPS_PAYLOAD_KEY => array(
                    self::LIST_PAYLOAD_KEY => array(
                        self::DATA_KEY_PAYLOAD => array(
                            self::TYPE_KEY_PAYLOAD => self::LIST_PAYLOAD_KEY,
                            self::ID_KEY_PAYLOAD => $listId
                        )
                    )
                )
            )
        );

        return $this->requestV3('/api/profile-subscription-bulk-create-jobs/', self::HTTP_POST, $body);
    }

    /**
     *  Request method used by all API methods to make calls
     *
     * @param $path
     * @param $method
     * @param $body
     * @param $attempt
     * @return array|string|null
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    protected function requestV3($path, $method, $body = null, $attempt = 0)
    {
        $curl = curl_init();
        $options = array(
                CURLOPT_URL => self::BASE_URL . $path,
            ) + $this->getHeaders() + $this->getDefaultCurlOptions($method);

        curl_setopt_array($curl, $options);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($curl);
        $phpVersionHttpCode = version_compare(phpversion(), '5.5.0', '>') ? CURLINFO_RESPONSE_CODE : CURLINFO_HTTP_CODE;
        $statusCode = curl_getinfo($curl, $phpVersionHttpCode);

        // In the event that the curl_exec fails for whatever reason, it responds with `false`,
        // Implementing a timeout and retry mechanism which will attempt the API call 3 times at 5 second intervals
        if (!$response && ($statusCode < 200 || $statusCode >= 300)){
            if($attempt < 3) {
                sleep(1);
                $this->requestV3($path, $method, $body, $attempt+1);
            } else {
                throw new KlaviyoApiException(self::ERROR_API_CALL_FAILED);
            }
        }

        $phpVersionHttpCode = version_compare(phpversion(), '5.5.0', '>') ? CURLINFO_RESPONSE_CODE : CURLINFO_HTTP_CODE;
        $statusCode = curl_getinfo($curl, $phpVersionHttpCode);
        curl_close($curl);

        return $this->handleAPIResponse($response, $statusCode);
    }

    /**
     * Build customer properties for the api/events endpoint
     *
     * @param $customerProperties
     * @return \array[][]
     */
    public function buildCustomerProperties($customerProperties): array
    {
        $kl_properties = [];

        foreach(array_keys(self::CUSTOMER_PROPERTIES_MAP) as $property_name){
            if (isset($customerProperties[$property_name])) {
                $kl_properties[self::CUSTOMER_PROPERTIES_MAP[$property_name]] = $customerProperties[$property_name];
                unset($customerProperties[$property_name]);
            }
        }

        return array(
            self::PROFILE_KEY_PAYLOAD => array(
                self::DATA_KEY_PAYLOAD => array(
                    self::TYPE_KEY_PAYLOAD => self::PROFILE_KEY_PAYLOAD,
                    self::ATTRIBUTE_KEY_PAYLOAD => $kl_properties,
                    self::PROPERTIES => $customerProperties,
                )
            )
        );
    }

    /**
     * Build Event Properties for the api/events endpoint
     *
     * @param $eventProperties
     * @param $time
     * @param $metric
     * @return array
     */
    public function buildEventProperties($eventProperties, $time, $metric): array
    {
        return array(
            self::PROPERTIES_KEY_PAYLOAD => $eventProperties,
            self::TIME_KEY_PAYLOAD => $time,
            self::VALUE_KEY_PAYLOAD => $eventProperties[self::VALUE_KEY_PAYLOAD_OLD],
            self::METRIC_KEY_PAYLOAD => array(
                self::DATA_KEY_PAYLOAD => array(
                    self::TYPE_KEY_PAYLOAD => self::METRIC_KEY_PAYLOAD,
                    self::ATTRIBUTE_KEY_PAYLOAD => array(
                        self::NAME_KEY_PAYLOAD => $metric,
                        self::SERVICE_PAYLOAD_KEY => self::PRESTASHOP_SERVICE_KEY
                    )
                )
            )
        );
    }

    /**
     * Get base options array for curl request.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function getDefaultCurlOptions($method = self::HTTP_POST)
    {
        return array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
        );
    }

    /**
     * Handle the API response and return the parsed data.
     *
     * @param string $response The raw API response.
     * @param int $statusCode The HTTP status code of the response.
     * @return array| string |null An array containing the parsed data or null on error.
     * @throws KlaviyoApiException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoRateLimitException
     */
    protected function handleAPIResponse($response, $statusCode)
    {
        $decoded_response = $this->decodeJsonResponse($response);
        if ($statusCode == 403) {
            throw new KlaviyoAuthenticationException(self::ERROR_INVALID_API_KEY, $statusCode);
        } elseif ($statusCode == 429) {
            throw new KlaviyoRateLimitException(
                'Rate Limit exceeded'
            );
        } elseif ($statusCode < 200 || $statusCode >= 300) {
            throw new KlaviyoApiException(isset($decoded_response['detail']) ? $decoded_response['detail'] : sprintf(self::ERROR_NON_200_STATUS, $statusCode), $statusCode);
        }

        return $decoded_response;
    }

    /**
     * Return decoded JSON response as associative or empty array.
     * Certain Klaviyo endpoints (such as Delete) return an empty string on success
     * and so PHP versions >= 7 will throw a JSON_ERROR_SYNTAX when trying to decode it
     *
     * @param string $response
     * @return mixed
     * @throws KlaviyoException
     */
    private function decodeJsonResponse($response)
    {
        if (!empty($response)) {
            try {
                return json_decode($response, true);
            } catch (Exception $e) {
                throw new KlaviyoException(self::ERROR_MALFORMED_RESPONSE_BODY);
            }
        }

        return json_decode('{}', true);
    }
}
