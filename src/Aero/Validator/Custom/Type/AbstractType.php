<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator\Custom\Type;

/**
 *
 *
 * @category    Aero
 * @package     Aero_Validator
 * @subpackage  Aero_Validator_Custom
 * @subpackage  Aero_Validator_Custom_Type
 * @author      Alex Zavacki
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * @var mixed
     */
    protected $validator;


    /**
     * Constructor.
     *
     * @param mixed $validator
     * @param array $options
     */
    public function __construct($validator, $options = array())
    {
        $this->setValidator($validator);

        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * @param  array $options
     * @return \Aero\Validator\Custom\Type\AbstractType
     */
    public function setOptions(array $options)
    {
        // do nothing in abstract type
        return $this;
    }

    /**
     * Return an array of validation error messages
     *
     * @return array
     */
    public function getMessages()
    {
        return null;
    }

    /**
     * @param  mixed $validator
     * @return \Aero\Validator\Custom\Type\AbstractType
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
