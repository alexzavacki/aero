<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Loader;

/**
 * Autoloader interface
 *
 * @category    Aero
 * @package     Aero_Loader
 * @author      Alex Zavacki
 */
interface AutoloaderInterface
{
    /**
     * Autoload a class
     *
     * @param  string $class
     * @return string|bool
     */
    public function autoload($class);

    /**
     * Register the autoloader with spl_autoload
     *
     * @param  bool $prepend
     * @return void
     */
    public function register($prepend = false);

    /**
     * Configure the autoloader
     *
     * @param  array|\Traversable $options
     * @return \Aero\Loader\AutoloaderInterface
     */
    public function setOptions($options);
}
