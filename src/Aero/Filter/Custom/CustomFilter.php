<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter\Custom;

use Aero\Filter\FilterInterface;

use Aero\Std\Plugin\PluginMap;

/**
 * Custom filter
 *
 * @category    Aero
 * @package     Aero_Filter
 * @subpackage  Aero_Filter_Custom
 * @author      Alex Zavacki
 */
class CustomFilter implements FilterInterface
{
    /**
     * @var mixed
     */
    protected $typeAdapter;

    /**
     * @var \Aero\Std\Plugin\PluginMap
     */
    protected static $staticTypePluginMap;


    /**
     * Constructor.
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @param string $type
     * @param mixed  $filter
     * @param array  $options
     */
    public function __construct($type, $filter, $options = array())
    {
        $typeClass = static::getStaticTypePluginMap()->get($type);

        if (!$typeClass) {
            throw new \LogicException(
                sprintf('Unable to locate type adapter associated with name "%s"', $type)
            );
        }

        if (!is_string($typeClass)) {
            throw new \InvalidArgumentException('Type adapter must be a class name');
        }

        if (!class_exists($typeClass)) {
            throw new \LogicException(sprintf('Type adapter class "%s" not found', $typeClass));
        }

        $reflection = new \ReflectionClass($typeClass);

        if (!$reflection->implementsInterface(__NAMESPACE__ . '\\Type\\TypeInterface')) {
            throw new \LogicException(
                sprintf(
                    'Type adapter class must implement interface "%s"',
                    __NAMESPACE__ . '\\Type\\TypeInterface'
                )
            );
        }

        $this->typeAdapter = new $typeClass($filter, $options);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return $this->typeAdapter->filter($value);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param \Aero\Std\Plugin\PluginMap $typePluginMap
     */
    public static function setStaticTypePluginMap($typePluginMap)
    {
        if (is_string($typePluginMap)) {
            if (!class_exists($typePluginMap)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid type map class provided (%s)', $typePluginMap)
                );
            }
            $typePluginMap = new $typePluginMap();
        }

        if (!$typePluginMap instanceof PluginMap) {
            throw new \InvalidArgumentException(sprintf(
                'Type map must extend Aero\\Plugin\\PluginMap; got type "%s" instead',
                (is_object($typePluginMap) ? get_class($typePluginMap) : gettype($typePluginMap))
            ));
        }

        static::$staticTypePluginMap = $typePluginMap;
    }

    /**
     * @return \Aero\Std\Plugin\PluginMap
     */
    public static function getStaticTypePluginMap()
    {
        if (!static::$staticTypePluginMap instanceof PluginMap) {
            static::$staticTypePluginMap = static::createDefaultStaticTypePluginMap();
        }
        return static::$staticTypePluginMap;
    }

    /**
     * @return \Aero\Filter\Custom\TypePluginMap
     */
    protected static function createDefaultStaticTypePluginMap()
    {
        return new TypePluginMap();
    }
}
