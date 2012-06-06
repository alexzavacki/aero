<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Event manager aware interface
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 */
interface EventManagerAware
{
    /**
     * Set event manager
     *
     * @param  \Aero\Std\Event\EventManagerInterface $manager
     * @return \Aero\Std\Event\EventManagerAware
     */
    public function setEventManager(EventManagerInterface $manager);

    /**
     * Get event manager
     *
     * @return \Aero\Std\Event\EventManagerInterface
     */
    public function getEventManager();
}
