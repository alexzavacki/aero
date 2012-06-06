<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Routing;

/**
 * Route interface
 *
 * @category    Aero
 * @package     Aero_Routing
 * @author      Alex Zavacki
 */
interface RouteInterface
{
    /**
     * Match a given request
     *
     * @param  mixed $request
     * @return array|false
     */
    public function match($request);

    /**
     * Assemble the route
     *
     * @param  array $params
     * @param  bool $absolute
     * @return string
     */
    public function assemble($params = array(), $absolute = false);

    /**
     * Create route object from specified data
     *
     * @static
     * @param  mixed $data
     * @return \Aero\Routing\RouteInterface
     */
    public static function create($data);

    /**
     * Validate and return formatted route array data
     *
     * @static
     * @param  array $data
     * @return array
     */
    public static function getValidatedFormattedArrayData(array $data);

    /**
     * Generate route name depends on route data
     *
     * @static
     * @param  mixed $route
     * @return string
     */
    public static function generateName($route);
}
