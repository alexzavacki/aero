<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator;

/**
 * Callback validator
 *
 * @category    Aero
 * @package     Aero_Validator
 * @author      Alex Zavacki
 */
class CallbackValidator extends AbstractValidator
{
    const INVALID_CALLBACK = 'callbackInvalid';
    const INVALID_VALUE = 'valueInvalid';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID_VALUE    => "'%value%' is not valid value",
        self::INVALID_CALLBACK => "An exception has been raised within the callback",
    );

    /**
     * @var callback
     */
    protected $callback;


    /**
     * Constructor.
     *
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        if (is_callable($options)) {
            $options = array('callback' => $options);
        }
        parent::__construct($options);
    }

    /**
     * Set validator options
     *
     * @param  array $options
     * @return \Aero\Validator\AbstractValidator
     */
    public function setOptions($options = array())
    {
        foreach (array_change_key_case($options, CASE_LOWER) as $key => $value)
        {
            switch (str_replace(array('_', '-', '.'), '', $key))
            {
                case 'callback':
                    $this->setCallback($value);
                    break;
            }
        }
        return $this;
    }

    /**
     * @return callback
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  callback $callback
     * @return \Aero\Validator\CallbackValidator
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback given');
        }

        $this->callback = $callback;
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!$this->callback) {
            throw new \InvalidArgumentException('Callback not set');
        }

        $this->setValue($value);

        try {
            if (!call_user_func($this->callback, $value)) {
                $this->error(self::INVALID_VALUE);
                return false;
            }
        } catch (\Exception $e) {
            $this->error(self::INVALID_CALLBACK);
            return false;
        }

        return true;
    }
}
