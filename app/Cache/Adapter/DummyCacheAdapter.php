<?php

namespace App\Cache\Adapter;


class DummyCacheAdapter extends \App\Cache\AbstractCacheAdapter
{
    public function isConfigurationRequired()
    {
        return false;
    }

    public function set(string $key, $value, int $duration)
    {
        return $value;
    }

    public function get(string $key)
    {
        return null;
    }

    public function configure($configuration=null)
    {
    }
}