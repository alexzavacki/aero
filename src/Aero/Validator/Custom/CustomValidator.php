<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator\Custom;

use Aero\Validator\ValidatorInterface;

use Aero\Std\Plugin\PluginMap;

/**
 * Custom validator
 *
 * @category    Aero
 * @package     Aero_Validator
 * @subpackage  Aero_Validator_Custom
 * @author      Alex Zavacki
 */
class CustomValidator implements ValidatorInterface
{
    const INVALID_CUSTOM = 'customInvalid';

    /**
     * @var mixed
     */
    protected $typeAdapter;

    /**
     * @var string
     */
    protected $message = 'Value is not valid';

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
     * @param mixed  $validator
     * @param array  $options
     */
    public function __construct($type, $validator, $options = array())
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

        $this->typeAdapter = new $typeClass($validator, $options);
    }

    /**
     * Value validation
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        try {
            return $this->typeAdapter->isValid($value);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return an array of validation error messages
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $this->typeAdapter->getMessages();

        if ($messages !== null) {
            if (is_string($messages)) {
                $messages = array($messages);
            }
            elseif (!is_array($messages)) {
                throw new \LogicException('Type adapter must return error messages as array or string');
            }
        }
        else {
            $messages = array(static::INVALID_CUSTOM => $this->message);
        }

        return $messages;
    }

    /**
     * @param  string $message
     * @return \Aero\Validator\Custom\CustomValidator
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
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
     * @return \Aero\Validator\Custom\TypePluginMap
     */
    protected static function createDefaultStaticTypePluginMap()
    {
        return new TypePluginMap();
    }
}
