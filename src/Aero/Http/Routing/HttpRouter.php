<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Routing;

use Aero\Routing\Router;
use Aero\Routing\MatchedRoute;

/**
 * Http route stack
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Routing
 * @author      Alex Zavacki
 */
class HttpRouter extends Router
{
    /**
     * @var string Class name of default route type
     */
    protected $defaultRouteType = 'Aero\\Http\\Routing\\PatternRoute';


    /**
     * Match a given request
     *
     * @param  \Aero\Http\Request $request
     * @return \Aero\Routing\MatchedRoute
     */
    public function match($request)
    {
        $pathinfo = urldecode($request->getPathInfo());

        foreach ($this->routes as $name => $route)
        {
            $pattern = null;

            if (is_array($route) && isset($route['pattern'])) {
                $pattern = $route['pattern'];
            }
            elseif ($route instanceof PatternRoute) {
                /** @var $route \Aero\Http\Routing\PatternRoute */
                $pattern = $route->getPattern();
            }

            if ($pattern !== null)
            {
                if (strlen($pattern) && ($pattern = rtrim($pattern, '/')) == '') {
                    $pattern = '/';
                }

                if (($pos = strpos($pattern, '{')) !== false) {
                    $staticPrefix = rtrim(substr($pattern, 0, $pos), '/');
                    if ($staticPrefix !== '' && strpos($pathinfo, $staticPrefix) !== 0) {
                        continue;
                    }
                }
                elseif ($pattern != $pathinfo) {
                    continue;
                }
            }

            $route = $this->getRoute($name);

            if (is_array($params = $route->match($request))) {
                return new MatchedRoute($route, $name, $params);
            }
        }

        return null;
    }
}
