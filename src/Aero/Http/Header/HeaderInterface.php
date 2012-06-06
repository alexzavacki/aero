<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Header;

/**
 * Interface for HTTP header
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
interface HeaderInterface
{
    /**
     * Retrieve header field name
     * @return string
     */
    public function getHeaderName();

    /**
     * Retrieve header field value
     * @return string
     */
    public function getValue();

    /**
     * Should header replace a previous similar header?
     * @return bool
     */
    public function getReplace();

    /**
     * Get header as well formed header line
     * @return string
     */
    public function toString();
}
