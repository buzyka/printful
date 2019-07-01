<?php
namespace App\Printful;

/**
 * Interface ApiMethodInterface
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Printful
*/
interface ApiMethodInterface
{
    /**
     * ApiMethodInterface constructor.
     * @param string $apiKey
     * @param \App\Cache\CacheInterface $cacheInstance
     */
    public function __construct(string $apiKey, \App\Cache\CacheInterface $cacheInstance);

    /**
     * Return relative path to API method
     * @return string
     */
    public function requestPath(): string;

    /**
     * Return request method
     * @return string
     */
    public function requestMethod(): string;

    /**
     * Return Request options
     * @return array
     */
    public function requestOptions(): array;
}