<?php
namespace App\Cache;

/**
 * Class AppCache
 * Singleton for getting cache adapter
 *
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Cache
 */
class AppCache
{
    const ADAPTER_CLASS_TEMPLATE = '\Adapter\{}CacheAdapter';

    protected static $cacheInstance;

    /**
     * Init configure and return cache adapter that will use in application.
     * @param string $adapter
     * @param null $configuration
     * @return \App\Cache\CacheInterface
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
        return static::getInstance();
    }

    /**
     * Get Cache adapter instance
     * @return \App\Cache\CacheInterface
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