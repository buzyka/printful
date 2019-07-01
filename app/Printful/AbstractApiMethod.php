<?php
namespace App\Printful;

use App\Cache\CacheInterface;
use App\Cache\CacheAdapterException;
use App\Printful\ApiClient;
use App\Printful\ApiRequestException;

/**
 * Class AbstractApiMethod
 * Expected that each API method class inherit this abstraction
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Printful
 */
abstract class AbstractApiMethod  implements ApiMethodInterface
{
    const SUCCESS_RESPONSE_CODE = 200;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var CacheInterface
     */
    private $cacheInstance;

    /**
     * Cache duration in seconds
     * @var int
     */
    private $cacheDuration;

    /**
     * @inheritDoc
     */
    public function __construct(string $apiKey, CacheInterface $cacheInstance)
    {
        $this->apiKey = $apiKey;
        $this->cacheInstance = $cacheInstance;
        $this->cacheDuration = 5*60; //default cache duration is 5 minutes
    }

    /**
     * Generate request cache key
     * @return mixed
     */
    abstract protected function generateRequestCacheKey(): string;

    /**
     * Set Cache Duration
     * @param int $duration
     */
    public function setCacheDuration(int $duration)
    {
        $this->cacheDuration = $duration;
    }

    /**
     * Initiate request to Api and first step response processing (incapsulate caching)
     * @return mixed
     * @throws \App\Printful\ApiRequestException
     */
    protected function runRequest()
    {
        if (is_null($response = $this->getResponseFromCache())){
            try {
                $response = $this->requestThroughApiClient();
                $this->putResponseToCache($response);
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

    /**
     * Check response for the request in cache
     * @return mixed|null
     */
    protected function getResponseFromCache()
    {
        return $this->cacheInstance->get($this->generateRequestCacheKey());
    }

    /**
     * Put response for the request in cache
     * @return mixed|null
     */
    protected function putResponseToCache($responseData)
    {
        try{
            $this->cacheInstance->set($this->generateRequestCacheKey(), $responseData, $this->cacheDuration);
        } catch (CacheAdapterException $adapterException){
            // @todo it is not critical, but we couldn't put data to cache

        }
    }

    /**
     * Make API Request
     * @return mixed
     */
    protected function requestThroughApiClient()
    {
        $client = new ApiClient($this->apiKey);
        return $client->request($this);
    }
}