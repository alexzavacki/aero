<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form\Element;

use Aero\Std\Plugin\PluginMap;

/**
 * Element factory
 *
 * @category    Aero
 * @package     Aero_Form
 * @subpackage  Aero_Form_Element
 * @author      Alex Zavacki
 */
class ElementFactory
{
    /**
     * @var \Aero\Std\Plugin\PluginMap
     */
    protected $typePluginMap;

    /**
     * @var \Aero\Std\Plugin\PluginMap
     */
    protected static $staticTypePluginMap;


    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @param  string $type
     * @param  string $name
     * @param  array  $options
     *
     * @return \Aero\Form\Element\ElementInterface
     */
    public function create($type, $name, $options = null)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Element type must be a string');
        }
        if (!is_string($name) || !($name = trim($name))) {
            throw new \InvalidArgumentException('Element name must be a non-empty string');
        }

        $typeClass = null;

        if ($this->typePluginMap instanceof PluginMap) {
            $typeClass = $this->typePluginMap->get($type);
        }

        if (!$typeClass) {
            $typeClass = static::getStaticTypePluginMap()->get($type);
        }

        if (!$typeClass) {
            throw new \LogicException(sprintf('Unable to locate element "%s"', $type));
        }

        if (!is_string($typeClass)) {
            throw new \InvalidArgumentException('Element type must be a class name');
        }

        if (!class_exists($typeClass)) {
            throw new \LogicException(sprintf('Element class "%s" not found', $typeClass));
        }

        $reflection = new \ReflectionClass($typeClass);

        if (!$reflection->implementsInterface(__NAMESPACE__ . '\\ElementInterface')) {
            throw new \LogicException(
                sprintf(
                    'Element class must implement interface "%s"',
                    __NAMESPACE__ . '\\ElementInterface'
                )
            );
        }

        return new $typeClass($name, $options);
    }

    /**
     * @param  \Aero\Std\Plugin\PluginMap $typePluginMap
     * @return \Aero\Form\Element\ElementFactory
     */
    public function setTypePluginMap(PluginMap $typePluginMap = null)
    {
        $this->typePluginMap = $typePluginMap;
        return $this;
    }

    /**
     * @return \Aero\Std\Plugin\PluginMap
     */
    public function getTypePluginMap()
    {
        if (!$this->typePluginMap instanceof PluginMap) {
            $this->typePluginMap = $this->createDefaultTypePluginMap();
        }
        return $this->typePluginMap;
    }

    /**
     * @return \Aero\Std\Plugin\PluginMap
     */
    protected function createDefaultTypePluginMap()
    {
        return new PluginMap();
    }

    /**
     * @param  \Aero\Std\Plugin\PluginMap $typePluginMap
     * @return \Aero\Form\Element\ElementFactory
     */
    public static function setStaticTypePluginMap(PluginMap $typePluginMap = null)
    {
        self::$staticTypePluginMap = $typePluginMap;
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
     * @return \Aero\Std\Plugin\PluginMap
     */
    protected static function createDefaultStaticTypePluginMap()
    {
        return new TypePluginMap();
    }
}
