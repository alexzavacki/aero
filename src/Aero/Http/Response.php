<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http;

/**
 * Http response
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Response
 * @author      Alex Zavacki
 */
class Response
{
    /**
     * @const string Version constant numbers
     */
    const VERSION_11 = '1.1';
    const VERSION_10 = '1.0';

    /**
     * @const int
     */
    const DEFAULT_STATUS_CODE = 200;

    /**
     * @var string
     */
    protected $version = self::VERSION_11;

    /**
     * @var int
     */
    protected $statusCode = self::DEFAULT_STATUS_CODE;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * @var \Aero\Http\ResponseHeaders
     */
    protected $headers;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * Status codes translation table.
     *
     * @var array
     */
    static public $statusTexts = array(
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );


    /**
     * Constructor.
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = self::DEFAULT_STATUS_CODE, $headers = array())
    {
        $this->content = (string) $content;

        if (is_array($status)) {
            $tmpHeaders = $headers;
            $headers = $status;
            $status = is_int($tmpHeaders) ? $tmpHeaders : self::DEFAULT_STATUS_CODE;
        }

        $this->statusCode = (int) $status;
        $this->headers = $headers;
    }

    /**
     * Represent the response as string
     *
     * @return string
     */
    public function toString()
    {
        $str = $this->renderResponseLine() . "\r\n";
        $str .= $this->headers()->toString();
        $str .= "\r\n";
        $str .= (string) $this->content;
        return $str;
    }

    /**
     * Allow PHP casting of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Render the response line string
     *
     * @return string
     */
    public function renderResponseLine()
    {
        return sprintf('HTTP/%s %d %s', $this->version, $this->statusCode, $this->getActualStatusText());
    }

    /**
     * Send HTTP headers
     *
     * @return \Aero\Http\Response
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        // http response line
        header($this->renderResponseLine());

        // headers
        if ($this->headers)
        {
            foreach ($this->headers() as $header)
            {
                if ($header instanceof Header\MultiLineHeader) {
                    $replace = $header->getReplace();
                    /** @var $header \Aero\Http\Header\MultiLineHeader */
                    foreach ($header->toMultiLineArray() as $line) {
                        header($line, $replace);
                        if ($replace === true) {
                            $replace = false;
                        }
                    }
                }
                else {
                    header($header->toString(), $header->getReplace());
                }
            }
        }

        return $this;
    }

    /**
     * Send content
     *
     * @return \Aero\Http\Response
     */
    public function sendContent()
    {
        echo $this->content;
        return $this;
    }

    /**
     * Send response
     *
     * @return \Aero\Http\Response
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        return $this;
    }

    /**
     * Set HEADERS parameter container
     *
     * @param  \Aero\Http\ResponseHeaders $headers
     * @return \Aero\Http\Response
     */
    public function setHeaders(ResponseHeaders $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Return HEADERS parameter container
     *
     * @return \Aero\Http\ResponseHeaders
     */
    public function headers()
    {
        if (!$this->headers instanceof ResponseHeaders) {
            $this->headers = $this->headers
                ? new ResponseHeaders($this->headers)
                : new ResponseHeaders();
        }
        return $this->headers;
    }

    /**
     * Get SetCookie headers object
     *
     * @return mixed
     */
    public function cookies()
    {
        return $this->headers()->cookies();
    }

    /**
     * @param  int $code
     * @param  string $text
     * @return \Aero\Http\Response
     */
    public function setStatus($code, $text = null)
    {
        $this->statusCode = (int) $code;
        $this->setStatusText($text);
        return $this;
    }

    /**
     * @param  int $statusCode
     * @return \Aero\Http\Response
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param  string $statusText
     * @return \Aero\Http\Response
     */
    public function setStatusText($statusText)
    {
        $this->statusText = $statusText !== null ? (string) $statusText : null;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * Get actual response status text
     *
     * @return string
     */
    public function getActualStatusText()
    {
        if (is_string($this->statusText)) {
            return $this->statusText;
        }
        if (isset(self::$statusTexts[$this->statusCode])) {
            return self::$statusTexts[$this->statusCode];
        }
        return '';
    }

    /**
     * Set the HTTP version for this object
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $version
     * @return \Aero\Http\Response
     */
    public function setVersion($version)
    {
        if (!in_array($version, array(self::VERSION_10, self::VERSION_11))) {
            throw new \InvalidArgumentException('Version provided is not a valid HTTP version');
        }
        $this->version = $version;
        return $this;
    }

    /**
     * Return the HTTP version for current response
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param  string $content
     * @return \Aero\Http\Response
     */
    public function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Does the status code indicate a client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return ($this->statusCode < 500 && $this->statusCode >= 400);
    }

    /**
     * Is the request forbidden due to ACLs?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return (403 == $this->statusCode);
    }

    /**
     * Is the current status "informational"?
     *
     * @return bool
     */
    public function isInformational()
    {
        return ($this->statusCode >= 100 && $this->statusCode < 200);
    }

    /**
     * Does the status code indicate the resource is not found?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return (404 === $this->statusCode);
    }

    /**
     * Do we have a normal, OK response?
     *
     * @return bool
     */
    public function isOk()
    {
        return (200 === $this->statusCode);
    }

    /**
     * Does the status code reflect a server error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return (500 <= $this->statusCode && 600 > $this->statusCode);
    }

    /**
     * Do we have a redirect?
     *
     * @return bool
     */
    public function isRedirect()
    {
        return (300 <= $this->statusCode && 400 > $this->statusCode);
    }

    /**
     * Was the response successful?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return (200 <= $this->statusCode && 300 > $this->statusCode);
    }
}
