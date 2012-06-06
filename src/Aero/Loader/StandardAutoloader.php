<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Loader;

if (!interface_exists(__NAMESPACE__ . '\\AutoloaderInterface')) {
    require_once __DIR__ . '/AutoloaderInterface.php';
}

/**
 * PSR-0 compliant autoloader
 *
 * @category    Aero
 * @package     Aero_Loader
 * @author      Alex Zavacki
 */
class StandardAutoloader implements AutoloaderInterface
{
    const NAMESPACE_SEPARATOR = '\\';
    const PREFIX_SEPARATOR    = '_';

    /**
     * @var array Namespace/directory pairs to search
     */
    protected $namespaces = array();

    /**
     * @var array Prefix/directory pairs to search
     */
    protected $prefixes = array();

    /**
     * @var array Fallback directories
     */
    protected $fallbacks = array();

    /**
     * @var bool Searching the include for class files
     */
    protected $useIncludePath = false;


    /**
     * Constructor.
     *
     * @param array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Configure the autoloader
     *
     * @throws \InvalidArgumentException
     *
     * @param  array|\Traversable $options
     * @return \Aero\Loader\StandardAutoloader
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !($options instanceof \Traversable)) {
            throw new \InvalidArgumentException('Options must be an array or Traversable');
        }

        if (isset($options['namespaces'])) {
            $this->registerNamespace($options['namespaces']);
        }
        if (isset($options['prefixes'])) {
            $this->registerPrefix($options['prefixes']);
        }
        if (isset($options['fallback'])) {
            $this->registerFallbackDirectory($options['fallback']);
        }

        return $this;
    }

    /**
     * Register namespace/directory pair(s)
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $namespaces
     * @param  string|array $directory
     * @return \Aero\Loader\StandardAutoloader
     */
    public function registerNamespace($namespaces, $directory = null)
    {
        if (is_string($namespaces) && $directory !== null) {
            $namespaces = array($namespaces => $directory);
        }
        elseif (!is_array($namespaces) && !$namespaces instanceof \Traversable) {
            throw new \InvalidArgumentException('Namespace must be a string or an array of namespace/directory pairs');
        }

        foreach ($namespaces as $namespace => $directory)
        {
            $namespace = rtrim($namespace, self::NAMESPACE_SEPARATOR) . self::NAMESPACE_SEPARATOR;

            if (!isset($this->namespaces[$namespace])) {
                $this->namespaces[$namespace] = array();
            }

            foreach ((array) $directory as $dir) {
                $this->namespaces[$namespace][] = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
            }
        }

        return $this;
    }

    /**
     * Get all registered namespaces
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Register a prefix/directory pair
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $prefixes
     * @param  string|array $directory
     * @return \Aero\Loader\StandardAutoloader
     */
    public function registerPrefix($prefixes, $directory = null)
    {
        if (is_string($prefixes) && $directory !== null) {
            $prefixes = array($prefixes => $directory);
        }
        elseif (!is_array($prefixes) && !$prefixes instanceof \Traversable) {
            throw new \InvalidArgumentException('Prefix must be a string or an array of prefix/directory pairs');
        }

        foreach ($prefixes as $prefix => $directory)
        {
            if (!isset($this->prefixes[$prefix])) {
                $this->prefixes[$prefix] = array();
            }

            foreach ((array) $directory as $dir) {
                $this->prefixes[$prefix][] = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
            }
        }

        return $this;
    }

    /**
     * Get all registered prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Add the directory to use as a fallback
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $dirs
     * @return \Aero\Loader\StandardAutoloader
     */
    public function registerFallbackDirectory($dirs)
    {
        if (is_string($dirs)) {
            $dirs = array($dirs);
        }
        elseif (!is_array($dirs) && !$dirs instanceof \Traversable) {
            throw new \InvalidArgumentException('Prefix must be a string or an array of prefix/directory pairs');
        }

        $dirs = array_filter(array_map('realpath', (array) $dirs));

        $this->fallbacks = array_unique(array_merge($this->fallbacks, $dirs));
        return $this;
    }

    /**
     * Get the directory(ies) to use as a fallback
     *
     * @return array
     */
    public function getFallbackDirectories()
    {
        return $this->fallbacks;
    }

    /**
     * Turns on searching the include for class files
     *
     * @param boolean $useIncludePath
     * @return \Aero\Loader\StandardAutoloader|bool
     */
    public function useIncludePath($useIncludePath = null)
    {
        if (is_bool($useIncludePath)) {
            $this->useIncludePath = $useIncludePath;
            return $this;
        }
        return $this->useIncludePath;
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
     * @return string|bool
     */
    public function autoload($class)
    {
        $class = ltrim($class, self::NAMESPACE_SEPARATOR);
        $type  = strpos($class, self::NAMESPACE_SEPARATOR) !== false ? 'namespaces' : 'prefixes';

        $classFilename = $this->classNameToFilename($class);

        if (isset($this->$type)) {
            // Namespace and/or prefix autoloading
            foreach ($this->$type as $leader => $dirs)
            {
                if (strpos($class, $leader) !== 0) {
                    continue;
                }
                foreach ($dirs as $dir) {
                    $file = $dir . DIRECTORY_SEPARATOR . $classFilename;
                    if (is_file($file)) {
                        include $file;
                        return $class;
                    }
                }
            }
        }

        if ($this->fallbacks) {
            foreach ($this->fallbacks as $dir) {
                $file = $dir . DIRECTORY_SEPARATOR . $classFilename;
                if (is_file($file)) {
                    include $file;
                    return $class;
                }
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classFilename)) {
            include $file;
            return $class;
        }

        return false;
    }

    /**
     * Transform the class name to a filename
     *
     * @param  string $class
     * @param  string $directory
     * @return string
     */
    protected function classNameToFilename($class, $directory = '')
    {
        // $class may contain a namespace portion, in which case we need
        // to preserve any underscores in that portion.
        $namespace  = '';
        $pos = strrpos($class, self::NAMESPACE_SEPARATOR);
        if ($pos) {
            $namespace = substr($class, 0, $pos);
            $class = substr($class, $pos);
        }
        return $directory
            . str_replace(
                self::NAMESPACE_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $namespace
            )
            . str_replace(
                array(self::NAMESPACE_SEPARATOR, self::PREFIX_SEPARATOR),
                DIRECTORY_SEPARATOR,
                $class
            )
            . '.php';
    }
}
