<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http;

use Aero\Std\Plugin\PluginMap;

/**
 * Http headers container
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
class Headers implements \Iterator, \Countable
{
    /**
     * @const string Header types
     */
    const SINGLE_VALUE = 'singlevalue';
    const MULTI_VALUE  = 'multivalue';
    const MULTI_LINE   = 'multiline';

    /**
     * @var array List of registered headers
     */
    protected $headers = array();

    /**
     * @var \Aero\Std\Plugin\PluginMap
     */
    protected $headerPluginMap;


    /**
     * Constructor.
     *
     * @param array $headers
     */
    public function __construct($headers = array())
    {
        if (is_array($headers)) {
            $this->set($headers);
        }
    }

    /**
     * Set header or array of headers
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $nameOrArray
     * @param  string $value
     * @return \Aero\Http\Headers
     */
    public function set($nameOrArray, $value = null)
    {
        if (is_string($nameOrArray)) {
            $nameOrArray = $value !== null ? array($nameOrArray => $value) : array($nameOrArray);
        }
        elseif (!is_array($nameOrArray)) {
            throw new \InvalidArgumentException('Name must be a string or an array of headers');
        }

        foreach ($nameOrArray as $headerName => $header)
        {
            if ($header instanceof Header\HeaderInterface) {
                $this->addHeader($header);
                continue;
            }

            if (ctype_digit((string) $headerName))
            {
                if (is_string($header)) {
                    // create from string...
                    throw new \InvalidArgumentException('String header not implemented yet');
                }
                elseif (is_array($header)) {
                    $header = $this->formatHeaderArray($header);
                    $headerName = $header['name'];
                }
            }
            else {
                $header = array(
                    'name'  => $headerName,
                    'value' => $header,
                );
            }

            if (!is_array($header)) {
                throw new \InvalidArgumentException('Specified header not supported');
            }

            $key = str_replace(array('-', '_', ' ', '.'), '', strtolower($headerName));
            $this->headers[$key] = $header;
        }

        return $this;
    }

    /**
     * Analyze and standardize header array data
     *
     * @throws \InvalidArgumentException
     *
     * @param  array $array
     * @return array
     */
    public function formatHeaderArray(array $array)
    {
        if (!$array) {
            throw new \InvalidArgumentException('Malformed header. Empty array passed');
        }

        if (!isset($array['name']))
        {
            $header = array();
            foreach (array('name', 'value', 'type') as $field) {
                if ($array) {
                    $header[$field] = array_shift($array);
                }
            }
            $array = $header;
        }

        if (!isset($array['name']) || !is_string($array['name'])) {
            throw new \InvalidArgumentException('Malformed header. Name must be a string');
        }

        if (!isset($array['value'])) {
            $array['value'] = null;
        }

        return $array;
    }

    /**
     * Add header as multi value
     *
     * @param  string $name
     * @param  array $values
     * @return \Aero\Http\Headers
     */
    public function setMultiValue($name, $values = array())
    {
        $this->set(array(
            array(
                'name'  => $name,
                'value' => $values,
                'type'  => self::MULTI_VALUE
            )
        ));

        return $this;
    }

    /**
     * Add header as multi line
     *
     * @param  string $name
     * @param  array $values
     * @return \Aero\Http\Headers
     */
    public function setMultiLine($name, $values = array())
    {
        $this->set(array(
            array(
                'name'  => $name,
                'value' => $values,
                'type'  => self::MULTI_LINE
            )
        ));

        return $this;
    }

    /**
     * Add custom header
     *
     * @param  \Aero\Http\Header\HeaderInterface $header
     * @param  bool $overwrite
     *
     * @return \Aero\Http\Headers
     */
    public function addHeader(Header\HeaderInterface $header, $overwrite = true)
    {
        $key = str_replace(array('-', '_', ' ', '.'), '', strtolower($header->getHeaderName()));

        if (!array_key_exists($key, $this->headers) || $overwrite) {
            $this->headers[$key] = $header;
        }

        return $this;
    }

    /**
     * Create header object from array data
     *
     * @param  array $array
     * @return \Aero\Http\Header\HeaderInterface
     */
    public function createHeaderFromArray($array)
    {
        $key = str_replace(array('-', '_', ' ', '.'), '', strtolower($array['name']));

        if ($class = $this->getHeaderPluginMap()->get($key)) {
            return new $class();
        }

        $genericClasses = array(
            self::SINGLE_VALUE => __NAMESPACE__ . '\\Header\\GenericHeader',
            self::MULTI_VALUE  => __NAMESPACE__ . '\\Header\\GenericMultiValueHeader',
            self::MULTI_LINE   => __NAMESPACE__ . '\\Header\\GenericMultiLineHeader',
        );

        $type = isset($array['type']) ? $array['type'] : self::SINGLE_VALUE;

        if (isset($genericClasses[$type])) {
            if (is_array($array['value']) && $type == self::SINGLE_VALUE) {
                $type = self::MULTI_VALUE;
            }
        }
        else {
            $type = is_array($array['value']) ? self::MULTI_VALUE : self::SINGLE_VALUE;
        }

        return new $genericClasses[$type]($array['name'], $array['value']);
    }

    /**
     * Get header as instance of HeaderInterface
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $name
     * @param  bool $create
     * @return \Aero\Http\Header\HeaderInterface
     */
    public function getHeader($name, $create = true)
    {
        $key = str_replace(array('-', '_', ' ', '.'), '', strtolower($name));

        if (!isset($this->headers[$key])) {
            if ($create === false) {
                throw new \InvalidArgumentException("Header '{$name}' doesn't exist");
            }
            return $this->headers[$key] = $this->createHeaderFromArray(array(
                'name' => $name,
                'value' => !is_bool($create) ? $create : null,
            ));
        }

        if (is_array($this->headers[$key])) {
            $this->headers[$key] = $this->createHeaderFromArray($this->headers[$key]);
        }

        if (!$this->headers[$key] instanceof Header\HeaderInterface) {
            throw new \InvalidArgumentException('Invalid header data');
        }

        return $this->headers[$key];
    }

    public function getMultiValue($name)
    {

    }

    public function getMultiLine($name)
    {

    }

    /**
     * Get header's value(s)
     *
     * @param  string $name
     * @param  mixed $default
     *
     * @return string|array
     */
    public function get($name, $default = null)
    {
        $key = str_replace(array('-', '_', ' ', '.'), '', strtolower($name));

        if (!isset($this->headers[$key])) {
            return $default;
        }

        if (is_array($this->headers[$key])) {
            return array_key_exists('value', $this->headers[$key])
                ? $this->headers[$key]['value']
                : $default;
        }

        if ($this->headers[$key] instanceof Header\HeaderInterface) {
            return $this->headers[$key]->getValue();
        }

        return $default;
    }

    /**
     * Check if header exists
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        $name = str_replace(array('-', '_', ' ', '.'), '', strtolower($name));
        return isset($this->headers[$name]);
    }

    /**
     * Remove header by it's name
     *
     * @param  string $name
     * @return \Aero\Http\Headers
     */
    public function remove($name)
    {
        $name = str_replace(array('-', '_', ' ', '.'), '', strtolower($name));

        if (array_key_exists($name, $this->headers)) {
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * Remove a header from the container
     *
     * @param  \Aero\Http\Header\HeaderInterface $header
     * @return void
     */
    public function removeHeader(Header\HeaderInterface $header)
    {
        if (($key = array_search($header, $this->headers, true)) !== false) {
            unset($this->headers[$key]);
        }
    }

    /**
     * Clear all headers
     *
     * @return \Aero\Http\Headers
     */
    public function clear()
    {
        $this->headers = array();
        return $this;
    }

    /**
     * Get all headers as array of lines
     *
     * @return array
     */
    public function toArray()
    {
        $headers = array();

        foreach ($this as $header) {
            if ($header instanceof Header\MultiLineHeader) {
                /** @var $header \Aero\Http\Header\MultiLineHeader */
                $headers = array_merge($headers, $header->toMultiLineArray());
            }
            else {
                $headers[] = $header->toString();
            }
        }

        return $headers;
    }

    /**
     * Get headers as string
     *
     * @return string
     */
    public function toString()
    {
        return implode('', $this->toArray());
    }

    /**
     * Get headers as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set headers PluginMap
     *
     * @param  \Aero\Std\Plugin\PluginMap $headerPluginMap
     * @return \Aero\Http\Headers
     */
    public function setHeaderPluginMap(PluginMap $headerPluginMap)
    {
        $this->headerPluginMap = $headerPluginMap;
        return $this;
    }

    /**
     * Get headers PluginLoader
     *
     * @return \Aero\Std\Plugin\PluginMap
     */
    public function getHeaderPluginMap()
    {
        if ($this->headerPluginMap === null) {
            $this->headerPluginMap = new PluginMap(array(
                'setcookie' => __NAMESPACE__ .  '\\Header\\SetCookie',
            ));
        }
        return $this->headerPluginMap;
    }

    /**
     * Advance the pointer for this object as an interator
     *
     * @return void
     */
    public function next()
    {
        next($this->headers);
    }

    /**
     * Return the current key for this object as an interator
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->headers);
    }

    /**
     * Is this iterator still valid?
     *
     * @return bool
     */
    public function valid()
    {
        return current($this->headers) !== false;
    }

    /**
     * Reset the internal pointer for this object as an iterator
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->headers);
    }

    /**
     * Return the current value for this iterator, lazy loading it if need be
     *
     * @return \Aero\Http\Header\HeaderInterface
     */
    public function current()
    {
        $current = current($this->headers);

        if (is_array($current)) {
            $key = key($this->headers);
            $current = $this->headers[$key] = $this->createHeaderFromArray($current);
        }

        return $current;
    }

    /**
     * Return the number of headers in this contain.
     *
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }
}
