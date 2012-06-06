<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Routing;

/**
 * Route collection
 *
 * @category    Aero
 * @package     Aero_Routing
 * @author      Alex Zavacki
 */
class RouteCollection implements \IteratorAggregate
{
    /**
     * @var array List of registered routes
     */
    protected $routes = array();

    /**
     * @var string
     */
    protected $prefix;


    /**
     * Add routes
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array|\Traversable $name
     * @param  \Aero\Routing\Route|array $route
     * @return \Aero\Routing\RouteCollection
     */
    public function add($name, $route = null)
    {
        if (!is_array($name) && !$name instanceof \Traversable) {
            $routes = array($name => $route);
        }
        else {
            $routes = $name;
        }

        foreach($routes as $name => $route) {
            $this->routes[$name] = $route;
        }

        return $this;
    }

    /**
     * Get all routes
     *
     * @return array
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * Get route data by it's name
     *
     * @param  string $name
     * @param  mixed  $default
     * @return \Aero\Routing\Route|array|null
     */
    public function get($name, $default = null)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }
        return $default;
    }

    /**
     * Get route object by it's name
     *
     * @param  string $name
     * @param  mixed $default
     * @return \Aero\Routing\Route
     */
    public function getRoute($name, $default = null)
    {
        if (!isset($this->routes[$name])) {
            return $default;
        }

        if (!$this->routes[$name] instanceof Route) {
            $this->routes[$name] = Route::create($this->routes[$name]);
        }

        return $this->routes[$name];
    }

    /**
     * Remove route from stack by it's name
     *
     * @param  string $name
     * @return \Aero\Routing\RouteCollection
     */
    public function remove($name)
    {
        if (isset($this->routes[$name])) {
            unset($this->routes[$name]);
        }
        return $this;
    }

    /**
     * Clear all routes
     *
     * @return \Aero\Routing\RouteCollection
     */
    public function clear()
    {
        $this->routes = array();
        return $this;
    }

    /**
     * Get collection as Iterator.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }
}
