<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http;

/**
 * Response http headers container
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Header
 * @author      Alex Zavacki
 */
class ResponseHeaders extends Headers
{
    /**
     * Get SetCookie headers object
     *
     * @return mixed
     */
    public function cookies()
    {
        return $this->getHeader('Set-Cookie');
    }
}
