<?php


namespace App\Cache;

/**
 * Class AbstractCacheAdapter
 * Expected that each cache adapter should inherit this abstraction
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Cache
 */
abstract class AbstractCacheAdapter implements \App\Cache\CacheInterface
{
    /**
     * AbstractCacheAdapter constructor.
     * @param null $configuration Adapter configuration if this type of adapter required it
     * @throws CacheAdapterException
     */
    public function __construct($configuration = null)
    {
        if (!is_null($configuration)){
            $this->configure($configuration);
        } elseif ($this->isConfigurationRequired()){
            throw new CacheAdapterException(CacheAdapterException::MESSAGE_ADAPTER_CONFIGURATION_REQUIRED);
        }
    }

    /**
     * If configuration for this type of adapter required return true
     * @return bool
     */
    abstract public function isConfigurationRequired();

    /**
     * Configure adapter
     * @param array|null $configuration
     * @return mixed
     */
    abstract public function configure($configuration = null);

    /**
     * @inheritDoc
     */
    abstract public function set(string $key, $value, int $duration);

    /**
     * @inheritDoc
     */
    abstract public function get(string $key);


    /**
     * Serialize Value and expiration time if it need.
     * Expected that this method called by method `set`, implemented by each inherit     *
     * @param mixed $value
     * @param int|null $expirationTime
     * @return string
     * @throws CacheAdapterException
     */
    protected function serializeValue($value, int $expirationTime=null)
    {
        if (!is_null($expirationTime))
        $value = [
            'expirationTime' => $expirationTime,
            'value' => $value
        ];
        try {
            return serialize($value);
        } catch (\Exception $e){
            throw new CacheAdapterException(CacheAdapterException::MESSAGE_SERIALIZATION_NOT_SUPPORTED);
        }
    }

    /**
     * Unserialize stored in cache data
     * Expected that this method called by method `get`
     * @param string $value
     * @return mixed
     */
    protected function unserializeValue(string $value)
    {
        return unserialize($value);
    }
}