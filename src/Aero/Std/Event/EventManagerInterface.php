<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Standard event manager
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 * @author      Fabien Potencier
 */
interface EventManagerInterface
{
    /**
     * Trigger all listeners for a fired event
     *
     * @param  mixed|string|\Aero\Std\Event\EventInterface $event
     * @param  mixed|array|\ArrayAccess $params Array of arguments; typically, should be associative
     * @param  callback|bool $callback Callback that will be called for each iteration result
     *                                 If returns true, iteration will be stopped
     * @return \Aero\Std\Event\ResultStack
     */
    public function trigger($event, $params = null, $callback = null);

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param  string   $eventName The event to listen on
     * @param  callback $listener  The listener callback
     * @param  integer  $priority  The higher this value, the earlier an event
     *                             listener will be triggered in the chain (defaults to 0)
     * @return callback
     */
    public function addListener($eventName, $listener, $priority = null);

    /**
     * Sets an event listener after clearing
     *
     * @param  string   $eventName The event to listen on
     * @param  callback $listener  The listener callback
     * @param  integer  $priority  The higher this value, the earlier an event
     *                             listener will be triggered in the chain
     * @return callback
     */
    public function setListener($eventName, $listener, $priority = null);

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param  string $eventName
     * @return boolean
     */
    public function hasListeners($eventName = null);

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param  string $eventName
     * @return array Sorted array of listeners for specified event
     */
    public function getListeners($eventName = null);

    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName The event(s) to remove a listener from.
     * @param callback $listener  The listener to remove.
     */
    public function removeListener($eventName, $listener);

    /**
     * Clear listeners
     *
     * If $eventName exists in events stack,
     * listeners for this event only will be removed
     *
     * @param  string $eventName
     * @return void
     */
    public function clearListeners($eventName = null);

    /**
     * Retrieve all registered events
     *
     * @return array
     */
    public function getEvents();
}
