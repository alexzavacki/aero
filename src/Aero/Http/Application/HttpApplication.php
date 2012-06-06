<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Application;

use Aero\Application\AbstractApplication;

use Aero\Std\Event\EventInterface;
use Aero\Std\Event\EventManager;
use Aero\Std\Event\EventManagerAware;
use Aero\Std\Event\EventManagerInterface;
use Aero\Std\Event\ResultStack;

use Aero\Routing\Router;
use Aero\Routing\RouteInterface;
use Aero\Routing\MatchedRoute;

use Aero\Application\Controller\Resolver\ControllerResolverInterface;

use Aero\Application\ApplicationEvent;
use Aero\Application\InjectApplicationEventInterface;

use Aero\Std\Di\ServiceLocator;
use Aero\Std\Di\ServiceLocatorAware;

use Aero\Http\Request;
use Aero\Http\Response;

use Aero\Http\Routing\HttpRouter;

/**
 * Http application (front controller)
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Application
 * @author      Alex Zavacki
 */
class HttpApplication extends AbstractApplication
{
    /**
     * @const Locator services
     */
    const REQUEST_SERVICE  = 'app.request';
    const RESPONSE_SERVICE = 'app.response';
    const ROUTER_SERVICE   = 'app.router';


    /**
     * Run the application
     *
     * @throws \Aero\Http\Application\Exception\RouteNotFoundException
     * @throws \LogicException
     *
     * @param  \Aero\Http\Request|null $request
     * @return \Aero\Http\Response
     */
    public function run($request = null)
    {
        try {
            if ($request instanceof Request) {
                $this->setRequest($request);
            }

            // Create shared application event with request and application objects
            $event = $this->createApplicationEvent();

            $result = $this->triggerApplicationEvent(HttpApplicationEvent::REQUEST, $event);
            if ($result instanceof Response) {
                return $this->filterResponse($result, $event);
            }

            // Now we need our router object
            $event->setParam('router', $this->getRouter());

            $result = $this->triggerApplicationEvent(HttpApplicationEvent::ROUTE, $event);
            if ($result instanceof Response) {
                return $this->filterResponse($result, $event);
            }
            if ($result instanceof ResultStack) {
                $result = $result->get(function($r) {
                    return ($r instanceof MatchedRoute);
                });
            }

            if (!$result instanceof MatchedRoute) {
                $result = $this->triggerApplicationEvent(HttpApplicationEvent::ROUTE_NOT_FOUND, $event);
                if ($result instanceof Response) {
                    return $this->filterResponse($result, $event);
                }
                if ($result instanceof ResultStack) {
                    $result = $result->get(function($r) {
                        return ($r instanceof RouteInterface);
                    });
                }
            }
            if (!$result instanceof MatchedRoute) {
                throw new Exception\RouteNotFoundException('No route :(');
            }

            /** @var $result \Aero\Routing\MatchedRoute */
            $event->setParam('matchedRoute', $result);

            $result = $this->triggerApplicationEvent(HttpApplicationEvent::ROUTE_POST, $event);
            if ($result instanceof Response) {
                return $this->filterResponse($result, $event);
            }

            $response = $this->triggerApplicationEvent(HttpApplicationEvent::DISPATCH, $event);

            if (!$response instanceof Response) {
                $event->setParam('dispatch', $response);
                $response = $this->triggerApplicationEvent(HttpApplicationEvent::VIEW, $event);

                if (!$response instanceof Response) {
                    throw new \LogicException('The controller must return a response');
                }
            }

            return $this->filterResponse($response, $event);
        }
        catch (\Exception $e) {
            return $this->handleException($e, isset($event) ? $event : null);
        }
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Http\Application\HttpApplicationEvent $e
     * @return mixed
     */
    public function route(HttpApplicationEvent $e)
    {
        $request = $e->getRequest();
        $router  = $e->getParam('router');

        if (!$router instanceof Router) {
            throw new \InvalidArgumentException('Router object not set');
        }

        /** @var $router \Aero\Routing\Router */
        return $router->match($request);
    }

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @param  \Aero\Http\Application\HttpApplicationEvent $e
     * @return mixed
     */
    public function dispatch(HttpApplicationEvent $e)
    {
        $request = $e->getRequest();
        $matchedRoute = $e->getParam('matchedRoute');

        if (!$matchedRoute instanceof MatchedRoute) {
            throw new \InvalidArgumentException('MatchedRoute is not valid object');
        }

        $controllerResolver = $this->getControllerResolver();

        if ($controllerResolver instanceof EventManagerAware)
        {
            $app = $this; // for closure

            /** @var $controllerResolver \Aero\Std\Event\EventManagerAware */
            $controllerResolver->getEventManager()->addListener(
                ControllerResolverInterface::NEW_CONTROLLER_CREATED,
                function($event) use ($app, $e)
                {
                    if (!$event instanceof EventInterface) {
                        return;
                    }

                    /** @var $event \Aero\Std\Event\EventInterface */
                    $controller = $event->getParam('controller');

                    if ($controller instanceof ServiceLocatorAware && $app instanceof HttpApplication) {
                        /** @var $controller \Aero\Std\Di\ServiceLocatorAware */
                        /** @var $app \Aero\Http\Application\HttpApplication */
                        $controller->setLocator($app->getLocator());
                    }
                    if ($controller instanceof InjectApplicationEventInterface
                        && $e instanceof ApplicationEvent
                    ) {
                        /** @var $controller \Aero\Application\InjectApplicationEventInterface */
                        /** @var $e \Aero\Application\ApplicationEvent */
                        $controller->setEvent($e);
                    }
                }
            );
        }

        $controller = $controllerResolver->getController($matchedRoute);

        if (!$controller) {
            throw new \LogicException(
                sprintf('Unable to find the controller for path "%s".', $request->getPathInfo())
            );
        }

        $arguments = $controllerResolver->getArguments($matchedRoute, $controller, array(
            $request, $this->getResponse(), $matchedRoute, $this->getLocator(), $e, $this,
        ));

        return call_user_func_array($controller, $arguments);
    }

    /**
     * Filters a response
     *
     * @param  \Aero\Http\Response $response
     * @param  \Aero\Http\Application\HttpApplicationEvent $event
     * @return Response
     */
    protected function filterResponse(Response $response, HttpApplicationEvent $event = null)
    {
        if (!$event instanceof HttpApplicationEvent) {
            $event = $this->createApplicationEvent();
        }
        $event->setParam('response', $response);

        $this->triggerApplicationEvent(HttpApplicationEvent::RESPONSE, $event);

        return $event->getParam('response');
    }

    /**
     * Trying to convert exception to a Response
     *
     * @throws \Exception
     *
     * @param  \Exception $e
     * @param  \Aero\Http\Application\HttpApplicationEvent $event
     *
     * @return \Aero\Http\Response
     */
    protected function handleException(\Exception $e, HttpApplicationEvent $event = null)
    {
        if (!$event instanceof HttpApplicationEvent) {
            $event = $this->createApplicationEvent();
        }
        $event->setParam('exception', $e);

        $response = $this->triggerApplicationEvent(HttpApplicationEvent::EXCEPTION, $event);

        if (!$response instanceof Response) {
            throw $e;
        }

        try {
            return $this->filterResponse($response, $event);
        }
        catch (\Exception $e) {
            return $response;
        }
    }

    /**
     * Trigger custom application event
     *
     * @param  $eventName
     * @param  \Aero\Http\Application\HttpApplicationEvent|array $params
     * @param  callback $callback
     *
     * @return \Aero\Http\Response|\Aero\Std\Event\ResultStack|null
     */
    public function triggerApplicationEvent($eventName, $params = array(), $callback = null)
    {
        $eventManager = $this->getEventManager();

        if (!$eventManager->hasListeners($eventName)) {
            return null;
        }

        if ($params && is_callable($params)) {
            $callback = $params;
            $params = array();
        }

        $event = (!$params instanceof HttpApplicationEvent)
            ? $this->createApplicationEvent($params)
            : $params;

        if (!$callback) {
            $callback = function($r) {
                return ($r instanceof Response);
            };
        }

        $result = $eventManager->trigger($eventName, $event, $callback);
        if ($result->stopped()) {
            return $result->last();
        }

        return $result;
    }

    /**
     * Create and return filled standard application event
     *
     * @param  array $params
     * @return \Aero\Http\Application\HttpApplicationEvent
     */
    public function createApplicationEvent(array $params = array())
    {
        $e = new HttpApplicationEvent();

        $e->setApplication($this);
        $e->setRequest($this->getRequest());
        $e->setResponse($this->getResponse());

        if ($params) {
            $e->setParams($params);
        }

        return $e;
    }

    /**
     * Attach default listeners for route and dispatch events
     *
     * @return void
     */
    protected function attachDefaultListeners()
    {
        $eventmgr = $this->getEventManager();
        $eventmgr->addListener(HttpApplicationEvent::ROUTE, array($this, 'route'));
        $eventmgr->addListener(HttpApplicationEvent::DISPATCH, array($this, 'dispatch'));
    }

    /**
     * @param  \Aero\Http\Request $request
     * @return \Aero\Http\Application\HttpApplication
     */
    public function setRequest(Request $request)
    {
        $this->getLocator()->set(self::REQUEST_SERVICE, $request);
        return $this;
    }

    /**
     * @return \Aero\Http\Request
     */
    public function getRequest()
    {
        $request = $this->getLocator()->get(self::REQUEST_SERVICE);

        if (!$request instanceof Request) {
            $request = $this->createDefaultRequest();
            $this->setRequest($request);
        }

        return $request;
    }

    /**
     * @return \Aero\Http\Request
     */
    protected function createDefaultRequest()
    {
        return Request::createFromGlobals();
    }

    /**
     * @param  \Aero\Http\Response $response
     * @return \Aero\Http\Application\HttpApplication
     */
    public function setResponse(Response $response)
    {
        $this->getLocator()->set(self::RESPONSE_SERVICE, $response);
        return $this;
    }

    /**
     * @return \Aero\Http\Response
     */
    public function getResponse()
    {
        $response = $this->getLocator()->get(self::RESPONSE_SERVICE);

        if (!$response instanceof Response) {
            $response = $this->createDefaultResponse();
            $this->setResponse($response);
        }

        return $response;
    }

    /**
     * @return \Aero\Http\Response
     */
    protected function createDefaultResponse()
    {
        return new Response();
    }

    /**
     * @param  \Aero\Http\Routing\HttpRouter $router
     * @return \Aero\Http\Application\HttpApplication
     */
    public function setRouter($router)
    {
        $this->getLocator()->set(self::ROUTER_SERVICE, $router);
        return $this;
    }

    /**
     * @return \Aero\Routing\Router
     */
    public function getRouter()
    {
        $router = $this->getLocator()->get(self::ROUTER_SERVICE);

        if (!$router instanceof Router) {
            $router = $this->createDefaultRouter();
            $this->setRouter($router);
        }

        return $router;
    }

    /**
     * @return \Aero\Http\Routing\HttpRouter
     */
    protected function createDefaultRouter()
    {
        return new HttpRouter();
    }
}
