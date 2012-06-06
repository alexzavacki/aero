<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Form;

/**
 * Attributes collection
 *
 * @category    Aero
 * @package     Aero_Form
 * @author      Alex Zavacki
 */
class Attributes extends Collection
{
    /**
     * @var callback
     */
    protected $escaper;


    /**
     * @param  string $name
     * @param  string $value
     * @param  string $glue
     *
     * @return \Aero\Form\Attributes
     */
    public function appendValue($name, $value, $glue = '')
    {
        $oldValue = $this->get($name, false);
        $this->set($name, ($oldValue !== false ? $oldValue . $glue : '') . $value);

        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return static::arrayToString($this->list);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param  array $attributes
     * @return string
     */
    public static function arrayToString($attributes)
    {
        $string = '';

        foreach ((array) $attributes as $attr => $value)
        {
            //$attr = self::escape($attr);

            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            //$value = self::escape($value);

            if ($attr == 'id') {
                $value = static::formatId($value);
            }

            $string .= strpos($value, '"') !== false ? " $attr='$value'" : " $attr=\"$value\"";
        }

        return trim($string);
    }

    /**
     * @param  string $name
     * @param  bool   $allowBrackets
     *
     * @return string
     */
    public static function filterName($name, $allowBrackets = false)
    {
        $charset = '^a-zA-Z0-9_\x7f-\xff';

        if ($allowBrackets) {
            $charset .= '\[\]';
        }

        return preg_replace('/[' . $charset . ']/', '', $name);
    }

    /**
     * @param  string $value
     * @return string
     */
    public static function formatId($value)
    {
        $value = (string) $value;

        if (strstr($value, '['))
        {
            if (substr($value, -2) == '[]') {
                $value = substr($value, 0, strlen($value) - 2);
            }
            $value = trim($value, ']');
            $value = str_replace('][', '-', $value);
            $value = str_replace('[', '-', $value);
        }

        return $value;
    }
}
