<?php

/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpServer\Http;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Client
{
    private static $instance;

    public const AUTH_BASIC = CURLAUTH_BASIC;

    public const AUTH_DIGEST = CURLAUTH_DIGEST;

    public const AUTH_GSSNEGOTIATE = CURLAUTH_GSSNEGOTIATE;

    public const AUTH_NTLM = CURLAUTH_NTLM;

    public const AUTH_ANY = CURLAUTH_ANY;

    public const AUTH_ANYSAFE = CURLAUTH_ANYSAFE;

    public const USER_AGENT = 'PHP Curl/2.3';

    private array $cookies = [];

    private array $headers = [];

    public \CurlHandle $curl;

    public $error = false;

    public $errorCode = 0;

    public ?string $errorMessage;

    public $curlError = false;

    public $curlErrorCode = 0;

    public ?string $curlErrorMessage;

    public $httpError = false;

    public $httpStatusCode = 0;

    public ?string $httpErrorMessage;

    public array $requestHeaders;

    public $responseHeaders = [];

    public $response;

    protected $responseHeaderContinue = false;

    private function __construct()
    {
        $this->init();
    }

    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \PrestaShopException('Cannot unserialize a singleton.');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        } else {
            self::$instance->reset();
        }

        return self::$instance;
    }

    private function init()
    {
        $this->curl = curl_init();
        $this->setUserAgent(self::USER_AGENT);
        $this->setOpt(CURLINFO_HEADER_OUT, true);
        $this->setOpt(CURLOPT_HEADER, false);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_HEADERFUNCTION, [$this, 'addResponseHeaderLine']);
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, 10);

        return $this;
    }

    private function formatNewlineJsonString($data)
    {
        $jsonArray = array_map(function ($dataItem) {
            return json_encode($dataItem, JSON_UNESCAPED_SLASHES);
        }, $data);

        $json = implode("\r\n", $jsonArray);

        return str_replace('\\u0000', '', $json);
    }

    public function setTimeout($timeout)
    {
        $this->setOpt(CURLOPT_TIMEOUT, $timeout);

        return $this;
    }

    public function addResponseHeaderLine($curl, $header_line)
    {
        $trimmed_header = trim($header_line, "\r\n");

        if ($trimmed_header === '') {
            $this->responseHeaderContinue = false;
        } elseif (strtolower($trimmed_header) === 'http/1.1 100 continue') {
            $this->responseHeaderContinue = true;
        } elseif (!$this->responseHeaderContinue) {
            $this->responseHeaders[] = $trimmed_header;
        }

        return strlen($header_line);
    }

    protected function exec()
    {
        $this->responseHeaders = [];
        $this->response = curl_exec($this->curl);
        $this->curlErrorCode = curl_errno($this->curl);
        $this->curlErrorMessage = curl_error($this->curl);
        $this->curlError = $this->getErrorCode() !== 0;
        $this->httpStatusCode = (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $this->httpError = $this->isError();
        $this->error = $this->curlError || $this->httpError;
        $this->errorCode = $this->error ? ($this->curlError ? (int) $this->getErrorCode() : $this->getHttpStatus()) : 0;
        $this->requestHeaders = preg_split('/\r\n/', curl_getinfo($this->curl, CURLINFO_HEADER_OUT), -1, PREG_SPLIT_NO_EMPTY);

        if ($this->error) {
            $this->httpErrorMessage = empty($this->responseHeaders) ? '' : reset($this->responseHeaders);
            $this->errorMessage = $this->curlError ? $this->getErrorMessage() : $this->httpErrorMessage;
        } else {
            $this->httpErrorMessage = '';
        }

        return $this->errorCode;
    }

    protected function preparePayload($data)
    {
        $this->setOpt(CURLOPT_POST, true);

        if (is_array($data) || is_object($data)) {
            $skip = false;
            foreach ($data as $value) {
                if ($value instanceof \CURLFile) {
                    $skip = true;
                }
            }

            if (!$skip) {
                $data = http_build_query($data);
            }
        }

        $this->setOpt(CURLOPT_POSTFIELDS, $data);
    }

    protected function prepareJsonPayload($data)
    {
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
    }

    protected function setHttpAuth($httpauth)
    {
        $this->setOpt(CURLOPT_HTTPAUTH, $httpauth);
    }

    public function get($url, $headers, $data = null)
    {
        $this->setHeaders($headers);

        if (!is_null($data) && is_array($data) && count($data) > 0) {
            $this->setOpt(CURLOPT_URL, $url . '?' . http_build_query($data));
        } else {
            $this->setOpt(CURLOPT_URL, $url);
        }

        $this->setOpt(CURLOPT_HTTPGET, true);
        $this->exec();

        return $this;
    }

    public function post($url, array $headers = [], array $data = [], $isFile = null)
    {
        if (is_null($isFile)) {
            $isFile = false;
        }

        $this->setHeaders($headers);
        $this->setOpt(CURLOPT_URL, $url);

        if ($isFile) {
            $temp = tmpfile();
            fwrite($temp, $this->formatNewlineJsonString($data));
            rewind($temp);

            $tempPath = stream_get_meta_data($temp)['uri'];
            $payload = ['file' => new \CURLFile($tempPath, 'text/plain', 'file')];
            $this->preparePayload($payload);
        } else {
            $this->prepareJsonPayload($data);
        }

        $this->exec();

        if ($isFile) {
            fclose($temp);
        }

        return $this;
    }

    public function put($url, $data = null, $payload = null)
    {
        if (is_null($data)) {
            $data = [];
        }

        if (is_null($payload)) {
            $payload = false;
        }

        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->exec();

        return $this;
    }

    public function patch($url, $data = null, $payload = null)
    {
        if (is_null($data)) {
            $data = [];
        }

        if (is_null($payload)) {
            $payload = false;
        }

        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->exec();

        return $this;
    }

    public function delete($url, $data = null, $payload = null)
    {
        if (is_null($data)) {
            $data = [];
        }

        if (is_null($payload)) {
            $payload = false;
        }

        if (!empty($data)) {
            if ($payload === false) {
                $url .= '?' . http_build_query($data);
            } else {
                $this->preparePayload($data);
            }
        }

        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->exec();

        return $this;
    }

    public function setBasicAuthentication($username, $password)
    {
        $this->setHttpAuth(self::AUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);

        return $this;
    }

    public function setHeaders($headers)
    {
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->headers[$key] = $key . ': ' . $value;
                $this->setOpt(CURLOPT_HTTPHEADER, array_values($this->headers));
            }
        }

        return $this;
    }

    public function setUserAgent($useragent)
    {
        $this->setOpt(CURLOPT_USERAGENT, $useragent);

        return $this;
    }

    public function setReferer($referer)
    {
        $this->setOpt(CURLOPT_REFERER, $referer);

        return $this;
    }

    public function setCookie($key, $value)
    {
        $this->cookies[$key] = $value;
        $this->setOpt(CURLOPT_COOKIE, http_build_query($this->cookies, '', '; '));

        return $this;
    }

    public function setOpt($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    public function getOpt($option)
    {
        return curl_getinfo($this->curl, $option);
    }

    public function getEndpoint()
    {
        return $this->getOpt(CURLINFO_EFFECTIVE_URL);
    }

    public function setVerbose($on = null)
    {
        if (is_null($on)) {
            $on = true;
        }

        $this->setOpt(CURLOPT_VERBOSE, $on);

        return $this;
    }

    public function reset()
    {
        $this->close();
        $this->cookies = [];
        $this->headers = [];
        $this->error = false;
        $this->errorCode = 0;
        $this->errorMessage = null;
        $this->curlError = false;
        $this->curlErrorCode = 0;
        $this->curlErrorMessage = null;
        $this->httpError = false;
        $this->httpStatusCode = 0;
        $this->httpErrorMessage = null;
        $this->requestHeaders = [];
        $this->responseHeaders = [];
        $this->response = false;
        $this->init();

        return $this;
    }

    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }

        return $this;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function isInfo()
    {
        return $this->getHttpStatus() >= 100 && $this->getHttpStatus() < 200;
    }

    public function isSuccess()
    {
        return $this->getHttpStatus() >= 200 && $this->getHttpStatus() < 300;
    }

    public function isRedirect()
    {
        return $this->getHttpStatus() >= 300 && $this->getHttpStatus() < 400;
    }

    public function isError()
    {
        return $this->getHttpStatus() >= 400 && $this->getHttpStatus() < 600;
    }

    public function isClientError()
    {
        return $this->getHttpStatus() >= 400 && $this->getHttpStatus() < 500;
    }

    public function isServerError()
    {
        return $this->getHttpStatus() >= 500 && $this->getHttpStatus() < 600;
    }

    public function getResponseHeaders($headerKey = null)
    {
        $headers = [];
        $headerKey = strtolower($headerKey);

        foreach ($this->responseHeaders as $header) {
            $parts = explode(':', $header, 2);

            $key = isset($parts[0]) ? $parts[0] : '';
            $value = isset($parts[1]) ? $parts[1] : '';

            $headers[trim(strtolower($key))] = trim($value);
        }

        if ($headerKey) {
            return isset($headers[$headerKey]) ? $headers[$headerKey] : false;
        }

        return $headers;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getErrorCode()
    {
        return $this->curlErrorCode;
    }

    public function getErrorMessage()
    {
        return $this->curlErrorMessage;
    }

    public function getHttpStatus()
    {
        return $this->httpStatusCode;
    }
}
