<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Module;

use Aero\Std\Event\Event;

/**
 * Module event
 *
 * @category    Aero
 * @package     Aero_Module
 * @author      Alex Zavacki
 */
class ModuleEvent extends Event
{
    /**
     * Get the name of a given module
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->getParam('moduleName');
    }

    /**
     * Set the name of a given module
     *
     * @param  string $moduleName
     * @return \Aero\Module\ModuleEvent
     */
    public function setModuleName($moduleName)
    {
        if (!is_string($moduleName)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a string as an argument; %s provided'
                ,__METHOD__, gettype($moduleName)
            ));
        }
        $this->setParam('moduleName', $moduleName);
        return $this;
    }

    /**
     * Get module object
     *
     * @return object
     */
    public function getModule()
    {
        return $this->getParam('module');
    }

    /**
     * Set module object to compose in this event
     *
     * @param  object $module
     * @return \Aero\Module\ModuleEvent
     */
    public function setModule($module)
    {
        if (!is_object($module)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a module object as an argument; %s provided'
                ,__METHOD__, gettype($module)
            ));
        }
        $this->setParam('module', $module);
        return $this;
    }
}
