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
class Zend2Type extends AbstractType
{
    /**
     * Value validation
     *
     * @throws \LogicException
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!$this->validator instanceof \Zend\Validator\Validator) {
            throw new \LogicException(
                'Validator object not set or does not implement Zend\\Validator\\Validator'
            );
        }
        return $this->validator->isValid($value);
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
        if (!$this->validator instanceof \Zend\Validator\Validator) {
            throw new \LogicException(
                'Validator object not set or does not implement Zend\\Validator\\Validator'
            );
        }
        return $this->validator->getMessages();
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  mixed $validator
     * @return \Aero\Validator\Custom\Type\AbstractType
     */
    public function setValidator($validator)
    {
        if (!$validator instanceof \Zend\Validator\Validator) {
            throw new \InvalidArgumentException('Validator must implement Zend\\Validator\\Validator');
        }
        $this->validator = $validator;
        return $this;
    }
}
