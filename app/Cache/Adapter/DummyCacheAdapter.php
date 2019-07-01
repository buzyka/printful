<?php
namespace App\Cache\Adapter;

/**
 * Class DummyCacheAdapter
 * This calass is example of additional cache adapter and used in tests without saving overheads
 * @author Viktor Buzyka <vbuzyka@gmail.com>
 * @package App\Cache\Adapter
 */
class DummyCacheAdapter extends \App\Cache\AbstractCacheAdapter
{
    /**
     * @inheritDoc
     */
    public function isConfigurationRequired()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $duration)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function configure($configuration=null)
    {
    }
}