<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator;

/**
 * Digits validator
 *
 * @category    Aero
 * @package     Aero_Validator
 * @author      Alex Zavacki
 */
class DigitsValidator extends AbstractValidator
{
    const INVALID = 'intInvalid';
    const NOT_INT = 'notInt';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => 'Invalid type given. String or integer expected',
        self::NOT_INT => '"%value%" contains not only digits',
    );


    /**
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if (!ctype_digit((string) $value)) {
            $this->error(self::NOT_INT);
            return false;
        }

        return true;
    }
}
