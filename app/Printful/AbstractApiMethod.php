<?php


namespace App\Printful;


use App\Cache\CacheInterface;
use App\Printful\ApiClient;
use App\Printful\ApiRequestException;

abstract class AbstractApiMethod  implements ApiMethodInterface
{
    const SUCCESS_RESPONSE_CODE = 200;

    protected $lastResponse;

    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var CacheInterface
     */
    private $cacheInstance;

    /**
     * @inheritDoc
     */
    public function __construct(string $apiKey, CacheInterface $cacheInstance)
    {
        $this->apiKey = $apiKey;
        $this->cacheInstance = $cacheInstance;
    }

    /**
     * Generate request cache key
     * @return mixed
     */
    abstract protected function generateRequestCacheKey(): string;

    protected function runRequest()
    {
        if (is_null($response = $this->getResponseFromCache())){
            try {
                $response = $this->requestThroughApiClient();
            } catch (\Exception $apiClientException){
                throw new ApiRequestException($apiClientException->getMessage(), $apiClientException->getCode());
            }
        }
        if ($response->code == self::SUCCESS_RESPONSE_CODE){
            return $response->result;
        } else {
            throw new ApiRequestException($response->result, $response->code);
        }
    }

    protected function getResponseFromCache()
    {
        return $this->cacheInstance->get($this->generateRequestCacheKey());
    }

    protected function requestThroughApiClient()
    {
        $client = new ApiClient($this->apiKey);
        return $client->request($this);
    }


}