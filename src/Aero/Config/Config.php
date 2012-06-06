<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Config;

/**
 * Config container
 *
 * @category    Aero
 * @package     Aero_Config
 * @author      Alex Zavacki
 */
class Config
{
    /**
     * @var array Config data container
     */
    protected $data = array();

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var string
     */
    protected $keySeparator;


    /**
     * Constructor.
     *
     * @param array  $array
     * @param bool   $readOnly
     * @param string $keySeparator
     */
    public function __construct(array $array = array(), $readOnly = false, $keySeparator = null)
    {
        $this->readOnly     = (bool) $readOnly;
        $this->keySeparator = $keySeparator;
        $this->setArray($array);
    }

    /**
     * Config cloning
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $this->data[$key] = clone $value;
            }
        }
    }

    /**
     * Return config data as php array
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();

        foreach ($this->data as $key => $value) {
            $array[$key] = ($value instanceof self) ? $value->toArray() : $value;
        }

        return $array;
    }

    /**
     * Merge config with this one
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @param  \Aero\Config\Config|array $merge
     * @return \Aero\Config\Config
     */
    public function merge($merge)
    {
        if ($this->readOnly) {
            throw new \LogicException('Config is read only');
        }

        if ($merge instanceof self) {
            $merge = $merge->toArray();
        }
        elseif (is_array($merge)) {
            // Convert nested Config objects to plain arrays...
            /*array_walk_recursive($merge, function(&$value, $key, $configClass) {
                if ($value instanceof $configClass) {
                    $value = $value->toArray();
                }
            }, __CLASS__);*/
        }
        else {
            throw new \InvalidArgumentException('Merged config must be a Config object or an array');
        }

        $this->setArray(array_replace_recursive($this->toArray(), $merge));
        return $this;
    }

    /**
     * Get config param by name
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $config = $this;
        $parts = is_string($this->keySeparator) ? explode($this->keySeparator, $name) : array($name);

        do {
            $item = array_shift($parts);
            if (!$config->has($item)) {
                return $default;
            }
            $config = $config->$item;
        }
        while ($parts && $config instanceof self);

        return !$parts ? $config : $default;
    }

    /**
     * Get config param by name
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        return $this->data[$name];
    }

    /**
     * Set config param
     *
     * @throws \LogicException
     *
     * @param  string $name
     * @param  mixed $value
     *
     * @return \Aero\Config\Config
     */
    public function set($name, $value)
    {
        if ($this->readOnly) {
            throw new \LogicException('Config is read only');
        }

        if ($this->keySeparator !== null && strpos($name, $this->keySeparator) !== false)
        {
            $parts = explode($this->keySeparator, $name);
            $name = array_shift($parts);
            do {
                $key = array_pop($parts);
                $value = array($key => $value);
            }
            while ($parts);
        }

        $this->data[$name] = is_array($value)
            ? new self($value, $this->readOnly, $this->keySeparator)
            : $value;

        return $this;
    }

    /**
     * Set config param
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Set config values from array
     *
     * @param  array $values
     * @return \Aero\Config\Config
     */
    public function setArray(array $values)
    {
        if ($values) {
            foreach (array_filter($values, 'is_array') as $key => $value) {
                $values[$key] = new self($value, $this->readOnly, $this->keySeparator);
            }
        }
        $this->data = $values;

        return $this;
    }

    /**
     * Does config have the param?
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        if ($this->keySeparator === null || strpos($name, $this->keySeparator) === false) {
            return array_key_exists($name, $this->data);
        }

        $config = $this;
        $parts = explode($this->keySeparator, $name);

        do {
            $item = array_shift($parts);
            if (!$config->has($item)) {
                return false;
            }
            $config = $config->$item;
        }
        while ($parts && $config instanceof self);

        return !$parts;
    }

    /**
     * Does config have the param?
     *
     * @param  string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Remove config param if exists
     *
     * @throws \LogicException If Config is read only
     *
     * @param  string $name
     * @return \Aero\Config\Config
     */
    public function remove($name)
    {
        if ($this->readOnly) {
            throw new \LogicException('Config is read only');
        }

        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }

        return $this;
    }

    /**
     * Remove config param if exists
     *
     * @throws \LogicException If Config is read only
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        $this->remove($name);
    }

    /**
     * Mark config as read only
     *
     * @param  bool $readOnly
     * @return \Aero\Config\Config
     */
    public function setReadOnly($readOnly = true)
    {
        $this->readOnly = (bool) $readOnly;

        foreach ($this->data as $value) {
            if ($value instanceof self) {
                /** @var $value \Aero\Config\Config */
                $value->setReadOnly($this->readOnly);
            }
        }

        return $this;
    }

    /**
     * Check if config marked as read only
     *
     * @return boolean
     */
    public function readOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param  string $keySeparator
     * @return \Aero\Config\Config
     */
    public function setKeySeparator($keySeparator)
    {
        $this->keySeparator = $keySeparator;

        foreach ($this->data as $value) {
            if ($value instanceof self) {
                /** @var $value \Aero\Config\Config */
                $value->setKeySeparator($this->keySeparator);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getKeySeparator()
    {
        return $this->keySeparator;
    }
}
