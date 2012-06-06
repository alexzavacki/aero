<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator;

/**
 * Validator interface
 *
 * @category    Aero
 * @package     Aero_Validator
 * @author      Alex Zavacki
 */
interface ValidatorInterface
{
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
}
