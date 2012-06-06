<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Listener aggregate interface
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 * @author      Fabien Potencier
 */
interface ListenerAggregateInterface
{
    /**
     * Attach one or more listeners
     *
     * @param  \Aero\Std\Event\EventManagerInterface $eventManager
     * @return mixed
     */
    public function attach(EventManagerInterface $eventManager);

    /**
     * Detach all previously attached listeners
     *
     * @param  \Aero\Std\Event\EventManagerInterface $events
     * @return mixed
     */
    public function detach(EventManagerInterface $events);
}
