<?php
namespace App\Printful;

/**
 * Class ApiClient
 * Incapsulate data exchange with API
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Printful
 */
class ApiClient
{
    /**
     * API base URL
     */
    const SERVICE_BASE_URL = 'https://api.printful.com';

    /**
     * @var \GuzzleHttp\HandlerStack|null
     */
    protected static $handlerStack=null;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * Mocking mechanism for tests
     * @param \GuzzleHttp\HandlerStack $handlerStack
     */
    public static function setGuzzleMock(\GuzzleHttp\HandlerStack $handlerStack)
    {
        self::$handlerStack = $handlerStack;
    }

    /**
     * ApiClient constructor.
     * @param string|null $apiKey
     */
    public function __construct(string $apiKey = null)
    {
        if (!is_null($apiKey)){
            $this->setApiKey($apiKey);
        }
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return bool
     */
    protected function hasApiKey()
    {
        return is_null($this->apiKey)? false : true;
    }

    /**
     * Make request to API method and return response.
     * @param ApiMethodInterface $apiMethodObject
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(ApiMethodInterface $apiMethodObject)
    {
        $url = static::SERVICE_BASE_URL . $apiMethodObject->requestPath();
        $method = $apiMethodObject->requestMethod();
        $options = $this->updateOptions($apiMethodObject->requestOptions());
        $client = $this->getHttpClient();
        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        $clientConfig = [];
        if (!is_null(self::$handlerStack)){
            $clientConfig['handler'] =self::$handlerStack;
            self::$handlerStack = null;
        }
        return new \GuzzleHttp\Client($clientConfig);
    }

    /**
     * @param array $options
     * @return array
     */
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

    /**
     * @return string
     */
    protected function getAuthorizationStr()
    {
        return 'Basic ' . base64_encode($this->apiKey);
    }





}