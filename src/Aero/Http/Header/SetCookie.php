<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 * Set-Cookie header wrapper
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
class SetCookie implements MultiLineHeader
{
    /**
     * @var array
     */
    protected $cookies = array();

    /**
     * @var bool Replace previous header
     */
    protected $replace = true;


    /**
     * @param  string $name
     * @param  string $value
     * @param  int $expire
     * @param  string $path
     * @param  string $domain
     * @param  bool $secure
     * @param  bool $httponly
     * @return \Aero\Http\Header\SetCookie
     */
    public function set(
        $name,
        $value = null,
        $expire = 0,
        $path = '/',
        $domain = null,
        $secure = false,
        $httponly = true
    ) {
        $this->cookies[] = compact($name, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    public function remove()
    {

    }

    public function clear()
    {

    }

    /**
     * Retrieve header field name
     *
     * @return string
     */
    public function getHeaderName()
    {
        return 'Set-Cookie';
    }

    /**
     * Retrieve header field values
     *
     * @return string
     */
    public function getValue()
    {
        return $this->cookies;
    }

    /**
     * Get header as well formed header line
     *
     * @return string
     */
    public function toString()
    {
        return implode('', $this->toMultiLineArray());
    }

    /**
     * Get array of header lines
     *
     * @return array
     */
    public function toMultiLineArray()
    {
        if (!$this->cookies) {
            return array();
        }

        $headers = array();

        foreach ($this->cookies as $cookie) {
            $headers[] = "Set-Cookie: Hello\r\n";
        }

        return $headers;
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
}
