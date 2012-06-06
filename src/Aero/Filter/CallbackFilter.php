<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter;

/**
 * Callback filter
 *
 * @category    Aero
 * @package     Aero_Filter
 * @author      Alex Zavacki
 */
class CallbackFilter extends AbstractFilter
{
    /**
     * @var callback
     */
    protected $callback;


    /**
     * Constructor.
     *
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        if (is_callable($options)) {
            $options = array('callback' => $options);
        }

        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set filter options
     *
     * @param  array $options
     * @return \Aero\Filter\AbstractFilter
     */
    public function setOptions($options = array())
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case 'callback':
                    $this->setCallback($value);
                    break;
            }
        }
        return $this;
    }

    /**
     * @return callback
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  callback $callback
     * @return \Aero\Filter\CallbackFilter
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback given');
        }

        $this->callback = $callback;
        return $this;
    }

    /**
     * Returns the result of filtering $value
     *
     * @throws \InvalidArgumentException
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (!$this->callback) {
            throw new \InvalidArgumentException('Callback not set');
        }
        return call_user_func($this->callback, $value);
    }
}
