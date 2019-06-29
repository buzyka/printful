<?php
namespace LocalTest\Cache\Adapter;

use PHPUnit\Framework\TestCase;
use App\Cache\Adapter\FileCacheAdapter;
use App\Cache\CacheAdapterException;


class FileCacheAdapterTest extends TestCase
{
    protected static $correctConfigurationFixture;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        self::$correctConfigurationFixture = [
            'storage' => self::getTestsPath() . '/tmp'
        ];
    }

    /**
     * Get path to the tests folder
     * @return string
     */
    private static function getTestsPath()
    {
        $currentDir = dirname(__FILE__);
        while(basename($currentDir) != 'tests'){
            $currentDir = dirname($currentDir);
        }
        return $currentDir;
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        $tmpPath = self::$correctConfigurationFixture['storage'];
        foreach (glob($tmpPath . '/*') as $file) {
            unlink($file);
        }
    }



    public function testIncorrectConfigurationFormat()
    {
        $incorrectConfiguration = 'not array';
        $this->expectException(CacheAdapterException::class);
        $this->expectExceptionMessage(CacheAdapterException::MESSAGE_INCORRECT_CONFIG_FORMAT);
        new FileCacheAdapter($incorrectConfiguration);
    }

    public function testIncorrectConfigurationUnreachableStorage()
    {
        $incorrectConfiguration = self::$correctConfigurationFixture;
        $incorrectConfiguration['storage'] .= '/nonexistent_folder';
        $this->assertFalse(is_dir($incorrectConfiguration['storage']));
        $this->expectException(CacheAdapterException::class);
        $this->expectExceptionMessage(CacheAdapterException::MESSAGE_UNREACHABLE_STORAGE);
        new FileCacheAdapter($incorrectConfiguration);
    }

    public function testIncorrectConfigurationStorageIsFile()
    {
        $incorrectConfiguration = self::$correctConfigurationFixture;
        $incorrectConfiguration['storage'] .= '/file.txt';
        file_put_contents($incorrectConfiguration['storage'], '');
        $this->expectException(CacheAdapterException::class);
        $this->expectExceptionMessage(CacheAdapterException::MESSAGE_UNREACHABLE_STORAGE);
        new FileCacheAdapter($incorrectConfiguration);
    }

    public function testSetSuccess(): string
    {
        $cacheObj = $this->getCacheObject();
        $key = 'test_value';
        $value = 'test string';
        $duration = 10;
        $cacheObj->set($key, $value, $duration);
        $path = self::$correctConfigurationFixture['storage'] . '/' .$key;
        $this->assertFileExists($path);
        return file_get_contents($path);
    }

    /**
     * @depends testSetSuccess
     * @param string $data
     * @return array unserialized array
     */
    public function testSetSuccessResponseFormat(string $data): array
    {
        $data = unserialize($data);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('expirationTime', $data);
        $this->assertArrayHasKey('value', $data);
        return $data;
    }

    /**
     * @depends testSetSuccessResponseFormat
     * @param array $data
     */
    public function testSetSuccessExpirationTime(array $data)
    {
        $duration = 10;
        $this->assertEquals(time()+$duration, $data['expirationTime']);
    }

    /**
     * @return \App\Cache\Adapter\FileCacheAdapter
     */
    private function getCacheObject()
    {
        $cacheObj = new FileCacheAdapter(self::$correctConfigurationFixture);
        return $cacheObj;
    }

    public function testSetIncorrectSymbolInKey()
    {
        $cacheObj = $this->getCacheObject();
        $key = 't\st_v:lue';
        $filenameExpected = 't0st_v2lue';
        $value = 'test string';
        $duration = 10;
        $cacheObj->set($key, $value, $duration);
        $path = self::$correctConfigurationFixture['storage'] . '/' .$filenameExpected;
        $this->assertFileExists($path);
    }

    public function testGetString()
    {
        $this->assertIsString(
            $this->setAndThenGetValue('test string')
        );
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function setAndThenGetValue($value)
    {
        $cacheObj = $this->getCacheObject();
        $key = 'get test';
        $duration = 10;
        $cacheObj->set($key, $value, $duration);
        $this->assertEquals($value, $cacheObj->get($key));
        return $value;
    }

    public function testGetInteger()
    {
        $this->assertIsInt(
            $this->setAndThenGetValue(100)
        );
    }

    public function testGetFloat()
    {

        $this->assertIsFloat(
            $this->setAndThenGetValue(5.5)
        );
    }

    public function testGetArray()
    {

        $this->assertIsArray(
            $this->setAndThenGetValue(
                [
                    'key1' => 'res',
                    'key2' => 10,
                    [ 1, 2, 3 ]
                ]
            )
        );
    }

    public function testGetObject()
    {

        $this->assertIsObject(
            $this->setAndThenGetValue(
                (object)[
                    'key1' => 'res',
                    'key2' => 10,
                    [ 1, 2, 3 ]
                ]
            )
        );
    }

    public function testGetObjectException()
    {
        $this->expectException(CacheAdapterException::class);
        $this->expectExceptionMessage(CacheAdapterException::MESSAGE_SERIALIZATION_NOT_SUPPORTED);
        $cacheObj = $this->getCacheObject();
        $key = 'get test';
        $duration = 10;
        $value = new class {
            public function test($param){
                echo $param;
            }
        };
        $cacheObj->set($key, $value, $duration);
    }
}