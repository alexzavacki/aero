<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Loader;

/**
 * A class loader that uses a mapping file to look up paths
 *
 * @category    Aero
 * @package     Aero_Loader
 * @author      Alex Zavacki
 */
class ClassMapAutoloader
{
    /**
     * @var array Class name/filename map
     */
    protected $map = array();

    /**
     * @var array already loaded files with maps
     */
    protected $loadedMaps = array();


    /**
     * Constructor.
     *
     * @param array $map A map where keys are classes and values the absolute file path
     */
    public function __construct($map = null)
    {
        if ($map !== null) {
            $this->registerAutoloadMap($map);
        }
    }

    /**
     * Register an autoload map
     *
     * An autoload map may be either an associative array, or a file returning
     * an associative array.
     *
     * An autoload map should be an associative array containing
     * classname/file pairs.
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $map
     * @return \Aero\Loader\ClassMapAutoloader
     */
    public function registerAutoloadMap($map)
    {
        if (is_string($map)) {
            $location = $map;
            if (($map = $this->loadMapFromFile($location)) === $this) {
                return $this;
            }
        }

        if (!is_array($map)) {
            throw new \InvalidArgumentException('Map of classes must be an array');
        }

        $this->map = array_merge($this->map, $map);

        if (isset($location)) {
            $this->loadedMaps[] = $location;
        }

        return $this;
    }

    /**
     * Load a map from a file
     *
     * @throws \InvalidArgumentException for nonexistent locations
     *
     * @param  string $filename
     * @return \Aero\Loader\ClassMapAutoloader|mixed
     */
    protected function loadMapFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('Map file provided does not exist');
        }

        $filename = realpath($filename);

        if (in_array($filename, $this->loadedMaps)) {
            return $this;
        }

        return include $filename;
    }

    /**
     * Register the autoloader with spl_autoload
     *
     * @param  bool $prepend
     * @return void
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'autoload'), true, $prepend);
    }

    /**
     * Autoload a class
     *
     * @param  string $class
     * @return mixed
     */
    public function autoload($class)
    {
        if (isset($this->map[$class])) {
            require_once $this->map[$class];
        }
    }
}
