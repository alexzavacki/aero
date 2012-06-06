<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Http\Routing;

use Aero\Std\Parameters;
use Aero\Routing\AbstractRoute;

/**
 * Http route
 *
 * @category    Aero
 * @package     Aero_Http
 * @subpackage  Aero_Http_Routing
 * @author      Alex Zavacki
 */
class PatternRoute extends AbstractRoute
{
    /**
     * @var string Route pattern
     */
    protected $pattern;

    /**
     * @var array List of query string requirements
     */
    protected $query = array();

    /**
     * @var array Compiled route data
     */
    protected $compiled;


    /**
     * Constructor
     *
     * @param string                     $pattern
     * @param array|\Aero\Std\Parameters $defaults
     * @param array|\Aero\Std\Parameters $requirements
     * @param array|\Aero\Std\Parameters $query
     * @param string|array               $namespaces
     *
     * @return \Aero\Http\Routing\PatternRoute
     */
    public function __construct(
        $pattern,
        $defaults = array(),
        $requirements = array(),
        $query = array(),
        $namespaces = array()
    ) {
        static::getValidatedFormattedArrayData(compact(
            'pattern', 'defaults', 'requirements', 'query', 'namespaces'
        ));

        parent::__construct($defaults, $requirements, $namespaces);

        $this->setPattern($pattern);

        if ($query) {
            $this->query = $query;
        }
    }

    /**
     * Match a given request
     *
     * @param  \Aero\Http\Request $request
     * @return array|false
     */
    public function match($request)
    {
        if ($this->compiled === null) {
            $this->compiled = $this->compile();
        }
        if (!is_array($this->compiled) || count($this->compiled) != 4) {
            throw new \InvalidArgumentException("Invalid route compiled data");
        }

        $pathinfo = urldecode($request->getPathInfo());

        if (!preg_match($this->compiled[1], $pathinfo, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (is_numeric($key) || is_int($key)) {
                unset($matches[$key]);
            }
            else {
                $matches[$key] = urldecode($matches[$key]);
            }
        }

        return array_merge($this->defaults()->all(), $matches);
    }

    /**
     * Assemble the route
     *
     * @param  array $params
     * @param  bool $absolute
     * @return string
     */
    public function assemble($params = array(), $absolute = false)
    {
        if ($this->compiled === null) {
            $this->compiled = $this->compile();
        }
        if (!is_array($this->compiled) || count($this->compiled) != 4) {
            throw new \InvalidArgumentException("Invalid route compiled data");
        }

        return '';
    }

    /**
     * Create new Route object from array data
     *
     * @throws \InvalidArgumentException
     *
     * @static
     * @param  array $route
     * @return \Aero\Http\Routing\PatternRoute
     */
    public static function createFromArray(array $route)
    {
        return new static(
            $route['pattern'],
            isset($route['defaults']) ? $route['defaults'] : array(),
            isset($route['requirements']) ? $route['requirements'] : array(),
            isset($route['query']) ? $route['query'] : array(),
            isset($route['namespaces']) ? $route['namespaces'] : array()
        );
    }

    /**
     * Validate and return formatted route array data
     *
     * @static
     * @param  array $data
     * @return array
     */
    public static function getValidatedFormattedArrayData(array $data)
    {
        $data = parent::getValidatedFormattedArrayData($data);

        if (!isset($data['pattern']) || !is_string($data['pattern']) || trim($data['pattern']) == '') {
            throw new \InvalidArgumentException('Route pattern must be non-empty string');
        }

        if (!isset($data['namespaces'])) {
            $data['namespaces'] = array();
        }
        if (isset($data['namespace'])) {
            $data['namespaces'] = array_merge((array) $data['namespaces'], (array) $data['namespace']);
        }

        return $data;
    }

    /**
     * Generate route name depends on route data
     *
     * @static
     * @param  mixed $route
     * @return string
     */
    public static function generateName($route)
    {
        if (is_array($route) && isset($route['pattern'])) {
            $pattern = $route['pattern'];
        }
        elseif ($route instanceof self) {
            /** @var $route \Aero\Http\Routing\PatternRoute */
            $pattern = $route->getPattern();
        }
        else {
            return null;
        }

        $pattern = str_replace(array(':', '|', '-', '{', '}'), '_', $pattern);
        $pattern = preg_replace('#[_]{2,}#', '_', str_replace(array('/_', '_/'), '/', $pattern));
        $pattern = preg_replace('#[^a-z0-9A-Z_./]+#', '', $pattern);

        return trim($pattern, ' /_');
    }

    /**
     * Compile route data
     *
     * Method is derived from code of the Symfony 2
     * Code subject to the MIT license
     *
     * @return array
     */
    public function compile()
    {
        $pos = 0;
        $len = strlen($this->pattern);

        $tokens    = array();
        $variables = array();

        $defaults     = $this->defaults();
        $requirements = $this->requirements();

        preg_match_all(
            '#.\{([\w\d_]+)\}#',
            $this->pattern,
            $matchedVariables,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );

        foreach ($matchedVariables as $match)
        {
            if ($text = substr($this->pattern, $pos, $match[0][1] - $pos)) {
                $tokens[] = array('text', $text);
            }
            $seps = array($this->pattern[$pos]);
            $pos = $match[0][1] + strlen($match[0][0]);
            $var = $match[1][0];

            if ($req = $requirements->get($var)) {
                $regexp = $req;
            }
            else {
                if ($pos !== $len) {
                    $seps[] = $this->pattern[$pos];
                }
                $regexp = sprintf('[^%s]+?', preg_quote(implode('', array_unique($seps)), '#'));
            }

            $tokens[] = array('variable', $match[0][0][0], $regexp, $var);
            $variables[] = $var;
        }

        if ($pos < $len) {
            $tokens[] = array('text', substr($this->pattern, $pos));
        }

        // find the first optional token
        $firstOptional = INF;
        for ($i = count($tokens) - 1; $i >= 0; $i--)
        {
            if ('variable' === $tokens[$i][0] && $defaults->get($tokens[$i][3])) {
                $firstOptional = $i;
            }
            else {
                break;
            }
        }

        // compute the matching regexp
        $regex = '';
        $indent = 1;
        if (1 === count($tokens) && 0 === $firstOptional) {
            $token = $tokens[0];
            ++$indent;
            $regex .= str_repeat(' ', $indent * 4).sprintf("%s(?:\n", preg_quote($token[1], '#'));
            $regex .= str_repeat(' ', $indent * 4).sprintf("(?P<%s>%s)\n", $token[3], $token[2]);
        }
        else {
            foreach ($tokens as $i => $token) {
                if ('text' === $token[0]) {
                    $regex .= str_repeat(' ', $indent * 4).preg_quote($token[1], '#')."\n";
                } else {
                    if ($i >= $firstOptional) {
                        $regex .= str_repeat(' ', $indent * 4)."(?:\n";
                        ++$indent;
                    }
                    $regex .= str_repeat(' ', $indent * 4).sprintf("%s(?P<%s>%s)\n", preg_quote($token[1], '#'), $token[3], $token[2]);
                }
            }
        }
        while (--$indent) {
            $regex .= str_repeat(' ', $indent * 4).")?\n";
        }

        return array(
            'text' === $tokens[0][0] ? $tokens[0][1] : '',
            sprintf("#^\n%s$#xs", $regex),
            array_reverse($tokens),
            $variables
        );
    }

    /**
     * @param  string $pattern
     * @return \Aero\Http\Routing\PatternRoute
     */
    public function setPattern($pattern)
    {
        $this->pattern = static::formatPattern($pattern);
        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Format route pattern
     *
     * @static
     * @param  string $pattern
     * @return string
     */
    public static function formatPattern($pattern)
    {
        return '/' . trim(preg_replace('#[/\\\\]+#', '/', $pattern), ' /');
    }

    /**
     * Get query string requirements
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
     * Set query string requirements
     *
     * @param  array|\Aero\Std\Parameters $query
     * @return \Aero\Http\Routing\PatternRoute
     */
    public function setQuery($query)
    {
        if (is_array($query)) {
            $query = new Parameters($query);
        }
        elseif (!$query instanceof Parameters) {
            throw new \InvalidArgumentException('Query string requirements must be an array or Parameters object');
        }

        $this->query = $query;

        return $this;
    }
}
