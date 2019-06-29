<?php

namespace App\Cache\Adapter;

use App\Cache\CacheAdapterException;


class FileCacheAdapter extends \App\Cache\AbstractCacheAdapter
{
    const UNACCEPTABLE_SYMBOLS = ['\\', '/', ':', '*', '?', '"', '<', '>', '|', '+', '%', '!', '@'];

    protected $storagePath = '';

    /**
     * @inheritDoc
     */
    public function isConfigurationRequired()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function configure($configuration=null)
    {
        if ($this->isValidConfigFormat($configuration)){
            if ($this->isStoragePathReachable($configuration['storage'])){
                $this->storagePath = $configuration['storage'];
            } else {
                throw new CacheAdapterException(CacheAdapterException::MESSAGE_UNREACHABLE_STORAGE);
            }
        } else {
            throw new CacheAdapterException(CacheAdapterException::MESSAGE_INCORRECT_CONFIG_FORMAT);
        }
    }

    /**
     * Return false if configuration in invalid format
     * @param array $configuration
     * @return bool
     */
    private function isValidConfigFormat($configuration)
    {
        if (is_array($configuration) && is_string($configuration['storage'])){
            return true;
        }
        return false;
    }

    /**
     * Return false if it is not folder by path or it is not writable
     * @param string $path
     * @return bool
     */
    private function isStoragePathReachable($path)
    {
        if (is_dir($path) && is_readable($path) && is_writable($path)){
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $duration)
    {
        $filePath = $this->getFilePathByKey($key);
        $content = $this->serializeValue($value, $this->calcExpirationTime($duration));
        if ( file_put_contents($filePath, $content) === false ){
            throw new CacheAdapterException(CacheAdapterException::MESSAGE_WRITE_FAILURE);
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        $filePath = $this->getFilePathByKey($key);
        if (is_readable($filePath)){
            $data = $this->unserializeValue(file_get_contents($filePath));
            if (isset($data['expirationTime']) && $this->isNotExpired($data['expirationTime'])){
                if (isset($data['value'])){
                    return $data['value'];
                }
            }
        }
        return null;
    }

    private function isNotExpired(int $expirationTimestamp)
    {
        return ($expirationTimestamp >= time())?  true : false;
    }

    /**
     * Calculate expiration timestamp based on duration
     * @param int $duration Duration in seconds
     * @return int
     */
    private function calcExpirationTime(int $duration): int
    {
        return time()+$duration;
    }

    private function getFilePathByKey(string $key)
    {
       return $this->storagePath . DIRECTORY_SEPARATOR . $this->cleanFileName($key);
    }

    private function cleanFileName(string $fileName)
    {
        foreach (self::UNACCEPTABLE_SYMBOLS as $pos=>$symbol)
        {
            $fileName = str_replace($symbol, "$pos", $fileName);
        }
        return $fileName;
    }

}