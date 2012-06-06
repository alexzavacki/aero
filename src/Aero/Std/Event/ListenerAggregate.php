<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Standard listener aggregate
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 * @author      Fabien Potencier
 */
class ListenerAggregate implements ListenerAggregateInterface
{
    /**
     * @var array Registered aggregate's listeners
     */
    protected $handlers = array();

    /**
     * @var array Array of event listeners to subscribe
     */
    protected $subscribedEvents = array();


    /**
     * Constructor
     *
     * @param array $subscribedEvents
     */
    public function __construct($subscribedEvents = null)
    {
        if (is_array($subscribedEvents)) {
            $this->setSubscribedEvents($subscribedEvents);
        }
    }

    /**
     * Attach one or more listeners
     *
     * @throws \InvalidArgumentException
     *
     * @param  \Aero\Std\Event\EventManagerInterface $eventManager
     * @return void
     */
    public function attach(EventManagerInterface $eventManager)
    {
        foreach ($this->subscribedEvents as $eventName => $eventListeners)
        {
            if (
                is_string($eventListeners)
                || (is_array($eventListeners) && isset($eventListeners[1]) && is_int($eventListeners[1]))
                || is_callable($eventListeners)
            ) {
                $eventListeners = array($eventListeners);
            }
            elseif (!is_array($eventListeners)) {
                throw new \InvalidArgumentException('Event listener must be a string, array or callback');
            }

            if (!isset($this->handlers[$eventName])) {
                $this->handlers[$eventName] = array();
            }

            foreach ($eventListeners as $callback)
            {
                $priority = null;
                if (is_array($callback) && isset($callback[1]) && is_int($callback[1])) {
                    $priority = $callback[1];
                    $callback = $callback[0];
                }
                $listener = is_string($callback) ? array($this, $callback) : $callback;
                $this->handlers[$eventName][] = $eventManager->addListener($eventName, $listener, $priority);
            }
        }
    }

    /**
     * Detach all previously attached listeners
     *
     * @param  \Aero\Std\Event\EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->handlers as $eventName => $handler) {
            foreach ($handler as $key => $listener) {
                $events->removeListener($eventName, $listener);
                unset($this->handlers[$eventName][$key]);
            }
            unset($this->handlers[$eventName]);
        }
        $this->handlers = array();
    }

    /**
     * Set array of event listeners to subscribe
     *
     * @param  array $subscribedEvents
     * @return \Aero\Std\Event\ListenerAggregate
     */
    public function setSubscribedEvents(array $subscribedEvents)
    {
        $this->subscribedEvents = $subscribedEvents;
        return $this;
    }

    /**
     * Get array of event listeners to subscribe
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return $this->subscribedEvents;
    }
}
