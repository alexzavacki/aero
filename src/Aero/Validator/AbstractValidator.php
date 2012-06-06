<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Validator;

/**
 * Abstract validator
 *
 * @category    Aero
 * @package     Aero_Validator
 * @author      Alex Zavacki
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var mixed The value to be validated
     */
    protected $value;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var array
     */
    protected $messageTemplates = array();

    /**
     * @var array
     */
    protected $messageVariables = array();


    /**
     * Constructor.
     *
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set validator options
     *
     * @param  array $options
     * @return \Aero\Validator\AbstractValidator
     */
    public function setOptions($options = array())
    {
        // do nothing in abstract validator
        return $this;
    }

    /**
     * Invoke as command
     *
     * @param  mixed $value
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string|array $template
     * @param  string|null  $key
     *
     * @return \Aero\Validator\AbstractValidator
     */
    public function setMessageTemplate($template, $key = null)
    {
        if (is_string($template))
        {
            if ($key === null) {
                $template = array_fill_keys(array_keys($this->messageTemplates), $template);
            }
            elseif (isset($this->messageTemplates[$key])) {
                $template = array($key => $template);
            }
            else {
                throw new \InvalidArgumentException(sprintf('Key "%s" not found', $key));
            }
        }
        elseif (!is_array($template)) {
            throw new \InvalidArgumentException('Template must be a string or an array');
        }

        $this->messageTemplates = array_merge(
            $this->messageTemplates,
            array_intersect_key($template, $this->messageTemplates)
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getMessageTemplates()
    {
        return $this->messageTemplates;
    }

    /**
     * @return array
     */
    public function getMessageVariables()
    {
        return array_keys($this->messageVariables);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $key
     * @param  mixed|null $value
     *
     * @return \Aero\Validator\AbstractValidator
     */
    protected function error($key, $value = null)
    {
        if (!$key) {
            $keys = array_keys($this->messageTemplates);
            $key = current($keys);
        }
        elseif (!isset($this->messageTemplates[$key])) {
            throw new \InvalidArgumentException(
                sprintf('Key "%s" not found in message templates', $key)
            );
        }

        if ($value === null) {
            $value = $this->value;
        }

        $this->messages[$key] = $this->createMessage($key, $value);

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return string
     */
    protected function createMessage($key, $value)
    {
        if (!isset($this->messageTemplates[$key])) {
            throw new \InvalidArgumentException(
                sprintf('Key "%s" not found in message templates', $key)
            );
        }

        $value    = (string) $value;
        $template = $this->messageTemplates[$key];

        $message = str_replace('%value%', $value, $template);

        foreach ($this->messageVariables as $ident => $property) {
            $message = str_replace("%$ident%", $this->$property, $message);
        }

        return $message;
    }

    /**
     * Set the value and clear error messages
     *
     * @param  mixed $value
     * @return \Aero\Validator\AbstractValidator
     */
    protected function setValue($value)
    {
        $this->value = $value;
        $this->messages = array();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
