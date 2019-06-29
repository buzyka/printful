<?php


namespace App\Printful;

class ApiClient
{
    const SERVICE_BASE_URL = 'https://api.printful.com';

    protected $apiKey;

    public function __construct(string $apiKey = null)
    {
        if (!is_null($apiKey)){
            $this->setApiKey($apiKey);
        }
    }

    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    protected function hasApiKey()
    {
        return is_null($this->apiKey)? false : true;
    }

    public function request(ApiMethodInterface $apiMethodObject)
    {
        $url = static::SERVICE_BASE_URL . $apiMethodObject->requestPath();
        $method = $apiMethodObject->requestMethod();
        $options = $this->updateOptions($apiMethodObject->requestOptions());

        $client = new \GuzzleHttp\Client();
        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents());
    }

    protected function updateOptions(array $options)
    {
        if (!isset($options['headers'])){
            $options['headers'] = [];
        }
        if (!isset($options['headers']['Content-Type'])){
            $options['headers']['Content-Type'] = 'application/json';
        }
        if ($this->hasApiKey()){
            $options['headers']['Authorization'] = $this->getAuthorizationStr();
        }
        return $options;
    }

    protected function getAuthorizationStr()
    {
        return 'Basic ' . base64_encode($this->apiKey);
    }





}