<?php
/**
 * Aero Framework
 *
 * @category    Aero
 * @author      Alex Zavacki
 */

namespace Aero\Filter;

/**
 * Alpha filter
 *
 * @category    Aero
 * @package     Aero_Filter
 * @author      Alex Zavacki
 */
class AlphaFilter extends AbstractFilter
{
    /**
     * @var bool
     */
    protected $allowWhiteSpace = false;

    /**
     * @var bool
     */
    protected $englishAlphabethOnly = false;

    /**
     * @var bool Is PCRE is compiled with UTF-8 and Unicode support
     **/
    protected static $unicodeEnabled;


    /**
     * Constructor.
     *
     * @param bool $allowWhiteSpace
     * @param bool $englishAlphabethOnly
     */
    public function __construct($allowWhiteSpace = false, $englishAlphabethOnly = false)
    {
        $this->allowWhiteSpace = (bool) $allowWhiteSpace;
        $this->englishAlphabethOnly = (bool) $englishAlphabethOnly;

        if (self::$unicodeEnabled === null) {
            self::$unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }
    }

    /**
     * @param  bool $allowWhiteSpace
     * @return \Aero\Filter\AlphaFilter
     */
    public function setAllowWhiteSpace($allowWhiteSpace)
    {
        $this->allowWhiteSpace = (bool) $allowWhiteSpace;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowWhiteSpace()
    {
        return $this->allowWhiteSpace;
    }

    /**
     * @param  boolean $englishAlphabethOnly
     * @return \Aero\Filter\AlphaFilter
     */
    public function setEnglishAlphabethOnly($englishAlphabethOnly)
    {
        $this->englishAlphabethOnly = (bool) $englishAlphabethOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnglishAlphabethOnly()
    {
        return $this->englishAlphabethOnly;
    }

    /**
     * Returns the string $value, removing all but alphabetic characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $whiteSpace = $this->allowWhiteSpace ? '\s' : '';

        if ($this->englishAlphabethOnly || !self::$unicodeEnabled) {
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        }
        else {
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', (string) $value);
    }
}
