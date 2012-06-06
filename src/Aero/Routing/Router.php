<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Routing;

/**
 * Router
 *
 * @category    Aero
 * @package     Aero_Routing
 * @author      Alex Zavacki
 */
class Router
{
    /**
     * @var array List of registered routes
     */
    protected $routes = array();

    /**
     * @var string Class name of default route type
     */
    protected $defaultRouteType;

    /**
     * @var string Default name for route with empty name
     */
    protected $indexRouteName = 'index';


    /**
     * Constructor
     *
     * @param array $routes
     */
    public function __construct($routes = array())
    {
        if ($routes) {
            $this->addRoute($routes);
        }
    }

    /**
     * Match a given request
     *
     * @param  mixed $request
     * @return \Aero\Routing\MatchedRoute
     */
    public function match($request)
    {
        foreach ($this->routes as $name => $route)
        {
            $route = $this->getRoute($name);

            if (is_array($params = $route->match($request))) {
                return new MatchedRoute($route, $name, $params);
            }
        }

        return null;
    }

    /**
     * Assemble route path by route name
     *
     * @param  string $name
     * @param  array $params
     * @param  bool $absolute
     * @return string
     */
    public function assemble($name, $params = array(), $absolute = false)
    {
        if (!isset($this->routes[$name])) {
            throw new \LogicException(sprintf('Route "%s" does not exist.', $name));
        }
        return $this->getRoute($name)->assemble($params, $absolute);
    }

    /**
     * Add route(s)
     *
     * @param  \Aero\Routing\RouteInterface|array $route
     * @param  string $name
     * @return \Aero\Routing\Router
     */
    public function addRoute($route, $name = null)
    {
        if (!is_array($route) && !$route instanceof \Traversable) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException('Route name must be a string');
            }
            $routes = array($name => $route);
        }
        else {
            $routes = $route;
        }

        foreach($routes as $name => $route)
        {
            if (is_array($route)) {
                if (!isset($route['type'])) {
                    if (!$this->defaultRouteType) {
                        throw new \LogicException('Default route type not set');
                    }
                    $route['type'] = $this->defaultRouteType;
                }
                $routeClass = $route['type'];
            }
            elseif ($route instanceof RouteInterface) {
                $routeClass = get_class($route);
            }
            else {
                throw new \InvalidArgumentException('Route must be an array or an instance of RouteInterface');
            }

            if (!class_exists($routeClass)) {
                throw new \InvalidArgumentException("Route type '$routeClass' not found");
            }

            if (is_array($route)) {
                $route = $routeClass::getValidatedFormattedArrayData($route);
            }

            if (is_numeric($name)) {
                $name = $routeClass::generateName($route);
            }
            if (!is_string($name) || $name == '') {
                $name = $this->indexRouteName;
            }

            $this->routes[$name] = $route;
        }

        return $this;
    }

    /**
     * Check if route exists in route stack
     *
     * @param  string|\Aero\Routing\RouteInterface $route
     * @return bool
     */
    public function hasRoute($route)
    {
        if (is_string($route)) {
            return isset($this->routes[$route]);
        }
        return array_search($route, $this->routes, true) !== false;
    }

    /**
     * Get route as instance of RouteInterface
     *
     * @param  string $name
     * @param  mixed $default
     * @return \Aero\Routing\RouteInterface|mixed
     */
    public function getRoute($name, $default = null)
    {
        if (!isset($this->routes[$name])) {
            return $default;
        }

        if (!$this->routes[$name] instanceof RouteInterface)
        {
            $rawRoute = $this->routes[$name];

            if (!is_array($rawRoute) || !isset($rawRoute['type'])
                || !class_exists($rawRoute['type'])
            ) {
                return $default;
            }

            $this->routes[$name] = call_user_func(
                array($rawRoute['type'], 'create'), $this->routes[$name]
            );
        }

        return $this->routes[$name];
    }

    /**
     * Get raw route data by it's name
     *
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function getRawRoute($name, $default = null)
    {
        if (isset($this->routes[$name])) {
            return $this->routes[$name];
        }
        return $default;
    }

    /**
     * Get all routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Remove route from stack by it's name
     *
     * @param  string|\Aero\Routing\RouteInterface $route
     * @return \Aero\Routing\Router
     */
    public function removeRoute($route)
    {
        if (is_string($route)) {
            if (isset($this->routes[$route])) {
                unset($this->routes[$route]);
            }
        }
        elseif (($key = array_search($route, $this->routes, true) !== false)) {
            unset($this->routes[$key]);
        }

        return $this;
    }

    /**
     * Clear all routes
     *
     * @return \Aero\Routing\Router
     */
    public function clearRoutes()
    {
        $this->routes = array();
        return $this;
    }

    /**
     * Set default route type class name
     *
     * @param  string $defaultRouteType
     * @return \Aero\Routing\Router
     */
    public function setDefaultRouteType($defaultRouteType)
    {
        $this->defaultRouteType = (string) $defaultRouteType;
        return $this;
    }

    /**
     * Get default route type class name
     *
     * @return string
     */
    public function getDefaultRouteType()
    {
        return $this->defaultRouteType;
    }

    /**
     * @param  string $indexRouteName
     * @return \Aero\Routing\Router
     */
    public function setIndexRouteName($indexRouteName)
    {
        $this->indexRouteName = (string) $indexRouteName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIndexRouteName()
    {
        return $this->indexRouteName;
    }
}
