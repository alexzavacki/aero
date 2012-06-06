<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Routing;

use Aero\Std\Parameters;

/**
 * Abstract route
 *
 * @category    Aero
 * @package     Aero_Routing
 * @author      Alex Zavacki
 */
abstract class AbstractRoute implements RouteInterface
{
    /**
     * @var \Aero\Std\Parameters|array Default params
     */
    protected $defaults = array();

    /**
     * @var \Aero\Std\Parameters|array Params requirements
     */
    protected $requirements = array();

    /**
     * @var array List of namespaces to which route belongs
     */
    protected $namespaces = array();


    /**
     * Constructor
     *
     * @param array|\Aero\Std\Parameters $defaults
     * @param array|\Aero\Std\Parameters $requirements
     * @param string|array               $namespaces
     */
    public function __construct($defaults = array(), $requirements = array(), $namespaces = array())
    {
        if ($defaults) {
            $this->defaults = $defaults;
        }
        if ($requirements) {
            $this->requirements = $requirements;
        }
        if ($namespaces) {
            $this->setNamespace($namespaces);
        }
    }

    /**
     * Create route object from specified data
     *
     * @static
     * @param  mixed $data
     * @return \Aero\Routing\AbstractRoute
     * @throws \InvalidArgumentException
     */
    public static function create($data)
    {
        if (is_array($data)) {
            return static::createFromArray($data);
        }
        throw new \InvalidArgumentException("Can't create route object from " . gettype($data));
    }

    /**
     * Create new Route object from array data
     *
     * @static
     * @param  array $route
     * @return \Aero\Routing\AbstractRoute
     */
    public static function createFromArray(array $route)
    {
        // No implementation in abstract route
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
        if (!isset($data['type'])) {
            $data['type'] = get_called_class();
        }
        return $data;
    }

    /**
     * Return the defaults
     *
     * @return \Aero\Std\Parameters
     */
    public function defaults()
    {
        if (!$this->defaults instanceof Parameters) {
            $this->defaults = new Parameters($this->defaults);
        }
        return $this->defaults;
    }

    /**
     * Set the defaults
     *
     * @param  array|\Aero\Std\Parameters $defaults
     * @return \Aero\Routing\AbstractRoute
     */
    public function setDefaults($defaults)
    {
        if (is_array($defaults)) {
            $defaults = new Parameters($defaults);
        }
        elseif (!$defaults instanceof Parameters) {
            throw new \InvalidArgumentException('Default values must be an array or Parameters object');
        }

        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Return the requirements
     *
     * @return \Aero\Std\Parameters
     */
    public function requirements()
    {
        if (!$this->requirements instanceof Parameters) {
            $this->requirements = $this->createRequirementParameters($this->requirements);
        }
        return $this->requirements;
    }

    /**
     * Set the requirements
     *
     * @param  array $requirements
     * @return \Aero\Routing\AbstractRoute
     */
    public function setRequirements($requirements)
    {
        if (is_array($requirements)) {
            $requirements = $this->createRequirementParameters($requirements);
        }
        elseif (!$requirements instanceof Parameters) {
            throw new \InvalidArgumentException('Default values must be an array or Parameters object');
        }

        $this->requirements = $requirements;

        return $this;
    }

    /**
     * Create requirements holder with default assign filter
     *
     * @param  array $values
     * @return \Aero\Std\Parameters
     */
    public function createRequirementParameters(array $values = array())
    {
        return new Parameters($values, function($regex, $key = null) {
            if (!is_string($regex)) {
                throw new \InvalidArgumentException(sprintf(
                    'Routing requirements must be a string, "%s" given for "%s"',
                    gettype($regex),
                    $key
                ));
            }
            return rtrim(ltrim($regex, '^'), '$');
        });
    }

    /**
     * @param  string|array $namespace
     * @return \Aero\Routing\AbstractRoute
     * @throws \InvalidArgumentException
     */
    public function setNamespace($namespace)
    {
        $this->namespaces = array();
        $this->addNamespace($namespace);
        return $this;
    }

    /**
     * @param  string|array $namespace
     * @return \Aero\Routing\AbstractRoute
     * @throws \InvalidArgumentException
     */
    public function addNamespace($namespace)
    {
        if (is_string($namespace)) {
            $namespace = array($namespace);
        }
        elseif (!is_array($namespace)) {
            throw new \InvalidArgumentException('Namespace must be a string or an array of strings');
        }

        foreach ($namespace as $ns) {
            $ns = preg_replace('#[/\\\\]+#', '/', trim($ns, ' /\\'));
            if (!in_array($ns, $this->namespaces)) {
                $this->namespaces[] = $ns;
            }
        }

        return $this;
    }

    /**
     * @param  string|array $namespace
     * @return \Aero\Routing\AbstractRoute
     * @throws \InvalidArgumentException
     */
    public function removeNamespace($namespace)
    {
        if (is_string($namespace)) {
            $namespace = array($namespace);
        }
        elseif (!is_array($namespace)) {
            throw new \InvalidArgumentException('Namespace must be a string or an array of strings');
        }

        foreach ($namespace as $ns) {
            $ns = preg_replace('#[/\\\\]+#', '/', trim($ns, ' /\\'));
            if (($key = array_search($ns, $this->namespaces)) !== false) {
                unset($this->namespaces[$key]);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Check if current route is in specified namespace
     *
     * @throws \InvalidArgumentException
     *
     * @param  string|array $namespaces
     * @return bool
     */
    public function inNamespace($namespaces)
    {
        if (is_string($namespaces)) {
            $namespaces = array($namespaces);
        }
        elseif (!is_array($namespaces)) {
            throw new \InvalidArgumentException('Checking namespace must be a string or an array of strings');
        }

        if (!$this->namespaces) {
            return false;
        }

        foreach ($namespaces as $namespace) {
            $namespace = preg_replace('#[/\\\\]+#', '/', trim($namespace, ' /\\'));
            foreach ($this->namespaces as $ns) {
                if (strpos($ns, $namespace) === 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
