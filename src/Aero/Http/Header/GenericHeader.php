<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 * Generic HTTP header
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
class GenericHeader implements HeaderInterface
{
    /**
     * @var string Header field name
     */
    protected $headerName;

    /**
     * @var string Header field value
     */
    protected $value = '';

    /**
     * @var bool Replace previous header
     */
    protected $replace = true;


    /**
     * Constructor
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value = null)
    {
        $this->setHeaderName($name);

        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * Factory to generate a header object from a string
     *
     * @static
     * @param  string $headerLine
     * @return \Aero\Http\Header\GenericHeader
     */
    public static function fromString($headerLine)
    {
        list($name, $value) = explode(': ', $headerLine, 2);
        return new static($name, $value);
    }

    /**
     * Set header field name
     *
     * @param  string $name
     * @return \Aero\Http\Header\GenericHeader
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
     * Set header field value
     *
     * @param  string $value
     * @return \Aero\Http\Header\GenericHeader
     */
    public function setValue($value)
    {
        $this->value = trim($value);
        return $this;
    }

    /**
     * Retrieve header field value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * Get header as well formed header line
     *
     * @return string
     */
    public function toString()
    {
        if ($this->value == '') {
            return '';
        }
        return $this->headerName . ': ' . $this->value . "\r\n";
    }
}
