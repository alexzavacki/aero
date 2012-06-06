<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Plugin;

/**
 * Standard plugin broker
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Plugin
 * @author      Alex Zavacki
 */
class PluginBroker
{
    /**
     * @var array
     */
    protected $plugins = array();

    /**
     * @var \Aero\Std\Plugin\PluginMap
     */
    protected $pluginMap;

    /**
     * @var string
     */
    protected $defaultPluginMapClass = 'Aero\\Std\\Plugin\\PluginMap';


    /**
     * Create and return plugin by name
     *
     * @throws \InvalidArgumentException|\RuntimeException
     *
     * @param  string $name
     * @param  array $options
     *
     * @return object
     */
    public function createPlugin($name, $options = array())
    {
        $plugin = $this->getPluginMap()->get($name);

        if (!$plugin) {
            throw new \RuntimeException('Unable to locate plugin associated with "' . $name . '"');
        }

        if ($plugin instanceof \Closure) {
            return $plugin;
        }
        elseif (is_object($plugin)) {
            return clone $plugin;
        }
        elseif (!is_string($plugin)) {
            throw new \InvalidArgumentException('Plugin must be closure, object or class');
        }

        if (empty($options)) {
            $instance = new $plugin();
        }
        elseif ($this->isAssocArray($options)) {
            $instance = new $plugin($options);
        }
        else {
            $r = new \ReflectionClass($plugin);
            $instance = $r->newInstanceArgs($options);
        }

        return $instance;
    }

    /**
     * Get plugin by name
     *
     * Plugin will be created with $options and registered on the first call
     *
     * @param  string $name
     * @param  array $options
     *
     * @return object
     */
    public function getPlugin($name, $options = array())
    {
        $pluginName = strtolower($name);

        if (isset($this->plugins[$pluginName])) {
            return $this->plugins[$pluginName];
        }

        $plugin = $this->createPlugin($name, $options);
        $this->register($pluginName, $plugin);

        return $plugin;
    }

    /**
     * Get plugin by name
     *
     * Alias of getPlugin()
     *
     * @param  string $name
     * @param  array $options
     *
     * @return object
     */
    public function get($name, $options = array())
    {
        return $this->getPlugin($name, $options);
    }

    /**
     * Get list of all registered (loaded) plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Register a plugin object by name
     *
     * @param  string $name
     * @param  mixed $plugin
     *
     * @return \Aero\Std\Plugin\PluginBroker
     */
    public function register($name, $plugin)
    {
        $name = strtolower($name);
        $this->plugins[$name] = $plugin;
        return $this;
    }

    /**
     * Unregister a named plugin
     *
     * Removes the plugin instance from the registry, if found.
     *
     * @param  string $name
     * @return \Aero\Std\Plugin\PluginBroker
     */
    public function unregister($name)
    {
        $name = strtolower($name);

        if (isset($this->plugins[$name])) {
            unset($this->plugins[$name]);
        }

        return $this;
    }

    /**
     * Check if plugin registered
     *
     * @param  string $name
     * @return bool
     */
    public function isRegistered($name)
    {
        return isset($this->plugins[strtolower($name)]);
    }

    /**
     * @param  \Aero\Std\Plugin\PluginMap $pluginMap
     * @return \Aero\Std\Plugin\PluginBroker
     */
    public function setPluginMap(PluginMap $pluginMap)
    {
        $this->pluginMap = $pluginMap;
        return $this;
    }

    /**
     * @return \Aero\Std\Plugin\PluginMap
     */
    public function getPluginMap()
    {
        if (!$this->pluginMap instanceof PluginMap) {
            $this->pluginMap = $this->createDefaultPluginMap();
        }
        return $this->pluginMap;
    }

    /**
     * @return \Aero\Std\Plugin\PluginMap
     */
    protected function createDefaultPluginMap()
    {
        $pluginMapClass = $this->defaultPluginMapClass;
        return new $pluginMapClass();
    }

    /**
     * Is a value an associative array?
     *
     * @param  mixed $value
     * @return bool
     */
    protected function isAssocArray($value)
    {
        if (!is_array($value)) {
            return false;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return false;
        }
        return true;
    }
}
