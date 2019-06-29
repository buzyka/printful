<?php


namespace App\Cache;

/**
 * CacheAdapterException represents an exception caused by incorrect use or configure of cache adapter.
 *
 * @author
 */
class AppCacheException extends \Exception
{
    const MESSAGE_ADAPTER_NOT_INITIALIZED = 'Cache adapter not initialized and not configured.';
    const MESSAGE_ADAPTER_DOES_NOT_EXISTS = 'Adapter with this name does not exists.';
}