<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Routing;

/**
 * Matched route info
 *
 * @category    Aero
 * @package     Aero_Routing
 * @author      Alex Zavacki
 */
class MatchedRoute
{
    /**
     * @var \Aero\Routing\RouteInterface
     */
    protected $route;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var array
     */
    protected $params;


    /**
     * Constructor.
     *
     * @param \Aero\Routing\RouteInterface $route
     * @param string $name
     * @param array $params
     */
    public function __construct($route, $name, $params = array())
    {
        $this->route     = $route;
        $this->routeName = (string) $name;
        $this->params    = (array) $params;
    }

    /**
     * @return \Aero\Routing\RouteInterface
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Get params value
     *
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
