<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader\Cache;

/**
 * Config loader from cache
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @subpackage  Aero_Config_Loader_Cache
 * @author      Alex Zavacki
 */
class FileCache extends AbstractCache
{
    // Loads cache data, checks that cache config resource is fresh, writes new cache data if changed

    /**
     * @var string Cache pack filename
     */
    protected $cacheFilename;


    /**
     * Constructor.
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->cacheFilename = (string) $filename;
    }

    /**
     * @return string
     */
    protected function readCache()
    {
        return is_readable($this->cacheFilename) ? file_get_contents($this->cacheFilename) : null;
    }

    /**
     * @throws \RuntimeException
     *
     * @param  string $serialized
     * @return void
     */
    public function writeCache($serialized)
    {
        $dir = dirname($this->cacheFilename);

        if (!is_dir($dir)) {
            if (@mkdir($dir, 0777, true) === false) {
                throw new \RuntimeException(sprintf('Unable to create the %s directory', $dir));
            }
        }
        elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Unable to write in the %s directory', $dir));
        }

        $tmpFile = tempnam($dir, basename($this->cacheFilename));

        if (false !== @file_put_contents($tmpFile, $serialized)
            && @rename($tmpFile, $this->cacheFilename)
        ) {
            chmod($this->cacheFilename, 0666);
        }
        else {
            throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $this->cacheFilename));
        }
    }

    /**
     * @param  string $resource
     * @return string
     */
    public function getFormattedResource($resource)
    {
        return str_replace('\\', '/', $resource);
    }

    /**
     * @param  string $cacheFilename
     * @return \Aero\Config\Loader\Cache\FileCache
     */
    public function setCacheFilename($cacheFilename)
    {
        $this->cacheFilename = (string) $cacheFilename;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheFilename()
    {
        return $this->cacheFilename;
    }
}
