<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Application;

/**
 * Application event injector
 *
 * @category    Aero
 * @package     Aero_Application
 * @author      Alex Zavacki
 */
interface InjectApplicationEventInterface
{
    /**
     * Assign application event
     *
     * @param  \Aero\Application\ApplicationEvent $event
     * @return void
     */
    public function setEvent(ApplicationEvent $event);

    /**
     * Get application event
     *
     * @return \Aero\Application\ApplicationEvent
     */
    public function getEvent();
}
