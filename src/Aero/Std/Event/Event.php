<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Base event class
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 * @author      Fabien Potencier
 */
class Event implements EventInterface
{
    /**
     * @var string Event name
     */
    protected $name;

    /**
     * @var array|\ArrayAccess|object The event parameters
     */
    protected $params = array();

    /**
     * @var bool Whether or not to stop propagation
     */
    protected $propagationStopped = false;


    /**
     * Get event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the event name
     *
     * @param  string $name
     * @return \Aero\Std\Event\Event
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get all parameters
     *
     * @return array|object|\ArrayAccess
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get an individual parameter
     *
     * If the parameter does not exist, the $default value will be returned.
     *
     * @param  string|int $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        // Check in params that are arrays or implement array access
        if (is_array($this->params) || $this->params instanceof \ArrayAccess) {
            if (!isset($this->params[$name])) {
                return $default;
            }
            return $this->params[$name];
        }

        // Check in normal objects
        if (!isset($this->params->{$name})) {
            return $default;
        }

        return $this->params->{$name};
    }

    /**
     * Replace parameters
     *
     * @throws \InvalidArgumentException
     *
     * @param  array|\ArrayAccess|object $params
     * @return \Aero\Std\Event\Event
     */
    public function replaceParams($params)
    {
        if ($params === null) {
            $params = array();
        }

        if (!is_array($params) && !is_object($params)) {
            throw new \InvalidArgumentException(sprintf(
                'Event parameters must be an array or object; received "%s"',
                (is_object($params) ? get_class($params) : gettype($params))
            ));
        }

        $this->params = $params;
        return $this;
    }

    /**
     * Set parameters
     *
     * @throws \InvalidArgumentException
     *
     * @param  array|\ArrayAccess|object $params
     * @return \Aero\Std\Event\Event
     */
    public function setParams($params)
    {
        if ($params === null) {
            $params = array();
        }

        if (!is_array($params) && !is_object($params)) {
            throw new \InvalidArgumentException(sprintf(
                'Event parameters must be an array or object; received "%s"',
                (is_object($params) ? get_class($params) : gettype($params))
            ));
        }

        foreach ((array) $params as $paramName => $paramValue) {
            $this->setParam($paramName, $paramValue);
        }

        return $this;
    }

    /**
     * Set an individual parameter to a value
     *
     * @param  string|int $name
     * @param  mixed $value
     * @return \Aero\Std\Event\Event
     */
    public function setParam($name, $value)
    {
        if (is_array($this->params) || $this->params instanceof \ArrayAccess) {
            // Arrays or objects implementing array access
            $this->params[$name] = $value;
        }
        else {
            // Objects
            $this->params->{$name} = $value;
        }
        return $this;
    }

    /**
     * Stop further event propagation
     *
     * @param  bool $flag
     * @return void
     */
    public function stopPropagation($flag = true)
    {
        $this->propagationStopped = (bool) $flag;
    }

    /**
     * Is propagation stopped?
     *
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
}
