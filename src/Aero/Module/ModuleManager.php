<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Module;

use Aero\Application\AbstractApplication;

use Aero\Std\Event\EventManagerInterface;
use Aero\Std\Event\EventManager;
use Aero\Std\Event\EventInterface;

/**
 * Module manager
 *
 * @category    Aero
 * @package     Aero_Module
 * @author      Alex Zavacki
 */
class ModuleManager
{
    /**
     * @var array
     */
    protected $modules = array();

    /**
     * @var array
     */
    protected $moduleDirs = array();

    /**
     * @var array
     */
    protected $loadedModules = array();

    /**
     * @var \Aero\Std\Event\EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Aero\Module\ModuleEvent
     */
    protected $event;


    /**
     * Constructor.
     *
     * @param array|\Traversable $modules
     * @param string|array $dirs
     */
    public function __construct($modules, $dirs)
    {
        $this->setModules($modules);
        $this->setModuleDirs($dirs);
    }

    /**
     * Load modules
     *
     * @return \Aero\Module\ModuleManager
     */
    public function loadModules()
    {
        foreach ($this->modules as $moduleName) {
            $this->loadModule($moduleName);
        }
        return $this;
    }

    /**
     * Load a specific module by name.
     *
     * @throws \RuntimeException
     *
     * @param  string $moduleName
     * @return object
     */
    public function loadModule($moduleName)
    {
        if (isset($this->loadedModules[$moduleName])) {
            return $this->loadedModules[$moduleName];
        }

        $moduleClassName = str_replace('/', '\\', $moduleName) . "\\Module";

        if (!class_exists($moduleClassName)) {
            foreach ($this->moduleDirs as $dir) {
                $moduleFilename = $dir . "/{$moduleName}/Module.php";
                if (file_exists($moduleFilename)) {
                    require $moduleFilename;
                }
            }
        }

        if (!class_exists($moduleClassName, false)) {
            throw new \RuntimeException(
                sprintf('Module "%s" couldn\'t be loaded', $moduleName)
            );
        }

        $this->loadedModules[$moduleName] = new $moduleClassName();

        return $this->loadedModules[$moduleName];
    }

    /**
     * Get the array of module names that this manager should load.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Set an array or Traversable of module names that this module manager should load.
     *
     * @throws \InvalidArgumentException
     *
     * @param  array|\Traversable $modules
     * @return \Aero\Module\ModuleManager
     */
    public function setModules($modules)
    {
        if (!is_array($modules) && !$modules instanceof \Traversable) {
            throw new \InvalidArgumentException('Modules must be an array or Traversable');
        }
        $this->modules = $modules;
        return $this;
    }

    /**
     * @param  array $dirs
     * @return \Aero\Module\ModuleManager
     */
    public function setModuleDirs($dirs)
    {
        $this->moduleDirs = (array) $dirs;
        return $this;
    }

    /**
     * @return array
     */
    public function getModuleDirs()
    {
        return $this->moduleDirs;
    }

    /**
     * Get an array of the loaded modules.
     *
     * @return array
     */
    public function getLoadedModules()
    {
        return $this->loadedModules;
    }

    /**
     * Get the module event
     *
     * @return \Aero\Module\ModuleEvent
     */
    public function getModuleEvent()
    {
        if (!$this->event instanceof ModuleEvent) {
            $this->setModuleEvent(new ModuleEvent);
        }
        return $this->event;
    }

    /**
     * Set the module event
     *
     * @param  \Aero\Module\ModuleEvent $event
     * @return \Aero\Module\ModuleManager
     */
    public function setModuleEvent(ModuleEvent $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Set event manager
     *
     * @param  \Aero\Std\Event\EventManagerInterface $manager
     * @return \Aero\Module\ModuleManager
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
