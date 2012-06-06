<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Di;

/**
 * Simple service locator
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Di
 * @author      Alex Zavacki
 */
class ServiceLocator
{
    /**
     * @var array
     */
    protected $services = array();


    /**
     * @param  string $id
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($id, $default = null)
    {
        if (!array_key_exists($id, $this->services)) {
            return $default;
        }

        if ($this->services[$id] instanceof \Closure
            || (!is_object($this->services[$id]) && is_callable($this->services[$id]))
        ) {
            return call_user_func($this->services[$id], $this);
        }

        return $this->services[$id];
    }

    /**
     * @param  string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->services);
    }

    /**
     * @param  string $id
     * @param  mixed  $value
     *
     * @return \Aero\Std\Di\ServiceLocator
     */
    public function set($id, $value)
    {
        $this->services[$id] = $value;
        return $this;
    }

    /**
     * @param  \Closure $service
     * @return \Closure
     */
    public function singleton(\Closure $service)
    {
        return function($locator) use ($service) {
            static $instance;
            return $instance === null ? ($instance = $service($locator)) : $instance;
        };
    }

    /**
     * @param  \Closure $service
     * @return \Closure
     */
    public function protect(\Closure $service)
    {
        return function($locator) use ($service) {
            return $service;
        };
    }

    /**
     * @throws \LogicException
     *
     * @param  string $id
     * @return mixed
     */
    public function raw($id)
    {
        if (!array_key_exists($id, $this->services)) {
            throw new \LogicException(sprintf('Service "%s" not set', $id));
        }
        return $this->services[$id];
    }
}
