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
class EventManager implements EventManagerInterface
{
    /**
     * @var array Subscribed events and their listeners
     */
    protected $events = array();

    /**
     * @var array Sorted by priority events
     */
    protected $sortedEvents = array();

    /**
     * @var string Class representing the event being emitted
     */
    protected $eventClass = '\\Aero\\Std\\Event\\Event';

    /**
     * @var int Listener's default priority in stack
     */
    protected $defaultPriority = 1;


    /**
     * Trigger all listeners for a fired event
     *
     * @param  string|\Aero\Std\Event\EventInterface $event
     * @param  array|\ArrayAccess $params Array of arguments; typically, should be associative
     * @param  callback|bool|null $callback Callback that will be called for each iteration result
     *                                      If returns true, iteration will be stopped
     *
     * @return \Aero\Std\Event\ResultStack
     */
    public function trigger($event, $params = null, $callback = null)
    {
        /** @var $e EventInterface */
        if ($event instanceof EventInterface) {
            $e = $event;
            $event = $e->getName();
            $callback = $params;
        }
        elseif ($params instanceof EventInterface) {
            $e = $params;
            $e->setName($event);
        }
        else {
            $e = new $this->eventClass();
            $e->setName($event);
            $e->setParams($params);
        }

        $returnResults = $callback !== false;

        return $this->doTrigger($event, $e, $callback, $returnResults);
    }

    /**
     * @param  string $event Event name
     * @param  \Aero\Std\Event\EventInterface $e Event object
     * @param  callback $callback Callback that will be called for each iteration result
     *                            If returns true, iteration will be stopped
     * @param  bool $returnResults If true, collect results for each listener callback
     *                             and return as ResultStack, else - return null
     *
     * @return \Aero\Std\Event\ResultStack|null
     */
    protected function doTrigger($event, EventInterface $e, $callback, $returnResults = true)
    {
        $results = new ResultStack();
        $listeners = $this->getListeners($event);

        if (!$listeners) {
            return $returnResults ? $results : null;
        }

        foreach ($listeners as $listener)
        {
            $result = call_user_func($listener, $e);

            if ($returnResults) {
                $results->push($result);
            }

            if ($e->isPropagationStopped()) {
                if ($returnResults) {
                    $results->setStopped(true);
                }
                break;
            }

            if ($callback && call_user_func($callback, $result)) {
                if ($returnResults) {
                    $results->setStopped(true);
                }
                break;
            }
        }

        return $returnResults ? $results : null;
    }

    /**
     * Adds an event listener that listens on the specified events.
     *
     * @param string   $eventName The event to listen on
     * @param callback $listener  The listener callback
     * @param integer  $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain
     * @return callback
     */
    public function addListener($eventName, $listener, $priority = null)
    {
        if (!is_int($priority)) {
            $priority = $this->defaultPriority;
        }

        $this->events[$eventName][$priority][] = $listener;
        unset($this->sortedEvents[$eventName]);

        return $listener;
    }

    /**
     * Sets an event listener after clearing
     *
     * @param string   $eventName The event to listen on
     * @param callback $listener  The listener callback
     * @param integer  $priority  The higher this value, the earlier an event
     *                            listener will be triggered in the chain
     * @return callback
     */
    public function setListener($eventName, $listener, $priority = null)
    {
        $this->clearListeners($eventName);
        return $this->addListener($eventName, $listener, $priority);
    }

    /**
     * Checks whether an event has any registered listeners.
     *
     * @param  string $eventName
     * @return boolean
     */
    public function hasListeners($eventName = null)
    {
        if ($eventName !== null) {
            return isset($this->events[$eventName]) && $this->events[$eventName];
        }

        foreach ($this->events as $listeners) {
            if ($listeners) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param  string $eventName
     * @return array Sorted array of listeners for specified event
     */
    public function getListeners($eventName = null)
    {
        if ($eventName !== null) {
            if (!isset($this->sortedEvents[$eventName])) {
                $this->sortListeners($eventName);
            }
            return $this->sortedEvents[$eventName];
        }

        foreach (array_keys($this->events) as $eventName) {
            if (!isset($this->sortedEvents[$eventName])) {
                $this->sortListeners($eventName);
            }
        }

        return $this->sortedEvents;
    }

    /**
     * Removes an event listener from the specified events.
     *
     * @param  string   $eventName The event(s) to remove a listener from
     * @param  callback $listener  The listener to remove
     * @return void
     */
    public function removeListener($eventName, $listener)
    {
        if (!isset($this->events[$eventName])) {
            return;
        }

        foreach ($this->events[$eventName] as $priority => $listeners) {
            if (($key = array_search($listener, $listeners)) !== false) {
                unset($this->events[$eventName][$priority][$key]);
                unset($this->sortedEvents[$eventName]);
            }
        }
    }

    /**
     * Clear listeners
     *
     * If $eventName exists in events stack,
     * listeners for this event only will be removed
     *
     * @param  string $eventName
     * @return void
     */
    public function clearListeners($eventName = null)
    {
        if ($eventName === null) {
            $this->events = array();
            $this->sortedEvents = array();
        }
        elseif (isset($this->events[$eventName])) {
            unset($this->events[$eventName]);
            unset($this->sortedEvents[$eventName]);
        }
    }

    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param  string $eventName The name of the event
     * @return void
     */
    private function sortListeners($eventName)
    {
        $this->sortedEvents[$eventName] = array();

        if (isset($this->events[$eventName])) {
            krsort($this->events[$eventName]);
            $this->sortedEvents[$eventName] = call_user_func_array(
                'array_merge', $this->events[$eventName]
            );
        }
    }

    /**
     * Attach a listener aggregate
     *
     * Listener aggregates accept an EventManagerInterface instance, and call addListener()
     * one or more times, typically to attach to multiple events using local
     * methods.
     *
     * @param  \Aero\Std\Event\ListenerAggregateInterface $aggregate
     * @return mixed
     */
    public function addListenerAggregate(ListenerAggregateInterface $aggregate)
    {
        return $aggregate->attach($this);
    }

    /**
     * Detach a listener aggregate
     *
     * Listener aggregates accept an EventManagerInterface instance,
     * and call removeListener() of all previously attached listeners.
     *
     * @param  \Aero\Std\Event\ListenerAggregateInterface $aggregate
     * @return mixed
     */
    public function removeListenerAggregate(ListenerAggregateInterface $aggregate)
    {
        return $aggregate->detach($this);
    }

    /**
     * Retrieve all registered events
     *
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->events);
    }

    /**
     * Set the event class to utilize
     *
     * @param  string $class
     * @return \Aero\Std\Event\EventManagerInterface
     */
    public function setEventClass($class)
    {
        $this->eventClass = $class;
        return $this;
    }

    /**
     * Set default priority
     *
     * @param  int $defaultPriority
     * @return \Aero\Std\Event\EventManagerInterface
     */
    public function setDefaultPriority($defaultPriority)
    {
        $this->defaultPriority = (int) $defaultPriority;
        return $this;
    }

    /**
     * Get default priority
     *
     * @return int
     */
    public function getDefaultPriority()
    {
        return $this->defaultPriority;
    }
}
