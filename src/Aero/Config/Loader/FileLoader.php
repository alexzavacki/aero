<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader;

/**
 * Filesystem loader
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @author      Alex Zavacki
 */
class FileLoader extends AbstractLoader
{
    /**
     * @var \Aero\Config\Loader\Cache\CacheInterface
     */
    protected $cache;

    /**
     * @var \Aero\Config\Loader\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Aero\Config\Loader\Merger\MergerInterface
     */
    protected $merger;


    /**
     * Load config(s)
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $resources
     * @return array
     */
    public function load($resources)
    {
        $result = array();

        if (is_string($resources)) {
            $resources = array($resources);
        }
        elseif (!is_array($resources) && (!$resources instanceof \Traversable)) {
            throw new \InvalidArgumentException('Config resource must be a string or an array');
        }

        foreach ($resources as $resource)
        {
            $resource = $this->getFormattedResource($resource);

            if (!is_string($resource)) {
                continue;
            }

            if ($this->preLoaders) {
                foreach ($this->preLoaders as $loader) {
                    /** @var $loader \Aero\Config\Loader\LoaderInterface */
                    $config = $loader->load($resource);
                    $result = ($this->merger instanceof Merger\MergerInterface)
                        ? $this->merger->merge($result, $config)
                        : array_replace_recursive($result, $config);
                }
            }

            $config = null;

            if ($this->cache) {
                $config = $this->cache->load($resource);
            }

            if (!is_array($config))
            {
                $config = $this->doLoad($resource);

                if (!$config || !is_array($config)) {
                    $config = array();
                }
                elseif ($this->cache) {
                    $this->cache->set($resource, $config);
                }
            }

            $result = array_replace_recursive($result, $config);

            if ($this->postLoaders) {
                foreach ($this->postLoaders as $loader) {
                    /** @var $loader \Aero\Config\Loader\LoaderInterface */
                    $config = $loader->load($resource);
                    $result = ($this->merger instanceof Merger\MergerInterface)
                        ? $this->merger->merge($result, $config)
                        : array_replace_recursive($result, $config);
                }
            }
        }

        return $result;
    }

    /**
     * Internal loading implementation
     *
     * @param  string $resource
     * @return array
     */
    protected function doLoad($resource)
    {
        if (is_readable($resource)) {
            return include $resource;
        }
        return array();
    }

    /**
     * @param  string $resource
     * @return string
     */
    public function getFormattedResource($resource)
    {
        return $resource;
    }

    /**
     * @param  \Aero\Config\Loader\Cache\CacheInterface $cache
     * @param  bool $cascade
     * @return \Aero\Config\Loader\FileLoader
     */
    public function setCache(Cache\CacheInterface $cache, $cascade = true)
    {
        $this->cache = $cache;

        if ($cascade) {
            foreach ($this->preLoaders as $loader) {
                if ($loader instanceof self) {
                    /** @var $loader \Aero\Config\Loader\FileLoader */
                    $loader->setCache($cache, $cascade);
                }
            }
            foreach ($this->postLoaders as $loader) {
                if ($loader instanceof self) {
                    /** @var $loader \Aero\Config\Loader\FileLoader */
                    $loader->setCache($cache, $cascade);
                }
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Config\Loader\Cache\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param  \Aero\Config\Loader\Locator\LocatorInterface $locator
     * @param  bool $cascade
     * @return \Aero\Config\Loader\FileLoader
     */
    public function setLocator($locator, $cascade = false)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * @return \Aero\Config\Loader\Locator\LocatorInterface
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @param  \Aero\Config\Loader\Merger\MergerInterface $merger
     * @param  bool $cascade
     * @return \Aero\Config\Loader\FileLoader
     */
    public function setMerger(Merger\MergerInterface $merger, $cascade = true)
    {
        $this->merger = $merger;

        if ($cascade) {
            foreach ($this->preLoaders as $loader) {
                if ($loader instanceof self) {
                    /** @var $loader \Aero\Config\Loader\FileLoader */
                    $loader->setMerger($merger, $cascade);
                }
            }
            foreach ($this->postLoaders as $loader) {
                if ($loader instanceof self) {
                    /** @var $loader \Aero\Config\Loader\FileLoader */
                    $loader->setMerger($merger, $cascade);
                }
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Config\Loader\Merger\MergerInterface
     */
    public function getMerger()
    {
        return $this->merger;
    }
}
