<?php

namespace Prestashop\ModuleLibMboInstaller;

class HttpClient
{
    private $curl;
    
    private $headers = [];
    
    private $options = [];
    
    private $baseUrl = '';

    
    public function __construct($baseUrl)
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('cURL extension is not loaded');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->curl = curl_init();

        
        $this->setDefaultOptions();
    }

    
    private function setDefaultOptions()
    {
        $this->options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
    }

    
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    
    public function addHeader($name, $value)
    {
        $this->headers[] = "$name: $value";

        return $this;
    }

    
    public function setOptions($options)
    {
        $this->options = $options + $this->options;

        return $this;
    }

    
    public function request($method, $url, $data = null)
    {
        $url = $this->baseUrl . '/' . ltrim($url, '/');

        $options = $this->options + [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if ($data !== null) {
            if (is_array($data)) {
                $data = http_build_query($data);
            }

            if ($method === 'GET') {
                
                $options[CURLOPT_URL] .= '?' . $data;
            } else {
                $options[CURLOPT_POSTFIELDS] = $data;
            }
        }

        curl_setopt_array($this->curl, $options);

        $response = curl_exec($this->curl);

        if ($response === false) {
            throw new \RuntimeException(sprintf('cURL error (%s): %s', curl_errno($this->curl), curl_error($this->curl)));
        }

        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        
        $headerStr = substr((string) $response, 0, $headerSize);
        $body = substr((string) $response, $headerSize);
        $error = curl_error($this->curl);

        
        $headers = [];
        foreach (explode("\r\n", $headerStr) as $line) {
            if (preg_match('/^([^:]+):(.+)$/', $line, $matches)) {
                $headers[trim($matches[1])] = trim($matches[2]);
            }
        }

        return new Response(
            $httpCode,
            $body,
            $headers
        );
    }

    
    public function get($url, $params = [])
    {
        return $this->request('GET', $url, $params);
    }

    
    public function post($url, $data = [])
    {
        return $this->request('POST', $url, $data);
    }

    
    public function put($url, $data = [])
    {
        return $this->request('PUT', $url, $data);
    }

    
    public function delete($url, $params = [])
    {
        return $this->request('DELETE', $url, $params);
    }

    
    public function __destruct()
    {
        if ($this->curl) {
            curl_close($this->curl);
        }
    }
}
