<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form;

/**
 * Collection
 *
 * @category    Aero
 * @package     Aero_Form
 * @author      Alex Zavacki
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $list = array();

    /**
     * @var array
     */
    protected $unifiedNames = array();


    /**
     * @param  string $name
     * @param  string $value
     *
     * @return \Aero\Form\Collection
     */
    public function set($name, $value)
    {
        if (!isset($this->list[$name]))
        {
            $unifiedName = strtolower($name);
            if (isset($this->unifiedNames[$unifiedName])) {
                $name = $this->unifiedNames[$unifiedName];
            }
            else {
                $this->unifiedNames[$unifiedName] = $name;
            }
        }

        $this->list[$name] = $value;
        return $this;
    }

    /**
     * @param  array $items
     * @return \Aero\Form\Collection
     */
    public function setFromArray(array $items)
    {
        foreach ($items as $name => $value) {
            $this->set($name, $value);
        }
        return $this;
    }

    /**
     * @param  string $name
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset($this->list[$name])) {
            return $this->list[$name];
        }

        $unifiedName = strtolower($name);

        if (!isset($this->unifiedNames[$unifiedName])) {
            return $default;
        }

        return isset($this->list[$this->unifiedNames[$unifiedName]])
            ? $this->list[$this->unifiedNames[$unifiedName]]
            : $default;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        if (isset($this->list[$name])) {
            return true;
        }

        $unifiedName = strtolower($name);

        if (!isset($this->unifiedNames[$unifiedName])) {
            return false;
        }

        return isset($this->list[$this->unifiedNames[$unifiedName]]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->list;
    }

    /**
     * @param  string $name
     * @return \Aero\Form\Collection
     */
    public function remove($name)
    {
        if (isset($this->list[$name])) {
            unset($this->list[$name]);
        }
        if (isset($this->unifiedNames[$name])) {
            unset($this->unifiedNames[$name]);
        }

        $unifiedName = strtolower($name);

        if (isset($this->list[$unifiedName])) {
            unset($this->list[$unifiedName]);
        }
        if (isset($this->unifiedNames[$unifiedName])) {
            unset($this->unifiedNames[$unifiedName]);
        }

        return $this;
    }

    /**
     * @return \Aero\Form\Collection
     */
    public function clear()
    {
        $this->list = array();
        $this->unifiedNames = array();
        return $this;
    }

    /**
     * Defined by ArrayAccess
     *
     * @param  string $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Defined by ArrayAccess
     *
     * @param string $name
     * @param mixed $item
     */
    public function offsetSet($name, $item)
    {
        $this->set($name, $item);
    }

    /**
     * Defined by ArrayAccess
     *
     * @param  string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Defined by ArrayAccess
     *
     * @param string $name
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }

    /**
     * Defined by Traversable
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->list);
    }

    /**
     * Defined by Countable
     *
     * @return int
     */
    public function count()
    {
        return count($this->list);
    }
}
