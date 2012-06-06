<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application;

use Aero\Std\Di\ServiceLocator;

use Aero\Std\Event\EventManager;
use Aero\Std\Event\EventManagerInterface;

/**
 * Application (front controller)
 *
 * @category    Aero
 * @package     Aero_Application
 * @author      Alex Zavacki
 */
abstract class AbstractApplication
{
    /**
     * @const Locator services
     */
    const CONTROLLER_RESOLVER_SERVICE = 'app.controllerResolver';
    const RESOURCE_MANAGER_SERVICE    = 'app.resourceManager';

    /**
     * @var \Aero\Std\Di\ServiceLocator
     */
    protected $locator;

    /**
     * @var \Aero\Std\Event\EventManagerInterface
     */
    protected $eventManager;


    /**
     * Application cloning
     *
     * @return void
     */
    public function __clone()
    {
        $this->locator = null;
    }

    /**
     * Run the application
     *
     * @param  mixed $request
     * @return mixed
     */
    abstract public function run($request = null);

    /**
     * Set application controller resolver
     *
     * @param  \Aero\Application\Controller\Resolver\ControllerResolverInterface $controllerResolver
     * @return \Aero\Application\AbstractApplication
     */
    public function setControllerResolver(Controller\Resolver\ControllerResolverInterface $controllerResolver)
    {
        $this->getLocator()->set(self::CONTROLLER_RESOLVER_SERVICE, $controllerResolver);
        return $this;
    }

    /**
     * Get application controller resolver
     *
     * If not set create standard resolver as default
     *
     * @return \Aero\Application\Controller\Resolver\ControllerResolverInterface
     */
    public function getControllerResolver()
    {
        $controllerResolver = $this->getLocator()->get(self::CONTROLLER_RESOLVER_SERVICE);

        if (!$controllerResolver instanceof Controller\Resolver\ControllerResolverInterface) {
            $controllerResolver = $this->createDefaultControllerResolver();
            $this->setControllerResolver($controllerResolver);
        }

        return $controllerResolver;
    }

    /**
     * @return \Aero\Application\Controller\Resolver\StandardResolver
     */
    public function createDefaultControllerResolver()
    {
        return new Controller\Resolver\StandardResolver();
    }

    /**
     * Set application's service locator
     *
     * @param  \Aero\Std\Di\ServiceLocator $locator
     * @return \Aero\Application\AbstractApplication
     */
    public function setLocator(ServiceLocator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    /**
     * Get application's service locator (create if not exists)
     *
     * @return \Aero\Std\Di\ServiceLocator
     */
    public function getLocator()
    {
        if (!$this->locator instanceof ServiceLocator) {
            $this->locator = $this->createDefaultLocator();
        }
        return $this->locator;
    }

    /**
     * Create and initialize the service locator
     *
     * @return \Aero\Std\Di\ServiceLocator
     */
    protected function createDefaultLocator()
    {
        $locator = new ServiceLocator();
        $locator->set('application', $this);

        return $locator;
    }

    /**
     * Set application event manager
     *
     * @param  \Aero\Std\Event\EventManagerInterface $manager
     * @return \Aero\Application\AbstractApplication
     */
    public function setEventManager(EventManagerInterface $manager)
    {
        $this->eventManager = $manager;
        return $this;
    }

    /**
     * Get application event manager
     *
     * @return \Aero\Std\Event\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
            $this->attachDefaultListeners();
        }
        return $this->eventManager;
    }

    /**
     * Attach default listeners for route and dispatch events
     *
     * @return void
     */
    protected function attachDefaultListeners()
    {
        // No event listeners by default
    }

    /**
     * @param  \Aero\Application\Resource\ResourceManagerInterface $resourceManager
     * @return \Aero\Application\AbstractApplication
     */
    public function setResourceManager($resourceManager)
    {
        $this->getLocator()->set(self::RESOURCE_MANAGER_SERVICE, $resourceManager);
        return $this;
    }

    /**
     * @return \Aero\Application\Resource\ResourceManagerInterface
     */
    public function getResourceManager()
    {
        $resourceManager = $this->getLocator()->get(self::RESOURCE_MANAGER_SERVICE);

        if (!$resourceManager instanceof Resource\ResourceManagerInterface) {
            $resourceManager = $this->createDefaultResourceManager();
            $this->setResourceManager($resourceManager);
        }

        return $resourceManager;
    }

    /**
     * @return \Aero\Application\Resource\ResourceManagerInterface
     */
    protected function createDefaultResourceManager()
    {
        return new Resource\ResourceManager(new Resource\Locator\StandardResourceLocator());
    }
}
