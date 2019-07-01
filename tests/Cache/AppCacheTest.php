<?php
namespace LocalTest\Cache;

use App\Cache\Adapter\FileCacheAdapter;
use PHPUnit\Framework\TestCase;
use App\Cache\AppCache;
use App\Cache\{AppCacheException, CacheAdapterException};
use App\Cache\Adapter\DummyCacheAdapter;

class AppCacheTest extends TestCase
{
    public function testGetInstanceExcaption()
    {
        $this->expectException(AppCacheException::class);
        $this->expectExceptionMessage(AppCacheException::MESSAGE_ADAPTER_NOT_INITIALIZED);
        AppCache::getInstance();
    }

    public function testInitCacheIncorrectAdapter()
    {
        $this->expectExceptionMessage(AppCacheException::MESSAGE_ADAPTER_DOES_NOT_EXISTS);
        AppCache::init('someName');
    }

    public function testInitCacheDummy()
    {
        AppCache::init('Dummy');
        $this->assertInstanceOf(DummyCacheAdapter::class, AppCache::getInstance());
    }

    public function testInitCacheConfigurationRequiredException()
    {
        $this->expectExceptionMessage(CacheAdapterException::MESSAGE_ADAPTER_CONFIGURATION_REQUIRED);
        AppCache::init('File');
    }

    public function testInitCacheWithConfiguration()
    {
        $config = [
            'storage' => $this->getTestsPath() . '/tmp'
        ];
        AppCache::init('File', $config);
        $this->assertInstanceOf(FileCacheAdapter::class, AppCache::getInstance());
    }

    /**
     * Get path to the tests folder
     * @return string
     */
    private function getTestsPath()
    {
        $currentDir = dirname(__FILE__);
        while(basename($currentDir) != 'tests'){
            $currentDir = dirname($currentDir);
        }
        return $currentDir;
    }
}