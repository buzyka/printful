<?php


namespace App\Cache;

/**
 * CacheAdapterException represents an exception caused by incorrect use or configure of cache adapter.
 *
 * @author
 */
class CacheAdapterException extends \Exception
{
    const MESSAGE_INCORRECT_CONFIG_FORMAT = 'Incorrect configuration format';
    const MESSAGE_UNREACHABLE_STORAGE = 'Storage path does not reachable';
    const MESSAGE_WRITE_FAILURE = 'Storage write failure occurred';
    const MESSAGE_SERIALIZATION_NOT_SUPPORTED = 'Object does not support serialization.';
    const MESSAGE_ADAPTER_CONFIGURATION_REQUIRED = 'This type of adapter required aditional  configuration.';

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Cache Adapter Exception';
    }

}