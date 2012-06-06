<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std\Event;

/**
 * Event result stack
 *
 * @category    Aero
 * @package     Aero_Std
 * @subpackage  Aero_Std_Event
 * @author      Alex Zavacki
 * @author      Zend Technologies USA Inc.
 */
class ResultStack extends \SplStack
{
    /**
     * @var bool If collection marked stopped
     */
    protected $stopped = false;

    /**
     * Did the last result provided trigger a short circuit of the stack?
     *
     * @return bool
     */
    public function stopped()
    {
        return $this->stopped;
    }

    /**
     * Mark the collection as stopped (or its opposite)
     *
     * @param  bool $flag
     * @return \Aero\Std\Event\ResultStack
     */
    public function setStopped($flag)
    {
        $this->stopped = (bool) $flag;
        return $this;
    }

    /**
     * Convenient access to the first handler return value.
     *
     * @return mixed The first handler return value
     */
    public function first()
    {
        return parent::bottom();
    }

    /**
     * Convenient access to the last handler return value.
     *
     * If the collection is empty, returns null. Otherwise, returns value
     * returned by last handler.
     *
     * @return mixed The last handler return value
     */
    public function last()
    {
        if (count($this) === 0) {
            return null;
        }
        return parent::top();
    }

    /**
     * Check if any of the results match the given value.
     *
     * @param  mixed $value The value to look for among results
     * @return bool
     */
    public function contains($value)
    {
        foreach ($this as $result) {
            if ($result === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get result from stack
     *
     * If only one item in stack and no callback specified, it will be returned
     *
     * If no callback passed,
     * will be used the default one that checks result for non-empty value
     *
     * @param  callback $callback
     * @return mixed
     */
    public function get($callback = null)
    {
        if (!$callback || !is_callable($callback))
        {
            if (count($this) == 1) {
                return parent::top();
            }
            $callback = function($value) {
                return $value ? true : false;
            };
        }

        foreach ($this as $value) {
            if ($callback($value)) {
                return $value;
            }
        }

        return null;
    }
}
