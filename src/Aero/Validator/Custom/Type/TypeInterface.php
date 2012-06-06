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
interface TypeInterface
{
    /**
     * Constructor.
     *
     * @param mixed $validator
     * @param array $options
     */
    public function __construct($validator, $options = array());

    /**
     * Value validation
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value);

    /**
     * Return an array of validation error messages
     *
     * @return array
     */
    public function getMessages();

    /**
     * @param  mixed $validator
     * @return mixed
     */
    public function setValidator($validator);

    /**
     * @return mixed
     */
    public function getValidator();
}
