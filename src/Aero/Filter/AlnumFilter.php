<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter;

/**
 * Alpha numeric filter
 *
 * @category    Aero
 * @package     Aero_Filter
 * @author      Alex Zavacki
 */
class AlnumFilter extends AlphaFilter
{
    /**
     * Returns the string $value, removing all but alphabetic and digit characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $whiteSpace = $this->allowWhiteSpace ? '\s' : '';

        if ($this->englishAlphabethOnly || !self::$unicodeEnabled) {
            $pattern = '/[^a-zA-Z0-9' . $whiteSpace . ']/';
        }
        else {
            $pattern = '/[^\p{L}\p{N}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', (string) $value);
    }
}
