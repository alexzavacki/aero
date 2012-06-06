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
class GenericMultiLineHeader extends AbstractMultiHeader implements MultiLineHeader
{
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
        if (!$this->values) {
            return array();
        }

        $headers = array();

        foreach ($this->values as $value) {
            $headers[] = "{$this->headerName}: {$value}\r\n";
        }

        return $headers;
    }
}
