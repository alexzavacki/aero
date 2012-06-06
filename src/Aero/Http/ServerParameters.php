<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http;

use Aero\Std\Parameters;

/**
 * Http SERVER parameters container
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Request
 * @author      Alex Zavacki
 */
class ServerParameters extends Parameters
{
    /**
     * Get HTTP headers from current container
     *
     * @return array
     */
    public function getHeaders()
    {
        $httpHeaders = array(
            'http_host',
            'http_user_agent',
            'http_accept',
            'http_accept_language',
            'http_accept_encoding',
            'http_accept_charset',
            'http_connection',
            'http_cookie',
            'http_cache_control',
            'content_length',
            'content_md5',
            'content_type',
        );

        $server  = (array) $this;
        $headers = array();

        $server = array_change_key_case($server, CASE_LOWER);

        foreach ($httpHeaders as $header) {
            if (isset($server[$header])) {
                $key = strpos($header, 'http_') === 0 ? substr($header, 5) : $header;
                $headers[$key] = $server[$header];
            }
        }

        return $headers;
    }

    /**
     * @param  array $parameters
     * @return array
     */
    public function getHttpHeaderKeys(array $parameters)
    {
        $keys = array_change_key_case(array_combine(
            array_keys($parameters),
            array_keys($parameters)
        ));

        return array_merge($keys, array(
            'CONTENT_LENGTH',
            'CONTENT_MD5',
            'CONTENT_TYPE',
        ));
    }
}
