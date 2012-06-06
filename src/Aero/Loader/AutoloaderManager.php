<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Loader;

/**
 * Autoloader manager (factory)
 *
 * @category    Aero
 * @package     Aero_Loader
 * @author      Alex Zavacki
 */
abstract class AutoloaderManager
{
    /**
     * @var array Registered autoloaders through this factory
     */
    protected static $loaders = array();


    /**
     * Factory for autoloaders
     *
     * If autoloader already registered,
     * options will be passed through setOptions() method
     *
     * @throws \InvalidArgumentException
     *
     * @param array|\Traversable $autoloaders
     * @return void
     */
    public static function factory($autoloaders)
    {
        if (!is_array($autoloaders) && !($autoloaders instanceof \Traversable)) {
            throw new \InvalidArgumentException('Autoloaders must be an array or Traversable');
        }

        foreach ($autoloaders as $id => $options)
        {
            $id = ltrim($id, '\\');

            if (isset(static::$loaders[$id])) {
                static::$loaders[$id]->setOptions($options);
            }
            else {
                static::register($id, $options);
            }
        }
    }

    /**
     * Create autoloader and register it
     *
     * If $id is null, $class will be used instead
     * If $id is bool, it will be used instead of $replace
     *
     * @param  string $class
     * @param  array $options
     * @param  string $id
     * @param  bool $replace
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public static function register($class, $options = array(), $id = null, $replace = true)
    {
        $class = ltrim($class, '\\');

        if (is_bool($id)) {
            $replace = $id;
            $id = null;
        }

        if ($id === null) {
            $id = $class;
        }

        if (isset(static::$loaders[$id]))
        {
            if (!$replace) {
                throw new \InvalidArgumentException("Autoloader with id '$id' already registered");
            }

            static::unregisterAutoloader($id);
        }

        if ($class === __NAMESPACE__ . '\\StandardAutoloader') {
            $autoloader = static::createStandardAutoloader();
        }
        else {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(
                    sprintf('Autoloader class "%s" not loaded', $class)
                );
            }
            $autoloader = new $class();
        }

        $autoloader->setOptions($options);
        $autoloader->register();

        return static::$loaders[$id] = $autoloader;
    }

    /**
     * Place autoloader object in loader list
     *
     * @param  \Aero\Loader\AutoloaderInterface $autoloader
     * @param  string $id
     * @param  bool $replace
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function set($autoloader, $id = null, $replace = true)
    {
        if (!$autoloader instanceof AutoloaderInterface) {
            throw new \InvalidArgumentException('Autoloader must implement AutoloaderInterface');
        }

        if (is_bool($id)) {
            $replace = $id;
            $id = null;
        }

        if ($id === null) {
            $id = get_class($autoloader);
        }

        $id = ltrim($id, '\\');

        if (isset(static::$loaders[$id]))
        {
            if (!$replace) {
                throw new \InvalidArgumentException("Autoloader with id '$id' already registered");
            }

            static::unregisterAutoloader($id);
        }

        static::$loaders[$id] = $autoloader;
    }

    /**
     * Get an list of all autoloaders registered with the factory
     *
     * @return array
     */
    public static function getRegisteredAutoloaders()
    {
        return static::$loaders;
    }

    /**
     * Retrieves an autoloader by id
     *
     * @param  string $id
     * @return \Aero\Loader\AutoloaderInterface
     *
     * @throws \InvalidArgumentException
     */
    public static function getRegisteredAutoloader($id)
    {
        if (!isset(static::$loaders[$id])) {
            throw new \InvalidArgumentException(sprintf('Autoloader class "%s" not registered', $id));
        }
        return static::$loaders[$id];
    }

    /**
     * Unregisters all autoloaders that have been registered via the factory.
     * This will NOT unregister autoloaders registered outside of the fctory.
     *
     * @return void
     */
    public static function unregisterAutoloaders()
    {
        foreach (static::getRegisteredAutoloaders() as $class => $autoloader) {
            spl_autoload_unregister(array($autoloader, 'autoload'));
            unset(static::$loaders[$class]);
        }
    }

    /**
     * Unregister a single autoloader by id
     *
     * @param  string $id
     * @return bool
     */
    public static function unregisterAutoloader($id)
    {
        if (!isset(static::$loaders[$id])) {
            return false;
        }

        $autoloader = static::$loaders[$id];
        spl_autoload_unregister(array($autoloader, 'autoload'));
        unset(static::$loaders[$id]);

        return true;
    }

    /**
     * Get an instance of the standard autoloader
     *
     * @return \Aero\Loader\StandardAutoloader
     */
    public static function createStandardAutoloader()
    {
        if (!class_exists(__NAMESPACE__ . '\\StandardAutoloader')) {
            require_once __DIR__ . '/StandardAutoloader.php';
        }
        return new StandardAutoloader();
    }
}
