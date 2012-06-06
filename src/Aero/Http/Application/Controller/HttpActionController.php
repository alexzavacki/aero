<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application\Controller;

use Aero\Application\Controller\ActionController;

use Aero\Http\Application\HttpApplicationEvent;

use Aero\Routing\MatchedRoute;

use Aero\Http\View\HttpView;

use Aero\Http\Response;

/**
 * Abstract HTTP action controller
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Application
 * @subpackage  Aero_Http_Application_Controller
 * @author      Alex Zavacki
 */
abstract class HttpActionController extends ActionController
{
    /**
     * Get the Request object
     *
     * @throws \InvalidArgumentException
     *
     * @return \Aero\Http\Request
     */
    public function getRequest()
    {
        if (!$this->event instanceof HttpApplicationEvent) {
            throw new \InvalidArgumentException('Http action controller event must be instance of HttpApplicationEvent');
        }
        return $this->event->getRequest();
    }

    /**
     * Get the Response object
     *
     * @throws \InvalidArgumentException
     *
     * @return \Aero\Http\Response
     */
    public function getResponse()
    {
        if (!$this->event instanceof HttpApplicationEvent) {
            throw new \InvalidArgumentException('Http action controller event must be instance of HttpApplicationEvent');
        }
        return $this->event->getResponse();
    }

    /**
     * Get param value from matched route if set
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (!$this->event instanceof HttpApplicationEvent) {
            throw new \InvalidArgumentException(
                'Http action controller event must be instance of HttpApplicationEvent'
            );
        }

        $matchedRoute = $this->event->getParam('matchedRoute');

        if (!$matchedRoute instanceof MatchedRoute) {
            return $default;
        }

        /** @var $matchedRoute \Aero\Routing\MatchedRoute */
        return $matchedRoute->getParam($name, $default);
    }

    /**
     * Render view and return as response object
     *
     * @param  string $name
     * @param  array  $parameters
     * @param  \Aero\Http\Response $response
     * @return \Aero\Http\Response
     */
    public function render($name, $parameters = array(), $response = null)
    {
        if (!$response instanceof Response) {
            $response = $this->getResponse();
        }

        $response->setContent($this->renderView($name, $parameters));

        return $response;
    }

    /**
     * Render view and return as string
     *
     * @param  string $name
     * @param  array $parameters
     * @return string
     */
    public function renderView($name = null, $parameters = array())
    {
        $view = new HttpView();
        return $view->render($name, $parameters);
    }

    /**
     * @return \Aero\Http\Application\Controller\PluginBroker
     */
    protected function createDefaultPluginBroker()
    {
        return new PluginBroker();
    }

    /**
     * @return \Aero\Http\Application\Controller\PluginBroker
     */
    protected static function createDefaultStaticPluginBroker()
    {
        return new PluginBroker();
    }
}
