<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Plugin;

/**
 * Plugin map
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Plugin
 * @author      Alex Zavacki
 */
class PluginMap
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * @var array
     */
    protected static $staticMap = array();


    /**
     * Constructor.
     *
     * @param array $map
     */
    public function __construct($map = array())
    {
        $this->register(array_merge(
            (array) $this->getDefaultMap(),
            (array) static::$staticMap,
            (array) $map
        ));
    }

    /**
     * Get plugin info by name
     *
     * @param  string $name
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $name = strtolower($name);
        return isset($this->map[$name]) ? $this->map[$name] : $default;
    }

    /**
     * Get all registered plugin info
     *
     * @return array
     */
    public function all()
    {
        return $this->map;
    }

    /**
     * Register plugin(s)
     *
     * @throws \InvalidArgumentException

     * @param  string|array  $nameOrMap
     * @param  string|object $plugin
     *
     * @return \Aero\Std\Plugin\PluginMap
     */
    public function register($nameOrMap, $plugin = null)
    {
        if ($plugin !== null) {
            if (!is_string($nameOrMap)) {
                throw new \InvalidArgumentException('Plugin name must be a string');
            }
            $nameOrMap = array($nameOrMap => $plugin);
        }
        elseif (!is_array($nameOrMap) && !$nameOrMap instanceof \Traversable) {
            throw new \InvalidArgumentException('Expects an array or Traversable object');
        }

        if ($nameOrMap instanceof \Traversable) {
            foreach ($nameOrMap as $key => $value) {
                $this->map[strtolower($key)] = $value;
            }
        }
        else {
            $nameOrMap = array_change_key_case($nameOrMap, CASE_LOWER);
            $this->map = array_merge((array) $this->map, (array) $nameOrMap);
        }

        return $this;
    }

    /**
     * Unregister a named plugin
     *
     * @param  string $name
     * @return \Aero\Std\Plugin\PluginMap
     */
    public function unregister($name)
    {
        $name = strtolower($name);

        if (isset($this->map[$name])) {
            unset($this->map[$name]);
        }

        return $this;
    }

    /**
     * Check if plugin info registered by name
     *
     * @param  string $name
     * @return bool
     */
    public function isRegistered($name)
    {
        return isset($this->map[strtolower($name)]);
    }

    /**
     * Add a static map of plugins
     *
     * A null value will clear the static map.
     *
     * @throws \InvalidArgumentException
     *
     * @param array|\Traversable $map
     */
    public static function addStaticMap($map)
    {
        if (null === $map) {
            static::$staticMap = array();
            return;
        }

        if (!is_array($map) && !$map instanceof \Traversable) {
            throw new \InvalidArgumentException('Expects an array or Traversable object');
        }

        if ($map instanceof \Traversable) {
            foreach ($map as $key => $value) {
                static::$staticMap[$key] = $value;
            }
        }
        else {
            static::$staticMap = array_merge((array) static::$staticMap, $map);
        }
    }

    /**
     * Get array of default plugins
     *
     * @return array
     */
    public function getDefaultMap()
    {
        return array();
    }
}
