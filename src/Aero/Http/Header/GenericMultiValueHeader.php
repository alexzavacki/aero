<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 * Generic multi value header
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
class GenericMultiValueHeader extends AbstractMultiHeader implements MultiValueHeader
{
    /**
     * @var string Glue for values implode
     */
    protected $glue = ', ';


    /**
     * Constructor
     *
     * @param string $name
     * @param string|array $values
     * @param string $glue
     */
    public function __construct($name, $values = null, $glue = null)
    {
        if (is_string($glue)) {
            $this->glue = $glue;
        }

        parent::__construct($name, $values);
    }

    /**
     * Get header as well formed header line
     *
     * @return string
     */
    public function toString()
    {
        if (!$this->values) {
            return '';
        }
        return $this->headerName . ': ' . implode($this->glue, $this->values) . "\r\n";
    }

    /**
     * Set glue string
     *
     * @param  string $glue
     * @return \Aero\Http\Header\GenericMultiValueHeader
     */
    public function setGlue($glue)
    {
        $this->glue = (string) $glue;
        return $this;
    }

    /**
     * Get glue string
     *
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }
}
