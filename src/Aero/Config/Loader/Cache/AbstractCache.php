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
abstract class AbstractCache implements CacheInterface
{
    /**
     * @var array Cache data
     */
    protected $data;

    /**
     * @var bool Is pack modified?
     */
    protected $modified = false;

    /**
     * @var bool
     */
    protected $writeOnDestruct = true;


    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->writeOnDestruct) {
            $this->write();
        }
    }

    /**
     * @param  mixed $resource
     * @return array|null
     */
    public function load($resource)
    {
        if ($this->data === null) {
            $this->read();
        }

        $resource = $this->getFormattedResource($resource);

        if (!isset($this->data[$resource]) || !is_array($this->data[$resource])) {
            return null;
        }

        /*if (!file_exists($resource)) {
            return null;
        }*/

        $data = $this->data[$resource];
        $time = isset($data['time']) ? (int) $data['time'] : 0;

        if (filemtime($resource) > $time) {
            return null;
        }

        return isset($data['data']) ? $data['data'] : null;
    }

    /**
     * @param  mixed $resource
     * @return mixed
     */
    public function getFormattedResource($resource)
    {
        return $resource;
    }

    /**
     * @return array
     */
    public function read()
    {
        if ($this->data === null) {
            $this->data = is_string($data = $this->readCache()) ? unserialize($data) : array();
        }
        return $this->data;
    }

    /**
     * @param  mixed $resource
     * @param  mixed $data
     * @param  array $params
     * @return \Aero\Config\Loader\Cache\AbstractCache
     */
    public function set($resource, $data, $params = array())
    {
        $resource = $this->getFormattedResource($resource);

        $this->data[$resource] = array_merge(
            array(
                'data' => $data,
                'time' => time(),
            ),
            (array) $params
        );

        $this->modified = true;

        return $this;
    }

    /**
     * @param  bool $force
     * @return \Aero\Config\Loader\Cache\AbstractCache
     */
    public function write($force = false)
    {
        if ($this->modified || $force) {
            $this->writeCache(serialize($this->data));
            $this->modified = false;
        }
        return $this;
    }

    /**
     * @abstract
     * @return string
     */
    abstract protected function readCache();

    /**
     * @abstract
     * @param  string $serialized
     * @return void
     */
    abstract public function writeCache($serialized);
}
