<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Representation of an event
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 * @author      Fabien Potencier
 */
interface EventInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the event name
     *
     * @param  string $name
     * @return \Aero\Std\Event\EventInterface
     */
    public function setName($name);

    /**
     * Get parameters passed to the event
     *
     * @return array|\ArrayAccess
     */
    public function getParams();

    /**
     * Get a single parameter by name
     *
     * @param  string $name
     * @param  mixed $default Default value to return if parameter does not exist
     * @return mixed
     */
    public function getParam($name, $default = null);

    /**
     * Set event parameters
     *
     * @param  string $params
     * @return \Aero\Std\Event\EventInterface
     */
    public function setParams($params);

    /**
     * Set a single parameter by key
     *
     * @param  string $name
     * @param  mixed $value
     * @return \Aero\Std\Event\EventInterface
     */
    public function setParam($name, $value);

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events
     *
     * @param  bool $flag
     * @return void
     */
    public function stopPropagation($flag = true);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped();
}
