<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config\Loader;

/**
 * Abstract config loader
 *
 * @category    Aero
 * @package     Aero_Config
 * @subpackage  Aero_Config_Loader
 * @author      Alex Zavacki
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * @var array Loaders will be loaded before internal
     */
    protected $preLoaders = array();

    /**
     * @var array Loaders will be loaded before internal
     */
    protected $postLoaders = array();


    /**
     * @param  \Aero\Config\Loader\LoaderInterface $loader
     * @param  string $key
     *
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function addPreLoader(LoaderInterface $loader, $key = null)
    {
        if (is_string($key)) {
            $this->preLoaders[$key] = $loader;
        }
        else {
            $this->preLoaders[] = $loader;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPreLoaders()
    {
        return $this->preLoaders;
    }

    /**
     * @param  string $key
     * @param  mixed $default
     *
     * @return \Aero\Config\Loader\LoaderInterface
     */
    public function getPreLoader($key, $default = null)
    {
        return isset($this->preLoaders[$key]) ? $this->preLoaders[$key] : $default;
    }

    /**
     * @param  \Aero\Config\Loader\LoaderInterface $loader
     * @return bool
     */
    public function hasPreLoader(LoaderInterface $loader)
    {
        return in_array($loader, $this->preLoaders, true);
    }

    /**
     * @param  LoaderInterface|string $loaderOrKey
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function removePreLoader($loaderOrKey)
    {
        if ($loaderOrKey instanceof LoaderInterface) {
            if (($key = array_search($loaderOrKey, $this->preLoaders, true)) !== false) {
                unset($this->preLoaders[$key]);
            }
        }
        else {
            if (array_key_exists($loaderOrKey, $this->preLoaders)) {
                unset($this->preLoaders[$loaderOrKey]);
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function clearPreLoaders()
    {
        $this->preLoaders = array();
        return $this;
    }

    /**
     * @param  \Aero\Config\Loader\LoaderInterface $loader
     * @param  string $key
     *
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function addPostLoader(LoaderInterface $loader, $key = null)
    {
        if (is_string($key)) {
            $this->postLoaders[$key] = $loader;
        }
        else {
            $this->postLoaders[] = $loader;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPostLoaders()
    {
        return $this->postLoaders;
    }

    /**
     * @param  string $key
     * @param  mixed $default
     *
     * @return \Aero\Config\Loader\LoaderInterface
     */
    public function getPostLoader($key, $default = null)
    {
        return isset($this->postLoaders[$key]) ? $this->postLoaders[$key] : $default;
    }

    /**
     * @param  \Aero\Config\Loader\LoaderInterface $loader
     * @return bool
     */
    public function hasPostLoader(LoaderInterface $loader)
    {
        return in_array($loader, $this->postLoaders, true);
    }

    /**
     * @param  LoaderInterface|string $loaderOrKey
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function removePostLoader($loaderOrKey)
    {
        if ($loaderOrKey instanceof LoaderInterface) {
            if (($key = array_search($loaderOrKey, $this->postLoaders, true)) !== false) {
                unset($this->postLoaders[$key]);
            }
        }
        else {
            if (array_key_exists($loaderOrKey, $this->postLoaders)) {
                unset($this->postLoaders[$loaderOrKey]);
            }
        }

        return $this;
    }

    /**
     * @return \Aero\Config\Loader\AbstractLoader
     */
    public function clearPostLoaders()
    {
        $this->postLoaders = array();
        return $this;
    }
}
