<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application\Controller\Resolver;

use Aero\Routing\MatchedRoute;

use Aero\Std\Event\EventManagerInterface;
use Aero\Std\Event\EventManagerAware;
use Aero\Std\Event\EventManager;

/**
 * Standard controller resolver
 *
 * @category    Aero
 * @package     Aero_Application
 * @subpackage  Aero_Application_Controller
 * @author      Alex Zavacki
 */
class StandardResolver implements ControllerResolverInterface, EventManagerAware
{
    /**
     * @var \Aero\Std\Event\EventManagerInterface
     */
    protected $eventManager;


    /**
     * Return the controller callback
     *
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Routing\MatchedRoute $matchedRoute
     *
     * @return callback
     */
    public function getController($matchedRoute)
    {
        if (!$matchedRoute instanceof MatchedRoute) {
            throw new \InvalidArgumentException('Matched route must be instance of \\Aero\\Routing\\MatchedRoute');
        }

        $controller = $matchedRoute->getParam('controller');

        if (is_array($controller)) {
            return $controller;
        }
        elseif (is_object($controller) && method_exists($controller, '__invoke')) {
            return $controller;
        }
        elseif (!is_string($controller)) {
            throw new \InvalidArgumentException('Controller must be an array, an object or a string');
        }

        if (function_exists($controller)) {
            return $controller;
        }

        if (strpos($controller, ':') === false && method_exists($controller, '__invoke')) {
            return new $controller;
        }

        list($controller, $method) = $this->createControllerFromString($controller);

        if (!method_exists($controller, $method)) {
            throw new \InvalidArgumentException(sprintf('Method "%s::%s" does not exist.', get_class($controller), $method));
        }

        return array($controller, $method);
    }

    /**
     * Returns a callable controller for the given string.
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @param  string $controller
     *
     * @return callback
     */
    protected function createControllerFromString($controller)
    {
        if (strpos($controller, '::') === false) {
            $controller = $this->parseControllerName($controller);
        }

        if (strpos($controller, '::') === false) {
            throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        if (method_exists($class, 'getActionMethod')) {
            $method = call_user_func(array($class, 'getActionMethod'), $method);
        }

        $controller = new $class();

        $this->getEventManager()->trigger(self::NEW_CONTROLLER_CREATED, array(
            'controller' => $controller
        ), false);

        return array($controller, $method);
    }

    /**
     * Specific controller name parsing
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $controller
     *
     * @return string
     */
    protected function parseControllerName($controller)
    {
        throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
    }

    /**
     * Get the arguments to pass to the controller.
     *
     * @param  \Aero\Routing\MatchedRoute $matchedRoute
     * @param  callback $controller
     * @param  array    $variables
     *
     * @return array
     */
    public function getArguments($matchedRoute, $controller, $variables = array())
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        }
        elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        }
        else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($matchedRoute, $controller, $r->getParameters(), $variables);
    }

    /**
     * Get the arguments for the given parameters list
     *
     * @param  \Aero\Routing\MatchedRoute $matchedRoute
     * @param  callback $controller
     * @param  array    $parameters
     * @param  array    $variables
     *
     * @return array
     */
    protected function doGetArguments($matchedRoute, $controller, array $parameters, $variables = array())
    {
        $arguments = array();

        $routeParams = ($matchedRoute instanceof MatchedRoute) ? $matchedRoute->getParams() : array();
        $classVariables = null;

        foreach ($parameters as $param)
        {
            /** @var $param \ReflectionParameter */

            // by name
            if (array_key_exists($param->getName(), $routeParams)) {
                $arguments[] = $routeParams[$param->getName()];
                continue;
            }

            // by class
            if (($classVariables === null || $classVariables) && ($refClass = $param->getClass()))
            {
                /** @var $refClass \ReflectionClass */
                $paramClassName = $refClass->getName();

                if ($classVariables === null) {
                    if ($variables && is_array($variables))
                    {
                        if ($classVariables = array_filter($variables, 'is_object')) {
                            $classVariables = array_combine(
                                array_map('get_class', $classVariables),
                                $classVariables
                            );
                        }
                    }
                    else {
                        $classVariables = array();
                    }
                }

                if (isset($classVariables[$paramClassName])) {
                    $arguments[] = $classVariables[$paramClassName];
                    continue;
                }

                foreach ($classVariables as $className => $var) {
                    if (is_subclass_of($className, $paramClassName)) {
                        $arguments[] = $var;
                        continue 2;
                    }
                }

                $this->throwControllerParameterException($controller, $param);
            }

            // by default value
            if ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
                continue;
            }

            $this->throwControllerParameterException($controller, $param);
        }

        return $arguments;
    }

    /**
     * Throw exception for invalid controller parameter
     *
     * @throws \RuntimeException
     *
     * @param  mixed                $controller
     * @param  \ReflectionParameter $param
     *
     * @return void
     */
    protected function throwControllerParameterException($controller, \ReflectionParameter $param)
    {
        if (is_array($controller)) {
            $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
        }
        elseif (is_object($controller)) {
            $repr = get_class($controller);
        }
        else {
            $repr = $controller;
        }

        throw new \RuntimeException(
            sprintf('No value found for parameter "$%s" in controller "%s"', $param->getName(), $repr)
        );
    }

    /**
     * Set event manager
     *
     * @param  \Aero\Std\Event\EventManagerInterface $manager
     *
     * @return \Aero\Application\Controller\Resolver\StandardResolver
     */
    public function setEventManager(EventManagerInterface $manager)
    {
        $this->eventManager = $manager;
        return $this;
    }

    /**
     * Get event manager
     *
     * @return \Aero\Std\Event\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }
}
