<?php


namespace App\Cache;


class AppCache
{
    const ADAPTER_CLASS_TEMPLATE = '\Adapter\{}CacheAdapter';

    protected static $cacheInstance;

    /**
     * @param string $adapter
     * @param null $configuration
     * @throws AppCacheException
     */
    public static function init(string $adapter, $configuration = null)
    {
        $className = str_replace('{}', $adapter, __NAMESPACE__ . self::ADAPTER_CLASS_TEMPLATE);
        if (class_exists($className)){
            try{
                static::$cacheInstance = new $className($configuration);
            }catch (CacheAdapterException $adapterException){
                throw new AppCacheException($adapterException->getMessage());
            }
        } else {
            throw new AppCacheException(AppCacheException::MESSAGE_ADAPTER_DOES_NOT_EXISTS);
        }
    }

    /**
     * Get Cache object instance
     * @return \App\Cache\AbstractCacheAdapter
     * @throws AppCacheException
     */
    public final static function getInstance()
    {
        if (is_null(static::$cacheInstance)){
            throw new AppCacheException(AppCacheException::MESSAGE_ADAPTER_NOT_INITIALIZED);
        }
        return static::$cacheInstance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }
}