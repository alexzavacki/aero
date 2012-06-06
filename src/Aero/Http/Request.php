<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http;

use Aero\Std\Parameters;
use Aero\Routing\MatchedRoute;

/**
 * Http request
 *
 * @todo: lazy-load for request headers (parse only when getting)
 *        optimize (denormalize) getPathInfo (use only for default environment options (e.g. nginx))
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Request
 * @author      Alex Zavacki
 */
class Request
{
    /**
     * @const string Http scheme names
     */
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * @const string METHOD constant names
     */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /**
     * @const string Version constant numbers
     */
    const VERSION_11 = '1.1';
    const VERSION_10 = '1.0';

    /**
     * @var \Aero\Std\Parameters
     */
    protected $query;

    /**
     * @var \Aero\Std\Parameters
     */
    protected $post;

    /**
     * @var \Aero\Std\Parameters
     */
    protected $cookies;

    /**
     * @var \Aero\Http\FileParameters
     */
    protected $files;

    /**
     * @var \Aero\Http\ServerParameters
     */
    protected $server;

    /**
     * @var \Aero\Http\Headers
     */
    protected $headers;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var bool Can be checked proxy headers?
     */
    protected $useProxy = false;

    /**
     * @var \Aero\Http\Session
     */
    protected $session;


    /**
     * Constructor
     *
     * Set parameters for the current request
     *
     * @param array|\Aero\Std\Parameters $query
     * @param array|\Aero\Std\Parameters $post
     * @param array|\Aero\Std\Parameters $cookies
     * @param array|\Aero\Http\FileParameters $files
     * @param array|\Aero\Http\ServerParameters $server
     * @param string $content
     */
    public function __construct(
        $query = array(),
        $post = array(),
        $cookies = array(),
        $files = array(),
        $server = array(),
        $content = ''
    ) {
        $this->query   = $query;
        $this->post    = $post;
        $this->cookies = $cookies;
        $this->files   = $files;
        $this->server  = $server;

        $this->content = (string) $content;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @static
     * @return \Aero\Http\Request
     */
    public static function createFromGlobals()
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * Request cloning
     *
     * @return void
     */
    public function __clone()
    {
        foreach (array('query', 'post', 'cookies', 'files', 'server', 'headers') as $param) {
            if (isset($this->$param) && is_object($this->$param)) {
                $this->$param = clone $this->$param;
            }
        }
    }

    /**
     * Represent the request as string
     *
     * @return string
     */
    public function toString()
    {
        $str = sprintf('%s %s %s', $this->method, $this->getRequestUri(), $this->version) . "\r\n";
        if ($this->headers) {
            $str .= $this->headers()->toString();
        }
        $str .= "\r\n";
        $str .= $this->getContent();
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
     * Get param in order: GET, POST, COOKIE
     *
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $query = $this->query();
        if ($query->has($name)) {
            return $query->get($name);
        }

        $post = $this->post();
        if ($post->has($name)) {
            return $post->get($name);
        }

        $cookies = $this->cookies();
        if ($cookies->has($name)) {
            return $cookies->get($name);
        }

        return $default;
    }

    /**
     * Set GET (QUERY_STRING) parameter container
     *
     * @param  \Aero\Std\Parameters $query
     * @return \Aero\Http\Request
     */
    public function setQuery(Parameters $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Return GET (QUERY_STRING) parameter container
     *
     * @return \Aero\Std\Parameters
     */
    public function query()
    {
        if (!$this->query instanceof Parameters) {
            $this->query = new Parameters($this->query);
        }
        return $this->query;
    }

    /**
     * Set POST parameter container
     *
     * @param  \Aero\Std\Parameters $post
     * @return \Aero\Http\Request
     */
    public function setPost(Parameters $post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * Return POST parameter container
     *
     * @return \Aero\Std\Parameters
     */
    public function post()
    {
        if (!$this->post instanceof Parameters) {
            $this->post = new Parameters($this->post);
        }
        return $this->post;
    }

    /**
     * Set COOKIE parameter container
     *
     * @param  \Aero\Std\Parameters $cookies
     * @return \Aero\Http\Request
     */
    public function setCookies(Parameters $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * Return COOKIE parameter container
     *
     * @return \Aero\Std\Parameters
     */
    public function cookies()
    {
        if (!$this->cookies instanceof Parameters) {
            $this->cookies = new Parameters($this->cookies);
        }
        return $this->cookies;
    }

    /**
     * Set FILES parameter container
     *
     * @param  \Aero\Http\FileParameters $files
     * @return \Aero\Http\Request
     */
    public function setFiles(FileParameters $files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * Return FILES parameter container
     *
     * @return \Aero\Http\FileParameters
     */
    public function files()
    {
        if (!$this->files instanceof FileParameters) {
            $this->files = new FileParameters($this->files);
        }
        return $this->files;
    }

    /**
     * Set SERVER parameter container
     *
     * @param  \Aero\Http\ServerParameters $server
     * @return \Aero\Http\Request
     */
    public function setServer(ServerParameters $server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * Return SERVER parameter container
     *
     * @return \Aero\Http\ServerParameters
     */
    public function server()
    {
        if (!$this->server instanceof ServerParameters) {
            $this->setServer(new ServerParameters($this->server));
        }
        return $this->server;
    }

    /**
     * Set HEADERS parameter container
     *
     * @param  \Aero\Http\Headers $headers
     * @return \Aero\Http\Request
     */
    public function setHeaders(Headers $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Return HEADERS parameter container
     *
     * @return \Aero\Http\Headers
     */
    public function headers()
    {
        if (!$this->headers instanceof Headers) {
            $this->headers = is_array($this->headers)
                ? new Headers($this->headers)
                : new Headers($this->server()->getHeaders());
        }
        return $this->headers;
    }

    /**
     * Set request content text
     *
     * @param  string $content
     * @return \Aero\Http\Request
     */
    public function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * Get request content text
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the method for this request
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $method
     * @return \Aero\Http\Request
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (!defined('static::METHOD_' . $method)) {
            throw new \InvalidArgumentException('Invalid HTTP method passed');
        }
        $this->method = $method;
        return $this;
    }

    /**
     * Return the method for this request
     *
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            $method = $this->server()->get('REQUEST_METHOD', 'GET');
            if ($this->method === null && $method) {
                $this->setMethod($method);
            }
        }
        return $this->method;
    }

    /**
     * Set the HTTP version for this object
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $version
     * @return \Aero\Http\Request
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
     * Return the HTTP version for this request
     *
     * @return string
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $version = $this->server()->get('SERVER_PROTOCOL', self::VERSION_11);
            if ($this->version === null && $version) {
                $version = strpos($version, '1.0') !== false ? self::VERSION_10 : self::VERSION_11;
                $this->setVersion($version);
            }
        }
        return $this->version;
    }

    /**
     * Checks whether the request is secure or not.
     *
     * @return bool
     */
    public function isSecure()
    {
        $server  = $this->server();
        $headers = $this->headers();

        $https = $server->get('HTTPS');
        if (strtolower($https) == 'on' || $https == 1) {
            return true;
        }

        if (!$this->useProxy) {
            return false;
        }

        $https = $headers->get('SSL_HTTPS');
        if (strtolower($https) == 'on' || $https == 1) {
            return true;
        }

        if ($headers->get('X-Forwarded-Proto') == 'https') {
            return true;
        }

        return false;
    }

    /**
     * Set the HTTP scheme for this object
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $scheme
     * @return \Aero\Http\Request
     */
    public function setScheme($scheme)
    {
        $scheme = strtolower($scheme);
        if (!in_array($scheme, array(self::SCHEME_HTTP, self::SCHEME_HTTPS))) {
            throw new \InvalidArgumentException('Scheme provided is not a valid HTTP scheme');
        }
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Gets the request's HTTP scheme
     *
     * @return string
     */
    public function getScheme()
    {
        if ($this->scheme === null) {
            $this->setScheme($this->isSecure() ? self::SCHEME_HTTPS : self::SCHEME_HTTP);
        }
        return $this->scheme;
    }

    /**
     * Returns the host name.
     *
     * @return string
     */
    public function getHost()
    {
        $server  = $this->server();
        $headers = $this->headers();

        if ($this->useProxy && $host = $headers->get('X-Forwarded-Host')) {
            $elements = explode(',', $host);
            $host = trim($elements[count($elements) - 1]);
        }
        else {
            if (!$host = $headers->get('Host')) {
                if (!$host = $server->get('SERVER_NAME')) {
                    $host = $server->get('SERVER_ADDR', '');
                }
            }
        }

        // Remove port number from host and return trimmed
        return trim(preg_replace('/:\d+$/', '', $host));
    }

    /**
     * Returns the HTTP host being requested
     *
     * Port will be appended if not standard for current scheme
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port   = $this->getPort();

        if (($scheme == self::SCHEME_HTTP && $port == 80)
            || ($scheme == self::SCHEME_HTTPS && $port == 443)
        ) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }

    /**
     * Set the HTTP port for this object
     *
     * @throws \InvalidArgumentException
     *
     * @param  string $port
     * @return \Aero\Http\Request
     */
    public function setPort($port)
    {
        $port = (int) $port;
        if (!$port) {
            throw new \InvalidArgumentException('Port provided is not a valid HTTP port');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * Returns the port on which the request is made
     *
     * @return string
     */
    public function getPort()
    {
        if ($this->port === null) {
            $this->setPort($this->headers()->get('X-Forwarded-Port') ?: $this->server()->get('SERVER_PORT'));
        }
        return $this->port;
    }

    /**
     * Generates a normalized URI for the Request.
     *
     * @return string
     */
    public function getUri()
    {
        $qs = $this->getQueryString();

        if ($qs !== null) {
            $qs = '?' . $qs;
        }

        return $this->getScheme() . '://' . $this->getHttpHost()
            . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string
     */
    public function getQueryString()
    {
        if (!$qs = $this->server()->get('QUERY_STRING')) {
            return null;
        }

        $parts = array();
        $order = array();

        foreach (explode('&', $qs) as $segment)
        {
            if (false === strpos($segment, '=')) {
                $parts[] = $segment;
                $order[] = $segment;
            } else {
                $tmp = explode('=', rawurldecode($segment), 2);
                $parts[] = rawurlencode($tmp[0]).'='.rawurlencode($tmp[1]);
                $order[] = $tmp[0];
            }
        }
        array_multisort($order, SORT_ASC, $parts);

        return implode('&', $parts);
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function getClientIp()
    {
        $server = $this->server();

        if ($this->useProxy) {
            if ($server->has('HTTP_CLIENT_IP')) {
                return $server->get('HTTP_CLIENT_IP');
            }
            elseif ($server->has('HTTP_X_FORWARDED_FOR')) {
                $clientIp = explode(',', $server->get('HTTP_X_FORWARDED_FOR'), 2);
                return isset($clientIp[0]) ? trim($clientIp[0]) : '';
            }
        }

        return $server->get('REMOTE_ADDR');
    }

    /**
     * Set the request URI.
     *
     * @param  string $requestUri
     * @return \Aero\Http\Request
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
        return $this;
    }

    /**
     * Get the request URI.
     *
     * @return string
     */
    public function getRequestUri()
    {
        if ($this->requestUri === null) {
            $this->requestUri = $this->detectRequestUri();
        }
        return $this->requestUri;
    }

    /**
     * Detect the base URI for the request
     *
     * Looks at a variety of criteria in order to attempt to autodetect a base
     * URI, including rewrite URIs, proxy URIs, etc.
     *
     * Method is derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd)
     * Copyright (c) Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @return string
     */
    public function detectRequestUri()
    {
        $requestUri = null;
        $server     = $this->server();

        // Check this first so IIS will catch.
        $httpXRewriteUrl = isset($server['HTTP_X_REWRITE_URL']) ? $server['HTTP_X_REWRITE_URL'] : null;
        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = isset($server['IIS_WasUrlRewritten']) ? $server['IIS_WasUrlRewritten'] : null;
        $unencodedUrl    = isset($server['UNENCODED_URL']) ? $server['UNENCODED_URL'] : '';
        if ('1' == $iisUrlRewritten && '' !== $unencodedUrl) {
            return $unencodedUrl;
        }

        // HTTP proxy requests setup request URI with scheme and host
        // [and port] + the URL path, only use URL path.
        if (!$httpXRewriteUrl) {
            $requestUri = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : null;
        }
        if ($requestUri !== null) {
            $schemeAndHttpHost = $this->getScheme() . '://' . $this->getHttpHost();

            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
            return $requestUri;
        }

        // IIS 5.0, PHP as CGI.
        $origPathInfo = isset($server['ORIG_PATH_INFO']) ? $server['ORIG_PATH_INFO'] : null;
        if ($origPathInfo !== null) {
            $queryString = isset($server['QUERY_STRING']) ? $server['QUERY_STRING'] : '';
            if ($queryString !== '') {
                $origPathInfo .= '?' . $queryString;
            }
            return $origPathInfo;
        }

        return '/';
    }

    /**
     * Set the base URL.
     *
     * @param  string $baseUrl
     * @return \Aero\Http\Request
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Get the base URL.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = rtrim($this->detectBaseUrl(), '/');
        }
        return $this->baseUrl;
    }

    /**
     * Auto-detect the base path from the request environment
     *
     * Uses a variety of criteria in order to detect the base URL of the request
     * (i.e., anything additional to the document root).
     *
     * The base URL includes the schema, host, and port, in addition to the path.
     *
     * Method is derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd)
     * Copyright (c) Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @return string
     */
    public function detectBaseUrl()
    {
        $server = $this->server();

        $filename       = $server->get('SCRIPT_FILENAME', '');
        $scriptName     = $server->get('SCRIPT_NAME');
        $phpSelf        = $server->get('PHP_SELF');
        $origScriptName = $server->get('ORIG_SCRIPT_NAME');

        if ($scriptName !== null && basename($scriptName) === $filename) {
            $baseUrl = $scriptName;
        }
        elseif ($phpSelf !== null && basename($phpSelf) === $filename) {
            $baseUrl = $phpSelf;
        }
        elseif ($origScriptName !== null && basename($origScriptName) === $filename) {
            // 1and1 shared hosting compatibility.
            $baseUrl = $origScriptName;
        }
        else {
            // Backtrack up the SCRIPT_FILENAME to find the portion
            // matching PHP_SELF.
            $path = $phpSelf ? : '';
            $segments = array_reverse(explode('/', trim($filename, '/')));
            $index = 0;
            $last = count($segments);
            $baseUrl = '';

            do {
                $segment = $segments[$index];
                $baseUrl = '/' . $segment . $baseUrl;
                $index++;
            } while ($last > $index && false !== ($pos = strpos($path, $baseUrl)) && 0 !== $pos);
        }

        // Does the base URL have anything in common with the request URI?
        $requestUri = $this->getRequestUri();

        // Full base URL matches.
        if (0 === strpos($requestUri, $baseUrl)) {
            return $baseUrl;
        }

        // Directory portion of base path matches.
        if (0 === strpos($requestUri, dirname($baseUrl))) {
            return dirname($baseUrl);
        }

        $truncatedRequestUri = $requestUri;

        if (false !== ($pos = strpos($requestUri, '?'))) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);

        // No match whatsoever
        if (empty($basename) || false === strpos($truncatedRequestUri, $basename)) {
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of the base path. $pos !== 0 makes sure it is not matching a
        // value from PATH_INFO or QUERY_STRING.
        if (strlen($requestUri) >= strlen($baseUrl)
            && (false !== ($pos = strpos($requestUri, $baseUrl)) && $pos !== 0)
        ) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return $baseUrl;
    }

    /**
     * Set the base path.
     *
     * @param  string $basePath
     * @return \Aero\Http\Request
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        return $this;
    }

    /**
     * Get the base path.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php        returns an empty string
     *  * http://localhost/index.php/page   returns an empty string
     *  * http://localhost/web/index.php    return '/web'
     *
     * @return string
     */
    public function getBasePath()
    {
        if ($this->basePath === null) {
            $this->basePath = rtrim($this->detectBasePath(), '/');
        }
        return $this->basePath;
    }

    /**
     * Autodetect the base path of the request
     *
     * Uses several crtieria to determine the base path of the request.
     *
     * Method is derived from code of the Zend Framework
     * Code subject to the new BSD license (http://framework.zend.com/license/new-bsd)
     * Copyright (c) Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @return string
     */
    public function detectBasePath()
    {
        $filename = basename($this->server()->get('SCRIPT_FILENAME', ''));
        $baseUrl  = $this->getBaseUrl();

        // Empty base url detected
        if ($baseUrl === '') {
            return '';
        }

        // basename() matches the script filename; return the directory
        if (basename($baseUrl) === $filename) {
            return dirname($baseUrl);
        }

        // Base path is identical to base URL
        return $baseUrl;
    }

    /**
     * Set the path info
     *
     * @param  string $pathInfo
     * @return \Aero\Http\Request
     */
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;
        return $this;
    }

    /**
     * Get the path info
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string
     */
    public function getPathInfo()
    {
        if ($this->pathInfo === null) {
            $this->pathInfo = preg_replace('#/{2,}#', '/', $this->detectPathInfo());
            if (($this->pathInfo = rtrim($this->pathInfo, '/')) == '') {
                $this->pathInfo = '/';
            }
        }
        return $this->pathInfo;
    }

    /**
     * Get the path info.
     *
     * Method is derived from code of the Symfony 2
     * Code subject to the MIT license
     *
     * @return string
     */
    public function detectPathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)))))) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        }
        elseif (null === $baseUrl) {
            return $requestUri;
        }

        return (string) $pathInfo;
    }

    /**
     * Get ot set using proxy headers option
     *
     * @param  bool|null $use
     * @return \Aero\Http\Request|bool
     */
    public function useProxy($use = null)
    {
        if (is_bool($use)) {
            $this->useProxy = $use;
            return $this;
        }
        return $this->useProxy;
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return $this->headers()->getHeader('X-Requested-With') == 'XMLHttpRequest';
    }

    /**
     * Alias of isXmlHttpRequest()
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Is this an OPTIONS method request?
     *
     * @return bool
     */
    public function isOptions()
    {
        return ($this->getMethod() === self::METHOD_OPTIONS);
    }

    /**
     * Is this a GET method request?
     *
     * @return bool
     */
    public function isGet()
    {
        return ($this->getMethod() === self::METHOD_GET);
    }

    /**
     * Is this a HEAD method request?
     *
     * @return bool
     */
    public function isHead()
    {
        return ($this->getMethod() === self::METHOD_HEAD);
    }

    /**
     * Is this a POST method request?
     *
     * @return bool
     */
    public function isPost()
    {
        return ($this->getMethod() === self::METHOD_POST);
    }

    /**
     * Is this a PUT method request?
     *
     * @return bool
     */
    public function isPut()
    {
        return ($this->getMethod() === self::METHOD_PUT);
    }

    /**
     * Is this a DELETE method request?
     *
     * @return bool
     */
    public function isDelete()
    {
        return ($this->getMethod() === self::METHOD_DELETE);
    }

    /**
     * Is this a TRACE method request?
     *
     * @return bool
     */
    public function isTrace()
    {
        return ($this->getMethod() === self::METHOD_TRACE);
    }

    /**
     * Is this a CONNECT method request?
     *
     * @return bool
     */
    public function isConnect()
    {
        return ($this->getMethod() === self::METHOD_CONNECT);
    }
}
