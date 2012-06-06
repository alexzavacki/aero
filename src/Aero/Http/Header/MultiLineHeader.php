<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 * Interface for multi line http header
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
interface MultiLineHeader extends HeaderInterface
{
    /**
     * Get array of header lines
     * @return array
     */
    public function toMultiLineArray();
}
