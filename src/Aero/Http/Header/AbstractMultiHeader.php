<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
abstract class AbstractMultiHeader implements HeaderInterface
{
    /**
     * @var string Header field name
     */
    protected $headerName;

    /**
     * @var string Header field values
     */
    protected $values = array();

    /**
     * @var bool Replace previous header
     */
    protected $replace = true;

    /**
     * @var bool Values must be unique?
     */
    protected $unique = true;


    /**
     * Constructor
     *
     * @param string $name
     * @param string|array $values
     */
    public function __construct($name, $values = null)
    {
        $this->setHeaderName($name);

        if ($values !== null) {
            $this->setValue($values);
        }
    }

    /**
     * Set header field name
     *
     * @param  string $name
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function setHeaderName($name)
    {
        if (!$name || !is_string($name)) {
            throw new \InvalidArgumentException('Header name must be a string');
        }

        // Pre-filter to normalize valid characters, change underscore to dash
        $name = str_replace(' ', '-', ucwords(str_replace(array('_', '-'), ' ', $name)));

        // Validate what we have
        if (!preg_match('/^[a-z][a-z0-9-]*$/i', $name)) {
            throw new \InvalidArgumentException(
                'Header name must start with a letter, and consist of only letters, numbers, and dashes'
            );
        }

        $this->headerName = $name;
        return $this;
    }

    /**
     * Retrieve header field name
     *
     * @return string
     */
    public function getHeaderName()
    {
        return $this->headerName;
    }

    /**
     * Set header field value(s)
     *
     * @param  string|array $values
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function setValue($values)
    {
        $this->values = array();
        $this->addValue($values);
        return $this;
    }

    /**
     * Add header field value(s)
     *
     * @param  string|array $values
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function addValue($values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }

        $values = array_filter(array_map('strval', $values), 'strlen');

        if ($this->unique) {
            $values = array_diff($values, $this->values);
        }

        $this->values = array_merge($this->values, $values);
        return $this;
    }

    /**
     * Check if header contains specified value
     *
     * @param  string $value
     * @return bool
     */
    public function hasValue($value)
    {
        return in_array($value, $this->values);
    }

    /**
     * Remove specified value if exists
     *
     * @param  string $value
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function removeValue($value)
    {
        if (($key = array_search($value, $this->values)) !== false) {
            unset($this->values[$key]);
        }
        return $this;
    }

    /**
     * Clear all header values
     *
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function clearValues()
    {
        $this->values = array();
        return $this;
    }

    /**
     * Retrieve header field values
     *
     * @return string
     */
    public function getValue()
    {
        return $this->values;
    }

    /**
     * Set header replace option
     *
     * @param  bool $flag
     * @return \Aero\Http\Header\GenericHeader
     */
    public function setReplace($flag)
    {
        $this->replace = (bool) $flag;
        return $this;
    }

    /**
     * Should header replace a previous similar header?
     *
     * @return bool
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * Set unique values option
     *
     * @param  bool $unique
     * @return \Aero\Http\Header\AbstractMultiHeader
     */
    public function setUnique($unique)
    {
        $this->unique = (bool) $unique;
        return $this;
    }

    /**
     * Check if header must contain only unique values
     *
     * @return bool
     */
    public function unique()
    {
        return $this->unique;
    }
}
