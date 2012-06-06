<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Std;

use ArrayObject;

/**
 * Parameters holder
 *
 * @category    Aero
 * @package     Aero_Std
 * @author      Alex Zavacki
 */
class Parameters extends ArrayObject
{
    /**
     * @var callback Assign filter callback
     */
    protected $assignFilter;


    /**
     * Constructor
     *
     * @param array    $values
     * @param callback $assignFilter
     */
    public function __construct($values = null, $assignFilter = null)
    {
        if ($values === null) {
            $values = array();
        }
        elseif (is_callable($values)) {
            $assignFilter = $values;
            $values = array();
        }

        if ($assignFilter !== null) {
            $this->setAssignFilter($assignFilter);
            $values = array_map($assignFilter, $values, array_keys($values));
        }

        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Retrieve by key
     *
     * Returns null if the key does not exist.
     *
     * @param  string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        if (array_key_exists($name, $this)) {
            return parent::offsetGet($name);
        }
        return null;
    }

    /**
     * Get parameter by name
     *
     * @param  string $name
     * @param  mixed  $default
     * @param  bool   $deep
     * @return mixed
     */
    public function get($name, $default = null, $deep = false)
    {
        if (!$deep || ($pos = strpos($name, '[')) === false) {
            return array_key_exists($name, $this) ? parent::offsetGet($name) : $default;
        }

        if ($pos > 0) {
            $root = substr($name, 0, $pos);

            if (!array_key_exists($root, $this)) {
                return $default;
            }

            $value = $this[$root];
            $subkeys = substr($name, $pos);
        }
        else {
            $value = (array) $this;
            $subkeys = $name;
        }

        if (substr($subkeys, 0, 1) != '[') {
            throw new \InvalidArgumentException('Malformed path');
        }
        $subkeys = substr($subkeys, 1);

        if (substr($subkeys, -1) != ']') {
            throw new \InvalidArgumentException('Malformed path. Path must end with "]"');
        }
        $subkeys = substr($subkeys, 0, -1);

        foreach (explode('][', $subkeys) as $subkey)
        {
            $subkey = trim($subkey);

            if ($subkey == '' || strpos($subkey, '[') !== false || strpos($subkey, ']') !== false) {
                throw new \InvalidArgumentException('Malformed path');
            }

            if (!is_array($value) || !array_key_exists($subkey, $value)) {
                return $default;
            }

            $value = $value[$subkey];
        }

        return $value;
    }

    /**
     * Sets the value at the specified index to newval
     *
     * @param  mixed $name
     * @param  mixed $newval
     * @return void
     */
    public function offsetSet($name, $newval)
    {
        if ($this->assignFilter) {
            $newval = call_user_func($this->assignFilter, $newval, $name);
        }
        parent::offsetSet($name, $newval);
    }

    /**
     * @param  string $name
     * @param  mixed $value
     * @return \Aero\Std\Parameters
     */
    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
        return $this;
    }

    /**
     * Add an array of values
     *
     * Existing keys will be overwritten by default
     *
     * @param  array $parameters
     * @param  bool $overwrite
     * @return \Aero\Std\Parameters
     */
    public function add(array $parameters, $overwrite = true)
    {
        if (!$overwrite) {
            $parameters = array_diff_key($parameters, (array) $this);
        }
        foreach ($parameters as $key => $value) {
            $this->offsetSet($key, $value);
        }

        return $this;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this);
    }

    /**
     * Unset a parameter
     *
     * @param  string $name
     * @return \Aero\Std\Parameters
     */
    public function remove($name)
    {
        if (array_key_exists($name, $this)) {
            unset($this[$name]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return (array) $this;
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys((array) $this);
    }

    /**
     * Populate from native PHP array
     *
     * @param  array $values
     * @return \Aero\Std\Parameters
     */
    public function replace(array $values)
    {
        $this->exchangeArray(array());
        $this->add($values);
        return $this;
    }

    /**
     * Serialize to native PHP array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getArrayCopy();
    }

    /**
     * @param  callback $assignFilter
     * @return \Aero\Std\Parameters
     */
    public function setAssignFilter($assignFilter)
    {
        if ($assignFilter !== null && !is_callable($assignFilter)) {
            throw new \InvalidArgumentException('Assign filter must be valid callback or null');
        }
        $this->assignFilter = $assignFilter;
        return $this;
    }

    /**
     * @return callback
     */
    public function getAssignFilter()
    {
        return $this->assignFilter;
    }
}
